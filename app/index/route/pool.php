<?php
// +----------------------------------------------------------------------
// | 烟雨蜘蛛池系统 - index应用路由
// | 站群模板简易路由： /wz/{模板名}/{cid} -> 渲染 public/{模板名}/{模板名}.tpl?cid=cid
// | 易支付回调：      /pay/notify -> 异步通知(验签+积分到账)
// +----------------------------------------------------------------------
use think\facade\Route;

// 站群渲染路由（/wz -> 默认模板目录 index.tpl）
Route::rule('wz/:name', 'wz/render');
Route::rule('wz/:name/:cid', 'wz/render')->pattern(['cid' => '[0-9a-zA-Z\-]+']);

// 易支付异步回调与同步跳转
Route::rule('pay/notify', 'pay/notify');
Route::rule('pay/return', 'pay/returnx');
