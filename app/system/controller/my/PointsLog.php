<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 财务记录（积分明细）
 * ===========================================================================
 */
namespace app\system\controller\my;

use app\system\controller\AdminBase;
use app\model\pool\PointsLog as MD;



class PointsLog extends AdminBase
{
    /**
     * 财务记录列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery(['userid'=>$this->manUser['userid']]));
        $this->assign('limit', 15);
        return $this->fetch();
    }

}
