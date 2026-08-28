<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - CMS文章分组管理（抓取源配置 + 伪原创词库 + 执行抓取）
 * ===========================================================================
 */
namespace app\system\controller\cms;

use app\system\controller\AdminBase;
use app\model\cms\Group as MD;

class Group extends AdminBase
{
    /**
     * 分组列表
     */
    public function index(string $do = '')
    {
        if($do == 'json'){
            $rows = (new MD())->listQuery();
            foreach($rows as &$r){
                $r['articles'] = \app\model\cms\Article::where('groupid',$r['groupid'])->count();
                $r['pseudo_lines'] = count(array_filter(array_map('trim', explode("\n", strval($r['pseudo_lib'])))));
            }
            return $this->returnMsg($rows);
        }
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 添加/编辑分组
     */
    public function save()
    {
        $d = $this->only(['@token'=>'','@groupid/d','@title/*/{2,30}/分组名称需2-30位字符','list_url','list_rule','title_rule','content_rule','charset','pseudo_lib','@state/d','@listorder/d']);
        unset($d['token']);
        //同名校验
        $ex = MD::where('title',$d['title']);
        if($d['groupid']) $ex = $ex->where('groupid','<>',$d['groupid']);
        if($ex->count()) return $this->returnMsg('分组名称已存在');
        if($d['groupid']){
            if(!MD::one(['groupid'=>$d['groupid']])) return $this->returnMsg('分组不存在');
            MD::where('groupid',$d['groupid'])->update($d);
            return $this->returnMsg('修改成功',1);
        }
        unset($d['groupid']);
        $d['add_time'] = time();
        $d['creator'] = $this->manUser['username'];
        MD::create($d);
        return $this->returnMsg('添加成功',1);
    }

    /**
     * 删除分组（连带文章）
     */
    public function del()
    {
        $d = $this->only(['@token'=>'','@groupid/d']);
        if(!MD::one(['groupid'=>$d['groupid']])) return $this->returnMsg('分组不存在');
        \app\model\cms\Article::where('groupid',$d['groupid'])->delete();
        MD::where('groupid',$d['groupid'])->delete();
        return $this->returnMsg('删除成功',1);
    }

    /**
     * 执行抓取
     */
    public function crawl()
    {
        $d = $this->only(['@token'=>'','@groupid/d']);
        $g = MD::one(['groupid'=>$d['groupid']]);
        if(!$g) return $this->returnMsg('分组不存在');
        if(trim(strval($g->list_url)) === '') return $this->returnMsg('请先配置抓取列表页URL');
        set_time_limit(120);
        return $this->returnMsg(MD::crawl($g),1);
    }
}
