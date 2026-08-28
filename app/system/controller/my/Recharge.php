<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 会员在线充值（易支付兼容API）
 * 模式：fixed仅固定金额 / custom仅自定义金额 / mixed混合
 * 固定金额档位 pool_fixed_amounts 每行 金额:到账积分
 * 自定义金额按 pool_exchange_rate（1元=N积分）换算，默认100
 * ===========================================================================
 */
namespace app\system\controller\my;

use app\system\controller\AdminBase;
use app\model\pool\Recharge as MD;
use tool\Lock;

class Recharge extends AdminBase
{
    /**
     * 充值页
     */
    public function index()
    {
        $mode = vconfig('pool_pay_mode','mixed');
        $mode = in_array($mode, ['fixed','custom','mixed']) ? $mode : 'mixed';
        //固定金额档位解析 10:1000\n30:3000 => [{money,points}]
        $fixed = [];
        foreach(explode("\n", vconfig('pool_fixed_amounts','')) as $line){
            $line = trim($line);
            if(strpos($line, ':') === false) continue;
            list($m, $p) = explode(':', $line, 2);
            if(is_numeric($m) && is_numeric($p)) $fixed[] = ['money'=>floatval($m), 'points'=>floatval($p)];
        }
        $this->assign([
            'mode'   => $mode,
            'fixed'  => json_encode($fixed),
            'rate'   => floatval(vconfig('pool_exchange_rate',100)),
            'min'    => floatval(vconfig('pool_pay_min',1)),
            'max'    => floatval(vconfig('pool_pay_max',10000)),
            'points' => dround($this->manUser['points'])
        ]);
        return $this->fetch();
    }

    /**
     * 创建订单并返回易支付跳转地址
     */
    public function order()
    {
        //防频繁提交
        if(Lock::check(['key'=>'PAY_'.$this->manUser['userid']],5)) return $this->returnMsg(Lock::msg());
        $api = rtrim(strval(vconfig('pool_pay_api')), '/');
        $pid = strval(vconfig('pool_pay_pid'));
        $key = strval(vconfig('pool_pay_key'));
        if(!$key) return $this->returnMsg('系统未配置支付接口，请联系管理员！');
        if(!$api || !$pid) return $this->returnMsg('支付接口地址或商户PID未配置完整，请联系管理员！');
        $d = $this->only(['@token'=>'','@money/f','type/s']);
        $mode = vconfig('pool_pay_mode','mixed');
        $fixed = $this->fixedList();
        //匹配固定档位：金额存在即认为是固定充值，否则走比例换算
        $money = dround($d['money']);
        if($money <= 0) return $this->returnMsg('请输入正确的充值金额');
        $isFixed = false; $points = 0;
        foreach($fixed as $f){
            if(abs($f['money'] - $money) < 0.001){ $isFixed = true; $points = $f['points']; break; }
        }
        if($isFixed && $mode == 'custom') return $this->returnMsg('当前仅支持自选金额充值');
        if(!$isFixed){
            if($mode == 'fixed') return $this->returnMsg('请选择系统提供的固定金额档位');
            $rate = floatval(vconfig('pool_exchange_rate',100));
            if($rate <= 0) return $this->returnMsg('系统积分兑换比例配置有误！');
            if($money < floatval(vconfig('pool_pay_min',1))) return $this->returnMsg('最低充值 '.floatval(vconfig('pool_pay_min',1)).' 元');
            if($money > floatval(vconfig('pool_pay_max',10000))) return $this->returnMsg('最高充值 '.floatval(vconfig('pool_pay_max',10000)).' 元');
            $points = dround($money * $rate);
        }else{
            //固定档位不限制 min/max
        }
        $points = dround(max(1, $points));
        //创建本地订单(待支付)
        $orderid = set_order_id();
        MD::create([
            'orderid'  => $orderid,
            'userid'   => $this->manUser['userid'],
            'money'    => $money,
            'points'   => $points,
            'paytype'  => in_array(strval($d['type']), ['alipay','wxpay','qqpay']) ? $d['type'] : '',
            'status'   => 0,
            'add_time' => time(),
        ]);
        //组装易支付提交参数并签名（按ASCII升序）
        $host = $this->request->scheme().'://'.$this->request->host();
        $param = [
            'pid'          => $pid,
            'type'         => in_array(strval($d['type']), ['alipay','wxpay','qqpay']) ? $d['type'] : 'alipay',
            'out_trade_no' => $orderid,
            'notify_url'   => $host.'/pay/notify',
            'return_url'   => $host.'/pay/return',
            'name'         => vconfig('site_title','烟雨蜘蛛池').'-积分充值',
            'money'        => number_format($money, 2, '.', ''),
        ];
        $str = '';
        ksort($param, SORT_STRING);
        foreach($param as $k => $v) $str .= ($str ? '&' : '').$k.'='.$v;
        $param['sign'] = strtoupper(md5($str.$key));
        $param['sign_type'] = 'MD5';
        Lock::del(['key'=>'PAY_'.$this->manUser['userid']]);
        //兼容网关根地址与完整提交地址两种配置写法
        $submit = preg_match('#/submit\.php$#i', $api) ? $api : $api.'/submit.php';
        return $this->returnMsg(['url'=>$submit.'?'.http_build_query($param), 'orderid'=>$orderid],1);
    }

    /**
     * 轮询订单状态（前端支付后查单）
     */
    public function query()
    {
        $d = $this->only(['@token'=>'','@orderid/a']);
        $rs = MD::one(['orderid'=>$d['orderid'],'userid'=>$this->manUser['userid']]);
        if(!$rs) return $this->returnMsg('订单不存在');
        return $this->returnMsg(['status'=>intval($rs->status),'points'=>dround($rs->points)],1);
    }

    /**
     * 固定金额档位列表
     */
    protected function fixedList(): array
    {
        $list = [];
        foreach(explode("\n", vconfig('pool_fixed_amounts','')) as $line){
            $line = trim($line);
            if(strpos($line, ':') === false) continue;
            list($m, $p) = explode(':', $line, 2);
            if(is_numeric($m) && is_numeric($p)) $list[] = ['money'=>floatval($m), 'points'=>floatval($p)];
        }
        usort($list, fn($a,$b)=>$a['money']<=>$b['money']);
        return $list;
    }

}
