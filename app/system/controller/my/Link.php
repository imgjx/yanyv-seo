<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 我的链接（添加/管理）
 * ===========================================================================
 */
namespace app\system\controller\my;

use app\system\controller\AdminBase;
use app\model\pool\Link as MD;
use app\model\pool\Engine as Engine;
use app\model\pool\PointsLog as PL;
use tool\Lock;

class Link extends AdminBase
{
    /**
     * 添加链接页
     */
    public function add()
    {
        $list = [];
        foreach(Engine::where('state',1)->order('listorder','asc')->select() as $e){
            $opts = array_values(array_filter(array_map('intval', explode(',', str_replace('，',',',strval($e->guide_options))))));
            sort($opts);
            $list[] = [
                'engine_id' => $e->engine_id,
                'name'      => $e->name,
                'mark'      => $e->mark,
                'price_normal'  => $e->price_normal,
                'price_301'     => $e->price_301,
                'guide_options' => $opts
            ];
        }
        $this->assign([
            'engines'  => json_encode($list),
            'points'   => dround($this->manUser['points']),
            'siteList' => \app\model\pool\Site::where('state',1)->field('siteid,title')->order('siteid','asc')->select()->toArray(),
            'canPickSite' => intval(vconfig('pool_user_site', 1))   //是否允许用户自选投放站群
        ]);
        return $this->fetch();
    }

    /**
     * 计算应扣积分
     */
    public function preview()
    {
        $d = $this->only(['@token'=>'','@engine_id/d','@guide_type/d','@guide_count/d','@num/d']);
        $rs = $this->calcCost($d['engine_id'], $d['guide_type'], max(0,$d['num']), $d['guide_count']);
        if(is_string($rs)) return $this->returnMsg($rs);
        return $this->returnMsg(['cost'=>$rs,'balance'=>dround($this->manUser['points'])],1);
    }

