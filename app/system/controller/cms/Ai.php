<?php
/**
 * ===========================================================================
 * YanyvSEO - AI内容生成 / AI文章重写（任务制 + 分批step + 状态页轮询）
 * 内容管理 -> AI内容生成 / 文章管理 -> 重写文章
 * 提示词每次由前端传入；{date} 自动注入当前年月日
 * ===========================================================================
 */
namespace app\system\controller\cms;

use app\system\controller\AdminBase;
use app\model\cms\Article as MD;
use app\model\cms\Group as GM;
use app\model\cms\AiTask as TM;
use app\model\system\SystemSetting as S;
use tool\Ai as AiTool;

class Ai extends AdminBase
{
    /**
     * 内置默认生成提示词：Markdown文章，拟人化、无emoji、无AI味
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
     * 内置默认重写提示词：结构与原文完全不同
     */
    const DEFAULT_REWRITE = '你是一名从业十年的中文编辑。请在保留原文核心信息与观点的前提下，把下面这篇文章彻底重写：
1. 采用与原文完全不同的行文结构（段落划分、小标题、叙述顺序都要重新组织）；
2. 换一种表达方式与语气，语言自然拟人化，像真人重写一篇新稿；
3. 严禁使用任何emoji，禁止出现"作为AI""人工智能助手"等字眼，禁止"总而言之""综上所述""首先/其次/最后"等模板化套话；
4. 直接输出Markdown，第一行用 "# 标题" 给出与原文不同的新标题；
5. 正文中自然融入当前日期：{date}。

原文：
{content}';

    /**
     * AI内容生成页
     */
    public function index()
    {
        $this->assign([
            'ready'  => AiTool::ready(),
            'groups' => GM::allGroups(),
            'prompt' => trim(strval(vconfig('ai_article_prompt',''))) ?: self::DEFAULT_PROMPT,
            'date'   => date('Y年m月d日')
        ]);
        return $this->fetch();
    }

    /**
     * AI任务状态页（iframe 打开，轮询 step 推进并展示进度）
     */
    public function status()
    {
        $taskid = intval(input('taskid/d'));
        $task = $taskid ? TM::one(['taskid'=>$taskid]) : null;
        if(!$task) return $this->returnMsg('任务不存在');
        $this->assign(['taskid'=>$taskid, 'type'=>strval($task->type), 'root'=>'cms.ai/']);
        return $this->fetch();
    }

    /**
     * 文章重写提示词配置页
     */
    public function prompt()
    {
        $this->assign([
            'ready'  => AiTool::ready(),
            'prompt' => trim(strval(vconfig('ai_rewrite_prompt',''))) ?: self::DEFAULT_REWRITE,
            'date'   => date('Y年m月d日')
        ]);
        return $this->fetch();
    }

    /**
     * 保存生成提示词
     */
    public function save()
    {
        $d = $this->only(['@token'=>'','@prompt/s'], 'post', '', false);
        $this->saveSetting('ai_article_prompt', 'AI文章提示词', trim(strval($d['prompt'])), 5);
        return $this->returnMsg('提示词已保存', 1);
    }

    /**
     * 保存重写提示词
     */
    public function saveRewrite()
    {
        $d = $this->only(['@token'=>'','@prompt/s'], 'post', '', false);
        $this->saveSetting('ai_rewrite_prompt', '文章重写提示词', trim(strval($d['prompt'])), 6);
        return $this->returnMsg('提示词已保存', 1);
    }

    /**
     * 创建生成任务（不立即执行，由状态页 step 推进）
     */
    public function gen()
    {
        if(!AiTool::ready()) return $this->returnMsg('请先在 系统管理->AI配置 中配置API地址与密钥');
        $d = $this->only(['@token'=>'','@groupid/d','@rounds/d','@concurrency/d','@prompt/s'],'post','',false);
        $groupid = intval($d['groupid']);
        if(!$groupid || !GM::one(['groupid'=>$groupid])) return $this->returnMsg('请选择要保存到的文章分组');
        $rounds = max(1, min(50, intval($d['rounds'] ?? 1)));
        $conc   = max(1, min(10, intval($d['concurrency'] ?? 3)));
        $prompt = trim(strval($d['prompt']));
        if($prompt === '') $prompt = self::DEFAULT_PROMPT;
        $task = TM::create([
            'type'=>'gen','groupid'=>$groupid,'ids'=>'','prompt'=>$prompt,
            'concurrency'=>$conc,'total'=>$rounds,'done'=>0,'fail'=>0,'offset'=>0,
            'logs'=>'','state'=>0,
        ]);
        return $this->returnMsg(['taskid'=>intval($task->taskid)], 1);
    }

