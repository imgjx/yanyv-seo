<?php
/**
 * ===========================================================================
 * YanyvSEO - 站群前台渲染引擎
 * 简易路由：/wz/{模板名}/{cid} -> 渲染 public/{模板名}/{模板名}.tpl?cid=cid
 * 泛解析：当前Host命中 vt_pool_site.domain 即整站接管渲染
 * 流程：解析站点 -> 识别搜索引擎(仅UA/仅IP/IP+UA) -> 蜘蛛则按占比302强引/渲染落地页 -> 消耗引导额度并记账
 * ===========================================================================
 */
namespace app\index\controller;

use app\BaseController;
use app\model\pool\Site as SiteM;
use app\model\pool\Engine as EngineM;
use app\model\pool\Link as LinkM;
use app\model\pool\Template as TplM;
use app\model\cms\Article as ArticleM;
use think\Response;
use think\facade\Db;
use think\facade\View;
use think\exception\HttpResponseException;

class Wz extends BaseController
{
    /**
     * 当前命中的站点
     * @var array
     */
    protected $site = [];

    /**
     * 入口分发
     * @param string $name  模板文件名(省略时按 .conf 路由规则匹配当前路径)
     * @param mixed  $cid   内容参数ID
     */
    public function render(string $name = '', $cid = '')
    {
        // 解析站点：优先按当前域名，未命中按参数名兜底404
        $site = SiteM::matchHost($this->request->host());
        if(!$site) $this->out('Not Found', 404);
        $this->site = is_object($site) ? $site->toArray() : (array)$site;
        return $this->display($name, $cid);
    }

    /**
     * 兼容 /wz.tpl?cid=123 式调用: renderTpl
     */
    public function tpl(string $name = '', $cid = '')
    {
        return $this->render($name, $cid);
    }

    /**
     * 渲染落地页
     */
    protected function display(string $name, $cid = '')
    {
        $spider = $this->checkSpider();            //['engine_id'=>0或引擎ID,'ua'=>'','ip'=>'','obj'=>引擎记录]
        $isSpider = $spider['engine_id'] > 0;
        //准备模板数据：public/template/{目录}/，目录取站点绑定的模板记录
        $tplDir = '';
        $template_id = intval($this->site['template_id']);
        if($template_id && ($t = TplM::one(['tid'=>$template_id]))){
            $n = strval($t->name);
            if(is_dir(TplM::dir($n))) $tplDir = $n;
        }
        $conf = $tplDir !== '' ? TplM::readConf($tplDir) : ['routes'=>'','index'=>'index'];
        $params = [];           //路由变量 例 id=11 -> 模板内 {$id}
        if($name === ''){
            //未指定模板名：按 .conf 路由规则匹配当前访问路径(如 /post/11 -> post.tpl?id=11)
            $matched = TplM::matchRoute(strval($conf['routes']), strval($this->request->pathinfo()));
            $name = $matched ? $matched['tpl'] : 'index';
            $params = $matched ? $matched['params'] : [];
        }else{
            $params = ['id' => strip_sql(strval($cid))];       //显式调用保持 cid 参数
        }
        //合并URL查询参数（优先级低于路由变量）
        foreach((array)$this->request->get() as $k => $v){
            if(preg_match('/^[a-z][a-z0-9_]{0,20}$/i', strval($k)) && !isset($params[$k])){
                $params[$k] = strip_sql(strval(is_array($v) ? '' : $v));
            }
        }
        //SEO关键词模式：URL自带关键词(蜘蛛受kw_spider开关控制) > 词库随机
        $kw = $this->pickKeyword($params, $isSpider);
        //蜘蛛来访：按302强引占比决定是否直接跳转已投放链接（占比仅限制302强引，普通引导不限制）
        if($isSpider){
            //挑选一条该引擎可用的强引链接
            $jump = $this->pickLink($spider, 2);
            if($jump && mt_rand(1, 100) <= intval($this->site['ratio_301'])){
                $this->consume($jump, $spider, 2, $kw !== '');   //消耗额度+流水
                return Response::create($this->kwUrl($jump['url'], $kw), 'redirect', 302);
            }
            //不跳转时记一次普通引导
            if($normal = $this->pickLink($spider, 1)) $this->consume($normal, $spider, 1, $kw !== '');
        }
        //定位模板文件(ThinkPHP模板语法，think-template引擎渲染)
        $base = VT_PUBLIC.($tplDir !== '' ? 'template/'.$tplDir.'/' : '');
        $file = $base.$name.'.tpl';
        if(!is_file($file)){
            $idx = trim(strval($conf['index'] ?: 'index'), '.');
            $file = $base.(preg_match('/^[a-z0-9_\-]+$/i', $idx) ? $idx : 'index').'.tpl';
        }
        if(!is_file($file)) $this->out('Template file not exists', 500);
        //CMS内容抓取(带缓存)
        $cms = $this->cmsContent();
        //投放链接列表(非蜘蛛不给链接) 输出格式 [['url'=>...], ...] 见 template.md
        $links = [];
        if($isSpider){
            $show = max(1, intval($this->site['spider_show'] ?: 10));
            $urls = LinkM::where([
                ['engine_id','=',$spider['engine_id']],
                ['state','=',1],
                ['used','<',Db::raw('total')],
                ['guide_type','=',1]
            ])->where(function($q){ $q->where('site_id',$this->site['siteid'])->whereOr('site_id',0); })
              ->orderRaw('rand()')->limit($show)->column('url');
            foreach($urls as $u){ $links[] = ['url' => strval($u)]; }
        }
        //内容管理文章：列表(随机8条) + 详情(路由id匹配,按分组伪原创)
        $Art = ['list'=>[], 'detail'=>null];
        try{
            $aid = isset($params['id']) && preg_match('/^\d+$/', strval($params['id'])) ? intval($params['id']) : 0;
            if($aid && ($d = (new ArticleM())->displayContent($aid))) $Art['detail'] = $d;
            foreach(ArticleM::where('state',1)->orderRaw('rand()')->limit(8)->select() as $a){
                $Art['list'][] = ['articleid'=>$a->articleid,'title'=>$a->title,'url'=>'?id='.$a->articleid,'time'=>date('Y-m-d',$a->add_time)];
            }
        }catch(\Throwable $e){}
        View::assign(array_merge($params, [
            'site'       => array_merge($this->site, ['host'=>$this->request->host()]),
            'cid'        => strip_sql(strval($cid)),
            'engine'     => $spider,
            'GuideLinks' => $links,
            'Article'    => $Art,
            'Keyword'    => $kw,
            'Content'    => $cms['Content'] ?? '',
            'Cms'        => $cms,
            'copyright'  => vconfig('sys_copyright'),
        ]));
        $html = View::fetch($file);
        //非蜘蛛且设置了仅蜘蛛可见时不输出链接内容
        return Response::create($html)->contentType('text/html');
    }

