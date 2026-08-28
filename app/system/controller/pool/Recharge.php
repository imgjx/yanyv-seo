<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 充值订单/积分管理（管理员）
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\Recharge as MD;
use app\model\pool\PointsLog as PL;
use app\model\system\SystemManager as Manager;

class Recharge extends AdminBase
{
    /**
     * 订单列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery());
        $this->assign([
            'limit'  => 10,
            'status' => json_encode(['待支付','已支付','已失效'])
        ]);
        return $this->fetch();
    }

    /**
     * 手动标记支付状态（补单）
     */
    public function mark()
    {
        $d = $this->only(['@token'=>'','@rid/d','av/d']);
        $rs = MD::one(['rid'=>$d['rid']]);
        if(!$rs) return $this->returnMsg("数据不存在");
        //置为已支付时补加积分
        if($d['av'] == 1 && $rs->status == 0){
            PL::change($rs->userid, floatval($rs->points), 'recharge', '订单补录 '.$rs->orderid, $rs->orderid);
            Manager::where('userid',$rs->userid)->inc('total_points', $rs->points)->update();
        }
        return $this->returnMsg($rs->save(['status'=>$d['av'],'pay_time'=>time()]) !== false ? "操作成功" : "操作失败", 1);
    }

    /**
     * 删除订单
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','rid'])['rid'];
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        MD::destroy($id);
        return $this->returnMsg("删除成功", 1);
    }

    /**
     * 用户积分调整
     */
    public function points()
    {
        $d = $this->only(['@token'=>'','@userid/d','points/r','@remark/h']);
        $manager = Manager::one(['userid'=>$d['userid']], 'userid,username');
        if(!$manager) return $this->returnMsg("用户不存在");
        $res = PL::change($d['userid'], dround($d['points']), 'admin', '管理员调整'.(!empty($d['remark']) ? '：'.$d['remark'] : ''));
        return $this->returnMsg($res ? "调整成功" : "调整失败（余额不足？）", $res ? 1 : 0);
    }

}
