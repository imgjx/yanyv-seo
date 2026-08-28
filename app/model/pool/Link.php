<?php
/**
 * ===========================================================================
 * YanyvSEO - 链接投放模型
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;
use think\facade\Db;

class Link extends Base
{
    //数据表名（自动命名会误判为 vt_link，须显式指定）
    protected $name = 'pool_link';

    protected $pk = 'lid';

    /**
     * 链接列表（分页）
     * @param  array  $where   查询条件（含 userid 权限）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = trim($d['kw'] ?? '');
        if($kw !== '') $where[] = ['url','LIKE','%'.$kw.'%'];
        if(isset($d['state']) && is_numeric($d['state'])) $where[] = ['state','=',$d['state']];
        if(isset($d['engine_id']) && intval($d['engine_id'])) $where[] = ['engine_id','=',$d['engine_id']];
        if(isset($d['guide_type']) && is_numeric($d['guide_type'])) $where[] = ['guide_type','=',$d['guide_type']];
        $rs = $this->where($where)->withoutField($fields)->order('lid','desc')->paginate(intval($d['limit'] ?? 10));
        // 引擎名称补充
        $engines = Engine::column('name', 'engine_id');
        foreach($rs as $v){
            $v['engine_name'] = $engines[$v['engine_id']] ?? '-';
            $v['surplus'] = max(0, $v['total'] - $v['used']);
        }
        return $rs;
    }

}