    /**
     * 识别搜索引擎 仅UA / 仅IP / IP+UA
     * @return array [engine_id, ua, ip, obj]
     */
    public function checkSpider(): array
    {
        $ip = $this->realIp();
        $ua = strval($this->request->server('HTTP_USER_AGENT'));
        $list = EngineM::where('state',1)->select()->toArray();
        foreach($list as $en){
            if($en['match_mode'] != 2){
                $okUa = false;
                $uas = explode(',', strtolower(str_replace('，', ',', $en['ua_keywords'])));
                foreach(array_filter(array_map('trim', $uas)) as $kw){
                    if($kw !== '' && stripos($ua, $kw) !== false){ $okUa = true; break; }
                }
            }else{
                $okUa = true; //仅IP模式不校验UA
            }
            if($en['match_mode'] == 1){
                $okIp = true; //仅UA模式不校验IP
            }else{
                foreach(explode("\n", strval($en['ip_rules'])) as $rule){
                    $rule = trim($rule);
                    if($rule === '') continue;
                    if($this->ipMatch($ip, $rule)){ $okIp = true; break; }
                }
            }
            if($okUa && $okIp){
                return ['engine_id'=>$en['engine_id'], 'ua'=>mb_substr($ua,0,250), 'ip'=>$ip, 'obj'=>$en];
            }
        }
        return ['engine_id'=>0, 'ua'=>mb_substr($ua,0,250), 'ip'=>$ip, 'obj'=>null];
    }

