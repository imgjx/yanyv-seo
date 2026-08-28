<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 引导计费流水模型
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;

class BillingLog extends Base
{
    //数据表名（自动命名会误判为 vt_billing_log，须显式指定）
    protected $name = 'pool_billing_log';

    protected $pk = 'bid';

    protected $updateTime = false;

    /**
     * 引导记录列表（分页）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = trim($d['kw'] ?? '');
        if($kw !== '') $where[] = ['spider_ip|spider_ua','LIKE','%'.$kw.'%'];
        if(isset($d['userid']) && intval($d['userid'])) $where[] = ['userid','=',$d['userid']];
        if(strpos(($d['sotime'] ?? ''),' - ') !== false){
            $t = explode(' - ', $d['sotime']);
            $where[] = ['add_time','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['add_time','<=',strtotime($t[1]." 23:59:59")];
        }
        $rs = $this->where($where)->withoutField($fields)->order('bid','desc')->paginate(intval($d['limit'] ?? 10));
        //补充引擎名称与来源站点域名
        $engines = Engine::column('name', 'engine_id');
        foreach($rs as $v){
            $v['engine_name'] = $engines[$v['engine_id']] ?? '-';
            if(!empty($v['site_id'])) $v['site_domain'] = Site::where('siteid',$v['site_id'])->value('domain') ?: '';
        }
        return $rs;
    }

}
