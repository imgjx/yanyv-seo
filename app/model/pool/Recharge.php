<?php
/**
 * ===========================================================================
 * YanyvSEO - 充值订单模型
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;

class Recharge extends Base
{
    //数据表名（自动命名会误判为 vt_recharge，须显式指定）
    protected $name = 'pool_recharge';

    protected $pk = 'rid';

    protected $updateTime = false;

    /**
     * 订单列表（分页）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = trim($d['kw'] ?? '');
        if($kw !== '') $where[] = ['orderid|trade_no','LIKE','%'.$kw.'%'];
        if(isset($d['userid']) && intval($d['userid'])) $where[] = ['userid','=',$d['userid']];
        if(isset($d['status']) && is_numeric($d['status'])) $where[] = ['status','=',$d['status']];
        return $this->where($where)->withoutField($fields)->order('rid','desc')->paginate(intval($d['limit'] ?? 10));
    }

}
