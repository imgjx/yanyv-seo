<?php
/**
 * ===========================================================================
 * YanyvSEO - 易支付兼容回调控制器（免登录）
 * 异步通知 /pay/notify  同步跳转 /pay/return
 * ===========================================================================
 */
namespace app\index\controller;

use app\BaseController;
use app\model\pool\Recharge as RCM;
use app\model\pool\PointsLog as PL;
use app\model\system\SystemManager as Manager;
use think\Response;

class Pay extends BaseController
{
    /**
     * 异步通知：验签 -> 补积分
     */
    public function notify()
    {
        $d = $this->request->param();
        $key = vconfig('pool_pay_key');
        if(!$key || empty($d['out_trade_no'])) return $this->echoNotify('fail');
        //易支付V1验签：参数按ASCII升序排列，sign/sign_type与空值不参与，md5(a=b&c=d...+KEY)小写
        $sign = strtolower(strval($d['sign'] ?? ''));
        unset($d['sign'], $d['sign_type']);
        ksort($d, SORT_STRING);
        $str = '';
        foreach($d as $k => $v){
            if($v === '' || $v === null || is_array($v)) continue;
            $str .= ($str ? '&' : '').$k.'='.$v;
        }
        if(md5($str.$key) !== $sign) return $this->echoNotify('fail');
        if(strval($d['trade_status'] ?? '') != 'TRADE_SUCCESS') return $this->echoNotify('success');
        $rs = RCM::one(['orderid'=>strval($d['out_trade_no'])]);
        if(!$rs) return $this->echoNotify('fail');
        //重复通知忽略
        if($rs->status == 1) return $this->echoNotify('success');
        //金额校验(允许一分误差)
        if(abs(floatval($d['money']) - floatval($rs->money)) > 0.01) return $this->echoNotify('fail');
        //补加积分 + 置为已支付
        PL::change($rs->userid, floatval($rs->points), 'recharge', '在线充值 '.$rs->orderid.' 到账', $rs->orderid);
        Manager::where('userid',$rs->userid)->inc('total_points', $rs->points)->update();
        $rs->save([
            'status'   => 1,
            'trade_no' => strip_sql(strval($d['trade_no'] ?? '')),
            'paytype'  => strip_sql(strval($d['type'] ?? '')),
            'callback' => json_encode($d, JSON_UNESCAPED_UNICODE),
            'pay_time' => time(),
        ]);
        return $this->echoNotify('success');
    }

    /**
     * 同步跳转：回充值页
     */
    public function returnx()
    {
        $url = VT_DIR.'/system/'.(array_search("system", config('app.app_map')) ?: 'system').'/my.recharge/index';
        throw new \think\exception\HttpResponseException(Response::create()->header(['Location'=>$url]));
    }

    /**
     * 输出回调应答
     */
    protected function echoNotify(string $msg)
    {
        throw new \think\exception\HttpResponseException(Response::create($msg));
    }

}
