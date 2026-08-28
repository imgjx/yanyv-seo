<?php
/**
 * ===========================================================================
 * YanyvSEO - AI内容生成 / AI文章重写
 * 内容管理 -> AI内容生成 -> AI内容
 * 提示词可自定义(存 ai_article_prompt)，{date} 自动注入当前年月日
 * ===========================================================================
 */
namespace app\system\controller\cms;

use app\system\controller\AdminBase;
use app\model\cms\Article as MD;
use app\model\cms\Group as GM;
use app\model\system\SystemSetting as S;
use tool\Ai as AiTool;

class Ai extends AdminBase
{
    /**
     * 内置默认提示词：生成Markdown文章，拟人化、无emoji、无AI味
     */
    const DEFAULT_PROMPT = '你是一名从业十年的中文自媒体编辑，请围绕当下热点自拟主题，写一篇全新的中文文章。
要求：
1. 直接输出Markdown格式文章，第一行用 "# 标题" 给出文章标题；
2. 全文800-1500字，分段清晰，可有二级标题与列表；
3. 语言自然拟人化，像真人写的经验分享，观点有细节有事例；
4. 严禁使用任何emoji表情、禁止出现"作为AI""人工智能助手"等字眼；
5. 禁止使用"总而言之""综上所述""首先/其次/最后"等模板化套话；
6. 文章中自然融入当前日期：{date}。

当前日期：{date}';

    /**
     * AI内容页
     */
    public function index()
    {
        $this->assign([
            'ready'  => AiTool::ready(),
            'groups' => GM::allGroups(),
            'prompt' => $this->currentPrompt(),
            'date'   => date('Y年m月d日')
        ]);
        return $this->fetch();
    }

    /**
     * 保存提示词
     */
    public function save()
    {
        $d = $this->only(['@token'=>'','@prompt/s'], 'post', '', false);
        $prompt = trim(strval($d['prompt']));
        $row = S::one([['name','=','ai_article_prompt']]);
        if($row){
            $row->save(['value'=>$prompt, 'upd_time'=>time(), 'editor'=>$this->manUser['username']]);
        }else{
            S::create(['name'=>'ai_article_prompt','title'=>'AI文章提示词','group'=>'ai','type'=>'textarea','value'=>$prompt,'listorder'=>5,'state'=>1,'creator'=>$this->manUser['username'],'add_time'=>time()]);
        }
        S::cache(1);
        return $this->returnMsg('提示词已保存', 1);
    }

    /**
     * 批量生成文章（轮数=生成篇数，并发=同时请求数）
     */
    public function gen()
    {
        if(!AiTool::ready()) return $this->returnMsg('请先在 系统管理->AI配置 中配置API地址与密钥');
        $d = $this->only(['@token'=>'','@groupid/d','@rounds/d','@concurrency/d'],'post','',false);
        $groupid = intval($d['groupid']);
        if(!$groupid || !GM::one(['groupid'=>$groupid])) return $this->returnMsg('请选择要保存到的文章分组');
        $rounds = max(1, min(50, intval($d['rounds'] ?? 1)));
        $conc   = max(1, min(10, intval($d['concurrency'] ?? 3)));
        $prompt = $this->currentPrompt(true); //已注入日期
        @set_time_limit(0);
        //按日期注入+每篇角度编号构造提示词，保证多线程批量下主题分散
        $tasks = [];
        for($i = 1; $i <= $rounds; $i++){
            $tasks[$i] = $prompt."\n\n本篇序号：第{$i}篇，请自拟与其他篇目完全不同的主题与切入角度。";
        }
        $rs = AiTool::chatMulti($tasks, $conc);
        $ok = $fail = 0; $errs = [];
        foreach($rs as $k => $text){
            $art = self::parseArticle(strval($text));
            if($art === null){ $fail++; $errs[] = '第'.$k.'篇解析失败(内容为空或格式不符)'; continue; }
            if(MD::one(['groupid'=>$groupid,'title'=>$art['title']])){ $fail++; $errs[] = '第'.$k.'篇标题重复：'.$art['title']; continue; }
            MD::create(['groupid'=>$groupid,'title'=>$art['title'],'content'=>$art['content'],'state'=>1,'add_time'=>time()]);
            $ok++;
        }
        $msg = "生成完成：成功 {$ok} 篇，失败 {$fail} 篇";
        if($errs) $msg .= '；'.implode('；', array_slice($errs, 0, 3));
        return $this->returnMsg(['msg'=>$msg, 'ok'=>$ok, 'fail'=>$fail], 1);
    }

    /**
     * AI重写（多选文章）
     */
    public function rewrite()
    {
        if(!AiTool::ready()) return $this->returnMsg('请先在 系统管理->AI配置 中配置API地址与密钥');
        $d = $this->only(['@token'=>'','@ids/a','@concurrency/d'],'post','',false);
        $ids = array_values(array_filter(array_map('intval', (array)($d['ids'] ?? []))));
        if(!$ids) return $this->returnMsg('请选择需要重写的文章');
        $conc = max(1, min(10, intval($d['concurrency'] ?? 3)));
        @set_time_limit(0);
        $tasks = $articles = [];
        foreach(MD::where([['articleid','in',$ids]])->select() as $a){
            $articles[$a->articleid] = $a;
            $tasks[$a->articleid] = "你是一名从业十年的中文编辑。请将下面这篇文章整体重写：保持原意与信息量，但换一种行文结构与表达方式，语言自然拟人化，禁止使用emoji，禁止出现模板化套话，禁止出现\"作为AI\"等字眼，全文直接输出Markdown格式，第一行用 \"# 标题\" 给出与原文不同的新标题。\n\n原文：\n".$a->content;
        }
        if(!$tasks) return $this->returnMsg('文章不存在');
        $rs = AiTool::chatMulti($tasks, $conc);
        $ok = $fail = 0;
        foreach($rs as $id => $text){
            $art = self::parseArticle(strval($text));
            if($art === null){ $fail++; continue; }
            MD::where('articleid', $id)->update(['title'=>$art['title'], 'content'=>$art['content']]);
            $ok++;
        }
        return $this->returnMsg(['msg'=>"重写完成：成功 {$ok} 篇，失败 {$fail} 篇", 'ok'=>$ok, 'fail'=>$fail], 1);
    }

    /**
     * 当前生效提示词
     * @param bool $inject 是否注入日期
     */
    protected function currentPrompt(bool $inject = false): string
    {
        $p = trim(strval(vconfig('ai_article_prompt','')));
        if($p === '') $p = self::DEFAULT_PROMPT;
        return $inject ? str_replace('{date}', date('Y年m月d日'), $p) : $p;
    }

    /**
     * 解析AI输出为标题+正文
     */
    public static function parseArticle(string $text): ?array
    {
        $text = trim(str_replace("```", '', $text));
        if(mb_strlen($text) < 50) return null;
        if(preg_match('/^#\s*(.+)/mu', $text, $m)){
            $title = trim(strval($m[1]));
        }else{
            $title = mb_substr(preg_replace('/\s+/u', '', $text), 0, 30);
        }
        $title = trim(strip_tags($title));
        $content = trim(preg_replace('/^#\s*.+\n?/u', '', $text, 1));
        if($title === '' || mb_strlen($content) < 50) return null;
        return ['title'=>mb_substr($title,0,100), 'content'=>$content];
    }
}
