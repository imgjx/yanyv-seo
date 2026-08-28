<?php
/**
 * ===========================================================================
 * YanyvSEO - 全部链接管理（管理员）
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\Link as MD;

class Link extends AdminBase
{
    /**
     * 链接列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery());
        $this->assign([
            'limit'   => 10,
            'engines' => json_encode(\app\model\pool\Engine::column('name','engine_id'))
        ]);
        return $this->fetch();
    }

    /**
     * 删除链接
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','lid'])['lid'];
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        MD::destroy($id);
        return $this->returnMsg("删除成功", 1);
    }

    /**
     * 快编状态
     */
    public function up()
    {
        $d = $this->only(['@token'=>'','@lid/d','av','af']);
        if(!in_array($d['af'],['state'])) return $this->returnMsg("参数错误");
        $rs = MD::one(['lid'=>$d['lid']]);
        if(!$rs) return $this->returnMsg("数据不存在");
        return $this->returnMsg($rs->save([$d['af']=>intval($d['av'])]) !== false ? "设置成功" : "设置失败", 1);
    }

    /**
     * 导出txt
     */
    public function out()
    {
        $d = $this->only(['@token'=>'','state','engine_id'],'get');
        $where = [];
        if(isset($d['state']) && is_numeric($d['state'])) $where[] = ['state','=',$d['state']];
        if(intval($d['engine_id'] ?? 0)) $where[] = ['engine_id','=',$d['engine_id']];
        $list = MD::where($where)->field('url')->limit(50000)->select()->toArray();
        $str = implode(PHP_EOL, array_column($list, 'url'));
        download($str, 'links_'.date('YmdHis').'.txt');
    }

}
