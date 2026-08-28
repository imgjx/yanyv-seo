<?php
/**
 * ===========================================================================
 * YanyvSEO - CMS文章管理（列表 + 手动单篇录入）
 * ===========================================================================
 */
namespace app\system\controller\cms;

use app\system\controller\AdminBase;
use app\model\cms\Article as MD;

class Article extends AdminBase
{
    /**
     * 文章列表
     */
    public function index(string $do = '')
    {
        if($do == 'json'){
            $d = $this->only(['groupid'],'get');
            $where = [];
            if(intval($d['groupid'])) $where[] = ['groupid','=',intval($d['groupid'])];
            $rows = (new MD())->listQuery($where);
            $groups = \app\model\cms\Group::allGroups();
            $map = array_column($groups,'title','groupid');
            foreach($rows as &$r){ $r['group_name'] = $map[$r['groupid']] ?? '未知'; $r['size'] = word_count(strval($r['content'])); }
            return $this->returnMsg($rows);
        }
        $this->assign([
            'limit'  => 10,
            'groups' => \app\model\cms\Group::allGroups()
        ]);
        return $this->fetch();
    }

    /**
     * 保存文章（手动单篇录入/编辑）
     */
    public function save()
    {
        $d = $this->only(['@token'=>'','@articleid/d','@groupid/d','@title/*/{2,100}/文章标题需2-100位字符','content','@state/d'],'post','strip_html',false);
        $d['content'] = $this->request->post('content','','trim');
        if($d['groupid'] && !\app\model\cms\Group::one(['groupid'=>$d['groupid']])) return $this->returnMsg('所属分组不存在');
        if($d['articleid']){
            if(!MD::one(['articleid'=>$d['articleid']])) return $this->returnMsg('文章不存在');
            MD::where('articleid',$d['articleid'])->update($d);
            return $this->returnMsg('修改成功',1);
        }
        unset($d['articleid']);
        $d['add_time'] = time();
        MD::create($d);
        return $this->returnMsg('添加成功',1);
    }

    /**
     * 删除文章
     */
    public function del()
    {
        $d = $this->only(['@token'=>'','@articleid/d']);
        MD::where('articleid',$d['articleid'])->delete();
        return $this->returnMsg('删除成功',1);
    }

    /**
     * 查看文章详情（后台预览伪原创效果）
     */
    public function view()
    {
        $d = $this->only(['@articleid/d']);
        $a = (new MD())->displayContent($d['articleid']);
        if(!$a) return $this->returnMsg('文章不存在');
        return $this->returnMsg(['title'=>$a['title'],'content'=>$a['content']],1);
    }
}
