<?php
/**
 * ===========================================================================
 * YanyvSEO - 积分明细模型
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;
use app\model\system\SystemManager;
use think\facade\Db;

class PointsLog extends Base
{
    //数据表名（自动命名会误判为 vt_points_log，须显式指定）
    protected $name = 'pool_points_log';

    protected $pk = 'pid';

    protected $updateTime = false;

    /**
     * 明细列表（分页）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = trim($d['kw'] ?? '');
        if($kw !== '') $where[] = ['remark|rel_order','LIKE','%'.$kw.'%'];
        if(isset($d['userid']) && intval($d['userid'])) $where[] = ['userid','=',$d['userid']];
        if(!empty($d['type'])) $where[] = ['type','=',$d['type']];
        if(strpos(($d['sotime'] ?? ''),' - ') !== false){
            $t = explode(' - ', $d['sotime']);
            $where[] = ['add_time','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['add_time','<=',strtotime($t[1]." 23:59:59")];
        }
        return $this->where($where)->withoutField($fields)->order('pid','desc')->paginate(intval($d['limit'] ?? 10));
    }

    /**
     * 积分变动（带流水记录，事务安全）
     * @param  int     $userid  用户ID
     * @param  float   $points  变动值 正加负减
     * @param  string  $type    类型 recharge/consume/admin/refund
     * @param  string  $remark  备注
     * @param  string  $order   关联单号
     * @return bool
     */
    public static function change(int $userid, float $points, string $type, string $remark = '', string $order = '')
    {
        if($points == 0) return false;
        $manager = SystemManager::one(['userid'=>$userid], 'userid,points');
        if(!$manager) return false;
        $before = dround($manager->points);
        $after  = dround($before + $points);
        if($after < 0) return false; //余额不足
        self::beginTrans();
        $res = SystemManager::where('userid',$userid)->update(['points'=>Db::raw('points'.($points>0 ? '+' : '-').abs($points))]);
        if(!$res){
            self::rollbackTrans();
            return false;
        }
        self::create([
            'userid'       => $userid,
            'type'         => $type,
            'points'       => $points,
            'before_points'=> $before,
            'after_points' => $after,
            'remark'       => $remark,
            'rel_order'    => $order
        ]);
        self::commitTrans();
        return true;
    }

}
