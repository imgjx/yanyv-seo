<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 引导计费记录（管理员）
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\BillingLog as MD;

class Billing extends AdminBase
{
    /**
     * 记录列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery());
        //统计概览
        $today = strtotime(date('Y-m-d'));
        $this->assign([
            'limit'       => 20,
            'sumToday'    => MD::where('add_time','>=',$today)->count(),
            'pointsToday' => MD::where('add_time','>=',$today)->sum('points'),
            'sumTotal'    => MD::count(),
        ]);
        return $this->fetch();
    }

}