    /**
     * 真实IP（站点配置了头则优先读该头第一个值）
     */
    protected function realIp(): string
    {
        $head = trim(strval($this->site['real_ip_head'] ?? 'X-Forwarded-For'));
        if($head !== '' && ($v = $this->request->header(strtolower($head)))){
            $ips = explode(',', strval($v));
            $ip = trim($ips[0]);
            if(filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
        return $this->request->ip();
    }

    /**
     * IP规则匹配 支持前缀段与CIDR
     */
    protected function ipMatch(string $ip, string $rule): bool
    {
        if(strpos($rule, '/') !== false){
            list($net, $mask) = explode('/', $rule, 2);
            if(!filter_var($ip, FILTER_VALIDATE_IP) || !filter_var($net, FILTER_VALIDATE_IP)) return false;
            return (ip2long($ip) >> (32 - intval($mask))) === (ip2long($net) >> (32 - intval($mask)));
        }
        return strncmp($ip, $rule, strlen($rule)) === 0;
    }

    /**
     * 挑选一条可用链接 state=1 且 used<total 且 (全站 或 本站)
     * @param int $type 引导类型1普通2强引
     */
    protected function pickLink(array $spider, int $type)
    {
        $q = LinkM::where([
            ['engine_id','=',$spider['engine_id']],
            ['state','=',1],
            ['used','<',Db::raw('total')],
            ['guide_type','=',$type]
        ])->where(function($q){ $q->where('site_id',$this->site['siteid'])->whereOr('site_id',0); })
          ->orderRaw('rand()');
        return $q->find()?->toArray();
    }

    /**
     * 消耗一条链接额度并写入引导流水（预付费制：不再动用户余额）
     * 关键词模式引导按站点 kw_price 计费（0则按链接单价）
     */
    protected function consume(array $link, array $spider, int $type, bool $kwUsed = false)
    {
        try{
            $points = floatval($link['price_point']);
            if($kwUsed && floatval($this->site['kw_price'] ?? 0) > 0) $points = floatval($this->site['kw_price']);
            Db::name('pool_link')->where([['lid','=',$link['lid']],['used','<',Db::raw('total')]])->inc('used')->update(['upd_time'=>time()]);
            Db::name('pool_link')->where([['lid','=',$link['lid']],['used','>=',Db::raw('total')]])->update(['state'=>2,'upd_time'=>time()]);
            Db::name('pool_billing_log')->insert([
                'link_id'    => $link['lid'],
                'userid'     => $link['userid'],
                'site_id'    => $this->site['siteid'],
                'engine_id'  => $spider['engine_id'],
                'guide_type' => $type,
                'points'     => dround($points),
                'spider_ip'  => $spider['ip'],
                'spider_ua'  => $spider['ua'],
                'referer_url'=> mb_substr(strip_sql($this->request->url(true)), 0, 250),
                'add_time'   => time(),
            ]);
        }catch(\Throwable $e){}
    }

    /**
     * SEO关键词解析（kw_mode=1 时生效）
     * 优先级：URL自带关键词 > 词库随机；蜘蛛访问URL带词受 kw_spider 开关控制
     * URL自带词需在词库中（词库为空则不限制）
     */
    protected function pickKeyword(array $params, bool $isSpider): string
    {
        if(intval($this->site['kw_mode'] ?? 0) !== 1) return '';
        $lib = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', strval($this->site['kw_lib'] ?? ''))))));
        $param = trim(strval($this->site['kw_param'] ?? 'kw')) ?: 'kw';
        $urlKw = trim(strval($params[$param] ?? ''));
        if($urlKw !== ''){
            $allow = $isSpider ? intval($this->site['kw_spider'] ?? 1) === 1 : true;
            if($allow && (!$lib || in_array($urlKw, $lib))) return mb_substr($urlKw, 0, 30);
        }
        return $lib ? $lib[mt_rand(0, count($lib)-1)] : '';
    }

    /**
     * 给引导URL附加关键词参数（kw_param配置，默认kw）
     */
    protected function kwUrl(string $url, string $kw): string
    {
        if($kw === '') return $url;
        $param = trim(strval($this->site['kw_param'] ?? 'kw')) ?: 'kw';
        return $url.(strpos($url, '?') === false ? '?' : '&').$param.'='.urlencode($kw);
    }

    /**
     * CMS内容：站点绑定文章分组（vt_cms_group），随机一篇正文
     * 输出：Content=伪原创+MD转HTML 正文，Title=文章标题（带文件缓存）
     */
    protected function cmsContent(): array
    {
        $groupid = intval($this->site['cms_groupid'] ?? 0);
        if(!$groupid) return [];
        $cacheFile = app()->getRuntimePath().'pool/cms_'.md5('g'.$groupid.$this->site['siteid']).'.php';
        $hours = max(1, intval($this->site['cache_hours'] ?: 6));
        if(is_file($cacheFile) && (time() - filemtime($cacheFile)) < $hours*3600){
            return include $cacheFile;
        }
        $data = [];
        try{
            $a = ArticleM::where([['groupid','=',$groupid],['state','=',1]])->orderRaw('rand()')->find();
            if($a){
                $data['Content'] = ArticleM::renderContent($groupid, strval($a->content));
                $data['Title']   = strval($a->title);
            }
        }catch(\Throwable $e){}
        if($data){
            if(!is_dir(dirname($cacheFile))) mkdir(dirname($cacheFile), 0755, true);
            file_put_contents($cacheFile, '<?php return '.var_export($data, true).';');
        }
        return $data;
    }

    /**
     * 纯文本响应
     */
    protected function out(string $msg, int $code = 200)
    {
        throw new HttpResponseException(Response::create($msg)->code($code));
    }

}
