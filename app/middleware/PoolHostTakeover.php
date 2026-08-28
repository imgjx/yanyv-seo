<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 站群独立域名接管中间件（index应用级）
 * 被站群绑定的域名（含泛解析），任何路径访问都直接进入站群渲染；
 * 系统/后台/用户中心仅通过主域名访问，保证站群域名与系统完全隔离。
 * ===========================================================================
 */
namespace app\middleware;

class PoolHostTakeover
{
    public function handle($request, \Closure $next)
    {
        //Site::takeover 内部已做防错处理；渲染期间不捕获异常，
        //否则会吞掉 302 强引所依赖的 HttpResponseException
        if(\app\model\pool\Site::takeover($request->host())){
            $wz = new \app\index\controller\Wz(app());
            return $wz->render('', '');
        }
        return $next($request);
    }
}
