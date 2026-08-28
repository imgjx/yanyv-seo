<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - SEO引擎规则模型
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;

class Engine extends Base
{
    //数据表名（自动命名会误判为 vt_engine，须显式指定）
    protected $name = 'pool_engine';

    protected $pk = 'engine_id';

    /**
     * 引擎列表（分页）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        if($kw !== '') $where[] = ['name|mark','LIKE','%'.$kw.'%'];
        return $this->where($where)->withoutField($fields)->order('listorder','asc')->order('engine_id','asc')->paginate(intval($d['limit'] ?? 10));
    }

}
