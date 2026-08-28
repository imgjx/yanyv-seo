<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\index\controller;

use think\Response;
use think\exception\HttpResponseException;

/**
 * 前台控制器
 */
class Index extends \app\BaseController
{
    /**
     * 首页：泛解析域名命中站群站点 -> 站群渲染接管；
     * 否则优先渲染自定义模板 /app/home.tpl，再否则跳转 /system/
     */
    public function index(){
        //泛解析站群接管（系统本身单域名部署，泛解析仅服务于站群模块）
        if(\app\model\pool\Site::matchHost($this->request->host())){
            $wz = new \app\index\controller\Wz($this->app);
            return $wz->render('', '');
        }
        $home = app()->getRootPath().'app/home.tpl';
        if(is_file($home)){
            // 自定义首页模板，支持ThinkPHP模板语法
            $re = Response::create($home, 'view')->assign([
                'site'      => vconfig('site_title','YanyvSEO'),
                'copyright' => vconfig('sys_copyright','Copyright (C) 2026 YanyvSEO All Rights Reserved.'),
                'author'    => vconfig('sys_author','嗷呜awa'),
                'sys_site'  => vconfig('sys_site',''),
                'sys_source'=> vconfig('sys_source',''),
            ])->header();
            throw new HttpResponseException($re);
        }
        // 无自定义模板则跳转后台入口
        $re = Response::create()->header(['Location'=>VT_DIR.'/system/']);
        throw new HttpResponseException($re);
    }

}