    /**
     * 创建重写任务
     */
    public function rewrite()
    {
        if(!AiTool::ready()) return $this->returnMsg('请先在 系统管理->AI配置 中配置API地址与密钥');
        $d = $this->only(['@token'=>'','@ids/a','@concurrency/d'],'post','',false);
        $ids = array_values(array_filter(array_map('intval', (array)($d['ids'] ?? []))));
        if(!$ids) return $this->returnMsg('请选择需要重写的文章');
        $conc = max(1, min(10, intval($d['concurrency'] ?? 3)));
        $valid = [];
        foreach(MD::where([['articleid','in',$ids]])->column('articleid') as $id){
            $valid[] = intval($id);
        }
        if(!$valid) return $this->returnMsg('所选文章不存在');
        $task = TM::create([
            'type'=>'rewrite','groupid'=>0,'ids'=>json_encode($valid),'prompt'=>'',
            'concurrency'=>$conc,'total'=>count($valid),'done'=>0,'fail'=>0,'offset'=>0,
            'logs'=>'','state'=>0,
        ]);
        return $this->returnMsg(['taskid'=>intval($task->taskid)], 1);
    }

    /**
     * 推进一个批次（状态页轮询调用）；返回任务状态
     */
    public function step()
    {
        $d = $this->only(['@token'=>'','@taskid/d'],'post','',false);
        $task = TM::one(['taskid'=>intval($d['taskid'])]);
        if(!$task) return $this->returnMsg('任务不存在');
        if(intval($task->state) === 1) return $this->returnMsg(['task'=>$task->status()], 1);
        @set_time_limit(0);
        $offset = intval($task->offset);
        $total  = intval($task->total);
        $conc   = max(1, min(10, intval($task->concurrency)));
        if($offset >= $total){
            $task->save(['state'=>1,'upd_time'=>time()]);
            $task->pushLog('任务完成');
            return $this->returnMsg(['task'=>$task->status()], 1);
        }
        $date = date('Y年m月d日');
        $done = intval($task->done);
        $fail = intval($task->fail);
        if($task->type === 'gen'){
            $prompt = str_replace('{date}', $date, strval($task->prompt));
            $groupid = intval($task->groupid);
            $tasks = [];
            $end = min($total, $offset + $conc);
            for($i = $offset + 1; $i <= $end; $i++){
                $tasks[$i] = $prompt."\n\n本篇序号：第{$i}篇（共{$total}篇），请自拟与其他篇目完全不同的主题与切入角度。";
            }
            $rs = AiTool::chatMulti($tasks, $conc);
            foreach($rs as $k => $text){
                $art = self::parseArticle(strval($text));
                if($art === null){ $fail++; $task->pushLog("第{$k}篇生成失败(内容为空或格式不符)"); continue; }
                if(MD::one(['groupid'=>$groupid,'title'=>$art['title']])){ $fail++; $task->pushLog("第{$k}篇标题重复：{$art['title']}"); continue; }
                MD::create(['groupid'=>$groupid,'title'=>$art['title'],'content'=>$art['content'],'state'=>1]);
                $done++; $task->pushLog("第{$k}篇已保存：{$art['title']}");
            }
            $offset = $end;
        }else{
            $tpl = trim(strval(vconfig('ai_rewrite_prompt','')));
            if($tpl === '') $tpl = self::DEFAULT_REWRITE;
            $ids = json_decode(strval($task->ids), true) ?: [];
            $end = min(count($ids), $offset + $conc);
            $tasks = [];
            for($i = $offset; $i < $end; $i++){
                $id = intval($ids[$i]);
                $a = MD::one(['articleid'=>$id]);
                if(!$a){ $fail++; $task->pushLog("文章{$id}不存在，跳过"); continue; }
                $tasks[$id] = str_replace(['{date}','{content}'], [$date, strval($a->content)], $tpl);
            }
            $rs = AiTool::chatMulti($tasks, $conc);
            foreach($rs as $id => $text){
                $art = self::parseArticle(strval($text));
                if($art === null){ $fail++; $task->pushLog("文章{$id}重写失败"); continue; }
                MD::where('articleid', $id)->update(['title'=>$art['title'],'content'=>$art['content'],'upd_time'=>time()]);
                $done++; $task->pushLog("文章{$id}已重写：{$art['title']}");
            }
            $offset = $end;
        }
        $task->save(['done'=>$done,'fail'=>$fail,'offset'=>$offset,'upd_time'=>time()]);
        if($offset >= intval($task->total)){
            $task->save(['state'=>1,'upd_time'=>time()]);
            $task->pushLog("任务完成：成功{$done}，失败{$fail}");
        }
        return $this->returnMsg(['task'=>$task->status()], 1);
    }

    /**
     * 查询任务状态
     */
    public function state()
    {
        $task = TM::one(['taskid'=>intval(input('taskid/d'))]);
        if(!$task) return $this->returnMsg('任务不存在');
        return $this->returnMsg(['task'=>$task->status()], 1);
    }

    /**
     * 保存系统配置项（存在则更新，否则创建）
     */
    protected function saveSetting(string $name, string $title, string $value, int $listorder): void
    {
        $row = S::one([['name','=',$name]]);
        if($row){
            $row->save(['value'=>$value, 'upd_time'=>time(), 'editor'=>$this->manUser['username']]);
        }else{
            S::create(['name'=>$name,'title'=>$title,'group'=>'ai','type'=>'textarea','value'=>$value,'listorder'=>$listorder,'state'=>1,'creator'=>$this->manUser['username'],'add_time'=>time()]);
        }
        S::cache(1);
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
