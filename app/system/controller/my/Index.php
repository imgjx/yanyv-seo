<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 我的概览
 * ===========================================================================
 */
namespace app\system\controller\my;

use app\system\controller\AdminBase;
use app\model\pool\Link as Link;
use app\model\pool\PointsLog as PL;
use app\model\pool\BillingLog as BL;
use think\facade\Db;


class Index extends AdminBase
{
    /**
     * 概览首页
     */
    public function index()
    {
        $uid = $this->manUser['userid'];
        $today = strtotime(date('Y-m-d'));
        //链接统计
        $surplus = Db::name('pool_link')->where('userid',$uid)->where('state',1)->field("COALESCE(SUM(total-used),0) AS s")->find();
        $stats = [
            'total'    => Link::where('userid',$uid)->count(),
            'running'  => Link::where('userid',$uid)->where('state',1)->count(),
            'done'     => Link::where('userid',$uid)->where('state',2)->count(),
            'surplus'  => dround(floatval($surplus['s'] ?? 0)),
        ];
        //今日引导与消耗
        $stats['guide_today'] = BL::where('userid',$uid)->where('add_time','>=',$today)->count();
        $pointsToday = PL::where('userid',$uid)->where('type','consume')->where('add_time','>=',$today)->sum('points');
        $stats['cost_today'] = dround($pointsToday);
        $this->assign([
            'stats'  => $stats,
            'points' => dround($this->manUser['points']),
            'recent' => Link::where('userid',$uid)->order('lid','desc')->limit(5)->select()->toArray(),
            'engines'=> \app\model\pool\Engine::column('name','engine_id')
        ]);
        return $this->fetch();
    }

}
