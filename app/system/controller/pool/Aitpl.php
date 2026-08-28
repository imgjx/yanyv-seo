<?php
/**
 * ===========================================================================
 * YanyvSEO - AI模板生成
 * 蜘蛛池管理 -> AI模板：输入建站需求，系统自动注入年月日，AI生成整套模板
 * 生成文件：public/template/{name}/index.tpl + post.tpl + {name}.conf
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\Template as MD;
use tool\Ai;

class Aitpl extends AdminBase
{
    /**
     * AI模板页
     */
    public function index()
    {
        $this->assign(['ready' => Ai::ready()]);
        return $this->fetch();
    }

    /**
     * 生成模板
     */
    public function gen()
    {
        if(!Ai::ready()) return $this->returnMsg('请先在 系统管理->AI配置 中配置API地址与密钥');
        $d = $this->only(['@token'=>'','@name/*/{2,20}/模板目录需2-20位字母、数字、下划线或中划线/1,2/_-','@title/*/{2,50}/模板显示名','@require/*','remark/h'],'post','',false);
        if(!preg_match('/^[a-z0-9_\-]{2,20}$/i', $d['name'])) return $this->returnMsg('目录名需2-20位字母、数字、下划线或中划线');
        if(MD::one(['name'=>$d['name']])) return $this->returnMsg('该目录名已存在');
        $dir = MD::dir($d['name']);
        if(is_dir($dir)) return $this->returnMsg("public/template/{$d['name']} 目录已存在，请更换目录名");
        $date = date('Y年m月d日');
        $require = trim(strval($d['require']));
        $prompt = "你是一名资深前端与SEO专家。请根据建站需求，为一个站群落地页系统生成一套完整模板。
系统自动注入的当前日期：{$date}（请自然体现在页面版权或正文中，不要出现占位符）。

【建站需求】
{$require}

【技术要求】
1. 模板引擎为ThinkPHP模板语法，只能使用以下变量，不要杜撰其他变量：
   - {\$site.title} 站点名称
   - {\$Keyword} 当前页面SEO关键词（可自然穿插在标题/正文/关键词标签中）
   - {\$Content|raw} 页面正文内容
   - {\$GuideLinks} 引导链接列表，用 <volist name=\"GuideLinks\" id=\"vo\"><a href=\"{\$vo.url}\">...</a></volist> 输出
   - {\$Article.list} 文章列表(含 title/url/time)，用 <volist name=\"Article.list\" id=\"vo\"> 输出，链接 {\$vo.url}
   - {\$Article.detail.title} / {\$Article.detail.content|raw} 文章详情(仅post.tpl使用)
   - {\$copyright} 版权文字
2. 只输出两个文件，严格按标记分隔，标记行必须原样输出：
===== index.tpl =====
（首页完整HTML：含导航、轮播或栏目区、{\$Content|raw}正文、文章列表、引导链接区、页脚含日期与 {\$copyright}）
===== post.tpl =====
（文章详情页完整HTML：{\$Article.detail.title} 标题、{\$Article.detail.content|raw} 正文、相关文章列表、页脚含日期与 {\$copyright}）
3. 单文件完整独立HTML，内联CSS，自适应移动端，简洁美观有真实网站感，禁止emoji，禁止出现\"AI生成\"字样；
4. 禁止输出任何解释说明文字，只输出标记与文件内容。";
        @set_time_limit(0);
        $text = Ai::chat($prompt, '你是模板代码生成器，只输出代码文件与标记，不输出任何解释。');
        $files = self::parseFiles(strval($text));
        if(!isset($files['index.tpl'])) return $this->returnMsg('AI生成失败：未解析到 index.tpl 内容，请重试或检查AI配置');
        if(!mkdir($dir, 0755, true)) return $this->returnMsg('目录创建失败，请检查权限');
        foreach($files as $f => $code){
            file_put_contents($dir.DIRECTORY_SEPARATOR.$f, $code);
        }
        MD::writeConf($d['name'], [
            'name'    => $d['name'],
            'title'   => $d['title'],
            'version' => '1.0',
            'author'  => 'AI',
            'remark'  => 'AI模板：'.mb_substr($require, 0, 80),
            'index'   => 'index',
            'routes'  => "/post/(\\d+) => post?id=\$1",
        ]);
        try{
            MD::create(['name'=>$d['name'],'title'=>$d['title'],'remark'=>mb_substr($require,0,80),'state'=>1,'creator'=>$this->manUser['username'],'add_time'=>time()]);
        }catch(\Throwable $e){}
        $list = implode('、', array_keys($files));
        return $this->returnMsg(['msg'=>"模板生成成功：public/template/{$d['name']}/（{$list}）", 'name'=>$d['name']], 1);
    }

    /**
     * 解析AI输出的多文件标记格式
     */
    public static function parseFiles(string $text): array
    {
        $out = [];
        if(!preg_match_all('/=====+\s*(index\.tpl|post\.tpl)\s*=+*(.*?)(?=====+\s*(?:index\.tpl|post\.tpl)\s*=+*|$)/s', $text, $m, PREG_SET_ORDER)){
            //兜底：整体作为 index.tpl
            if(trim($text) !== '') $out['index.tpl'] = trim($text);
            return $out;
        }
        foreach($m as $v){
            $code = trim(strval($v[2]));
            if($code !== '' && mb_strlen($code) > 50) $out[$v[1]] = $code;
        }
        return $out;
    }
}
