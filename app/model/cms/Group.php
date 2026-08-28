<?php
/**
 * ===========================================================================
 * YanyvSEO - CMS文章分组模型（含抓取与伪原创引擎）
 * 分组即抓取源：列表页URL+链接正则 / 详情页标题·内容正则
 * 伪原创库：每行 原词=替换词1|替换词2...，展示时实时随机替换
 * ===========================================================================
 */
namespace app\model\cms;

use app\model\Base;

class Group extends Base
{
    protected $name = 'cms_group';

    protected $pk = 'groupid';

    /**
     * 分组列表（分页）
     */
    public function listQuery(array $where = [])
    {
        $d = request()->get('','','strip_sql');
        if(($d['kw'] ?? '') !== '') $where[] = ['title','LIKE','%'.$d['kw'].'%'];
        return $this->where($where)->order('listorder','asc')->order('groupid','asc')->paginate(intval($d['limit'] ?? 10));
    }

    /**
     * 全部启用分组（下拉用）
     */
    public static function allGroups()
    {
        return self::where('state',1)->field('groupid,title')->order('listorder','asc')->select()->toArray();
    }

    /**
     * 伪原创替换：按分组词库实时处理
     * @param int    $groupid 分组ID
     * @param string $text    原文
     * @return string
     */
    public static function pseudo(int $groupid, string $text): string
    {
        if($text === '') return $text;
        $g = self::one(['groupid'=>$groupid]);
        $lib = $g ? strval($g->pseudo_lib) : '';
        if($lib === '') return $text;
        foreach(explode("\n", str_replace("\r",'',$lib)) as $line){
            $line = trim($line);
            if($line === '' || strpos($line,'=') === false) continue;
            [$from, $tos] = explode('=', $line, 2);
            $from = trim($from);
            if($from === '') continue;
            $opts = array_values(array_filter(array_map('trim', explode('|', $tos))));
            if(!$opts) continue;
            //整篇同词替换为同一候选，保持行文一致性
            $to = $opts[mt_rand(0, count($opts)-1)];
            $text = str_replace($from, $to, $text);
        }
        return $text;
    }

    /**
     * 抓取执行：列表页 -> 文章链接 -> 详情提取 -> 入库(按标题去重)
     * @param  \app\model\cms\Group $g 分组模型实例
     * @return string 结果报告
     */
    public static function crawl($g): string
    {
        $listUrl = trim(strval($g->list_url));
        if(!preg_match('#^https?://#i', $listUrl)) return '列表页URL无效';
        $html = self::fetch($listUrl, strval($g->charset));
        if($html === '') return '列表页抓取失败';
        //提取文章链接
        $urls = [];
        foreach(explode("\n", str_replace("\r",'',strval($g->list_rule))) as $line){
            $line = trim($line);
            if($line === '' || !is_regex($line)) continue;
            if(!preg_match_all($line, $html, $m)) continue;
            foreach((array)($m[1] ?? []) as $u){
                $u = trim(strval($u));
                if($u === '') continue;
                //相对路径补全
                if(!preg_match('#^https?://#i',$u)){
                    $p = parse_url($listUrl);
                    $u = $p['scheme'].'://'.$p['host'].(substr($u,0,1)=='/' ? $u : '/'.$u);
                }
                $urls[] = $u;
            }
        }
        $urls = array_values(array_unique($urls));
        if(!$urls) return '列表页未匹配到文章链接，请检查链接正则';
        //详情抓取入库
        $tRule = trim(strval($g->title_rule));
        $cRule = trim(strval($g->content_rule));
        if(!is_regex($tRule) || !is_regex($cRule)) return '标题或内容正则无效';
        $ok = $skip = $fail = 0;
        $urls = array_slice($urls, 0, 20);   //单次最多抓20篇防超时
        foreach($urls as $u){
            $html2 = self::fetch($u, strval($g->charset));
            if($html2 === ''){ $fail++; continue; }
            if(!preg_match($tRule, $html2, $mt)){ $fail++; continue; }
            if(!preg_match($cRule, $html2, $mc)){ $fail++; continue; }
            $title = trim(strip_tags(strval($mt[1] ?? '')));
            $content = trim(strval($mc[1] ?? ''));
            if($title === '' || word_count($content) < 20){ $fail++; continue; }
            if(Article::one(['groupid'=>$g->groupid,'title'=>$title])){ $skip++; continue; }
            Article::create(['groupid'=>$g->groupid,'title'=>$title,'content'=>$content,'state'=>1,'add_time'=>time()]);
            $ok++;
        }
        return "抓取完成：成功 {$ok} 篇，重复跳过 {$skip} 篇，失败 ".($fail)." 篇（单次上限20篇）";
    }

    /**
     * 抓取远端页面并转码
     */
    protected static function fetch(string $url, string $charset = 'utf-8'): string
    {
        $ch = curl_init();
        curl_setopt_array($ch,[
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; YanyvCrawler/1.0)',
            CURLOPT_ENCODING => 'gzip, deflate',
        ]);
        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if($html === false || $code >= 400) return '';
        $enc = strtolower($charset) !== 'utf-8' ? $charset : 'UTF-8, GBK, GB2312';
        $det = mb_detect_encoding($html, ['UTF-8','GBK','GB2312','BIG5'], true);
        if($det && $det !== 'UTF-8') $html = mb_convert_encoding($html, 'UTF-8', $det);
        return $html;
    }
}