    /**
     * 提交链接（批量1000条，去重，实时扣积分）
     */
    public function submit()
    {
        //防频繁提交
        $ip = $this->request->ip();
        if(Lock::check(['key'=>'LINK_'.$this->manUser['userid']])) return $this->returnMsg(Lock::msg());
        $d = $this->only(['@token'=>'','@engine_id/d','@guide_type/d','@guide_count/d','@urls','site_id/d']);
        //解析行
        $rows = preg_split('/[\r\n]+/', trim(strval($d['urls'])));
        $rows = array_values(array_unique(array_filter(array_map('trim', $rows))));
        if(!$rows) return $this->returnMsg('请输入需要提交的URL');
        if(count($rows) > 1000) return $this->returnMsg('每次最多提交1000条URL');
        $num = count($rows);
        //引擎与数量校验
        $en = Engine::one(['engine_id'=>$d['engine_id'],'state'=>1]);
        if(!$en) return $this->returnMsg('请选择有效的搜索引擎');
        if($d['guide_type'] != 1 && $d['guide_type'] != 2) return $this->returnMsg('引导类型参数错误');
        if($d['guide_type'] == 2 && dround($en->price_301) <= 0) return $this->returnMsg('该引擎未开启301强引');
        //引导数必须是引擎预设选项之一
        $opts = array_values(array_filter(array_map('intval', explode(',', str_replace('，',',',strval($en->guide_options))))));
        sort($opts);
        if(!$opts) $opts = [1];
        if(!in_array(intval($d['guide_count']), $opts)) return $this->returnMsg('引导次数请从可选选项中选择');
        //计费并校验余额（单价 × 条数 × 引导次数）
        $total = $this->calcCost($d['engine_id'], $d['guide_type'], $num, intval($d['guide_count']));
        if(is_string($total)) return $this->returnMsg($total);
        if(dround($this->manUser['points']) < $total) return $this->returnMsg("积分不足：本次需 {$total} 积分，当前余额 ".dround($this->manUser['points']));
        //扣费
        $unit = $d['guide_type'] == 1 ? dround($en->price_normal) : dround($en->price_301);
        $per  = dround($unit * intval($d['guide_count']));   //单条链接费用
        $orderid = set_order_id();
        if(!PL::change($this->manUser['userid'], -$total, 'consume', "提交{$num}条链接[".($d['guide_type']==1?'普通':'301强引')."×{$d['guide_count']}次] {$en->name}", '')){
            return $this->returnMsg('积分扣除失败');
        }
        //入库（引导数选项作为每条链接的总引导额度）
        $gc = intval($d['guide_count']);
        $data = [];
        $time = time();
        foreach($rows as $u){
            $u = strip_html($u, 0);
            if(!preg_match('/^https?:\/\//i', $u)) $u = 'http://'.$u;
            $data[] = [
                'userid'      => $this->manUser['userid'],
                'url'         => $u,
                'site_id'     => intval($d['site_id']),
                'engine_id'   => $d['engine_id'],
                'guide_type'  => $d['guide_type'],
                'total'       => $gc,       //每条链接的引导次数额度
                'used'        => 0,
                'cost_points' => $per,
                'price_point' => $unit,
                'state'       => 1,
                'source_ip'   => $ip,
                'add_time'    => $time,
                'upd_time'    => $time,
            ];
        }
        //过滤已存在的URL
        $exists = MD::whereIn('url', array_column($data,'url'))->column('url');
        $insert = array_filter($data, function($v) use ($exists){ return !in_array($v['url'], $exists); });
        $skip = count($data) - count($insert);
        $ok = 0;
        foreach (array_chunk($insert, 200) as $chunk) {
            foreach($chunk as $row){
                try{
                    MD::create($row);
                    $ok++;
                }catch(\think\db\exception\PDOException $e){
                    $skip++; //重复
                }
            }
        }
        //未成功入库的部分退还积分
        $fail = $num - $ok;
        if($fail > 0 && $ok >= 0){
            PL::change($this->manUser['userid'], dround($per * $fail), 'refund', "重复URL退还({$fail}条)", '');
        }
        Lock::del(['key'=>'LINK_'.$this->manUser['userid']]);
        $msg = "提交完成：成功{$ok}条";
        if($skip) $msg .= "，忽略重复".($skip+$fail)."条";
        $msg .= "，本次共消耗".$total."积分";
        return $this->returnMsg($msg, 1);
    }

    /**
     * 我的链接列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery(['userid'=>$this->manUser['userid']]));
        $this->assign([
            'limit'  => 15,
            'engines'=> json_encode(\app\model\pool\Engine::column('name','engine_id'))
        ]);
        return $this->fetch();
    }

    /**
     * 删除自己的链接（不退积分）
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','lid'])['lid'];
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        MD::where('userid',$this->manUser['userid'])->whereIn('lid',$id)->delete();
        return $this->returnMsg("删除成功", 1);
    }

    /**
     * 暂停/恢复
     */
    public function pause()
    {
        $d = $this->only(['@token'=>'','@lid/d','av/d']);
        $rs = MD::one(['lid'=>$d['lid'],'userid'=>$this->manUser['userid']]);
        if(!$rs) return $this->returnMsg("数据不存在");
        $state = intval($d['av']) ? 1 : 3; //1进行中/3暂停
        return $this->returnMsg($rs->save(['state'=>$state]) !== false ? ($state==1 ? '已恢复' : '已暂停') : "操作失败", 1);
    }

    /**
     * 费用计算：单价 × 条数 × 每条链接引导次数
     */
    private function calcCost(int $engineId, int $type, int $num, int $count = 1)
    {
        $en = Engine::one(['engine_id'=>$engineId,'state'=>1]);
        if(!$en) return '请选择有效的搜索引擎';
        $price = $type == 1 ? dround($en->price_normal) : dround($en->price_301);
        if($price <= 0) return '该类型未开放计费，请联系管理员';
        return dround($price * $num * max(1, $count));
    }

}
