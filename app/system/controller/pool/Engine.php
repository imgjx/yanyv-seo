<?php
/**
 * ===========================================================================
 * YanyvSEO - SEO引擎与计费管理
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\Engine as MD;

class Engine extends AdminBase
{
    /**
     * 引擎列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery());
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 组装计费字段
     */
    private function build(array &$d)
    {
        $d['price_normal']  = dround($d['price_normal']);
        $d['price_301']     = dround($d['price_301']);
        $opts = array_filter(array_map('intval', explode(',', str_replace('，', ',', strval($d['guide_options'] ?? '')))));
        sort($opts);
        if(!$opts) return '引导数选项不能为空，如：1000,3000,5000,10000';
        $d['guide_options'] = implode(',', $opts);
        if($d['match_mode'] != 2 && !$d['ua_keywords']) return '该匹配模式下 UA 关键词不能为空';
        if(in_array($d['match_mode'], [2,3]) && !trim($d['ip_rules'])) return '该匹配模式下 IP 规则不能为空';
        return '';
    }

    /**
     * 添加引擎
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','@name/*/{1,30}/引擎名称','@mark/*/{2,20}/标识需2-20位字母数字/1,2/_-','@match_mode/d','ua_keywords/h','ip_rules','@price_normal/r','@price_301/r','guide_options/h','listorder/d']);
        if(MD::one(['mark'=>$d['mark']])) return $this->returnMsg("引擎标识已存在");
        if($err = $this->build($d)) return $this->returnMsg($err);
        $d['creator'] = $this->manUser['username'];
        MD::create($d);
        return $this->returnMsg("添加成功", 1);
    }

    /**
     * 编辑引擎
     */
    public function edit()
    {
        $d = $this->only(['@token'=>'','@engine_id/d','@name/*/{1,30}/引擎名称','@match_mode/d','ua_keywords/h','ip_rules','@price_normal/r','@price_301/r','guide_options/h','listorder/d','state/d']);
        $rs = MD::one(['engine_id'=>$d['engine_id']]);
        if(!$rs) return $this->returnMsg("数据不存在");
        if($err = $this->build($d)) return $this->returnMsg($err);
        $d['editor'] = $this->manUser['username'];
        return $this->returnMsg($rs->save($d) !== false ? "编辑成功" : "编辑失败", 1);
    }

    /**
     * 删除引擎
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','engine_id'])['engine_id'];
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        MD::destroy($id);
        return $this->returnMsg("删除成功", 1);
    }

}
