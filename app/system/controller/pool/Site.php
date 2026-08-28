<?php
/**
 * ===========================================================================
 * YanyvSEO - 站群站点管理
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\Site as MD;
use app\model\pool\Template as Tpl;

class Site extends AdminBase
{
    /**
     * 站点列表
     */
    public function index(string $do = '')
    {
        if($do == 'json') return $this->returnMsg((new MD())->listQuery());
        $this->assign([
            'limit'     => 10,
            'tplList'   => json_encode(Tpl::column('title','tid')),
            'groupList' => json_encode(\app\model\cms\Group::allGroups())
        ]);
        return $this->fetch();
    }

    /**
     * 添加站点
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','@title/*/{2,60}/站点名称','@domain/*/{3,80}/泛解析域名如*.abc.com或abc.com/0/.*\-','template_id/d','ratio_301/d','weight/d','real_ip_head/h','cms_groupid/d','kw_mode/d','kw_lib','kw_price/r','kw_spider/d','kw_param/s','cache_hours/d','spider_show/d']);
        //SEO关键词库整理：每行一个
        $d['kw_lib'] = implode("\n", array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', strval($d['kw_lib'])))));
        //域名格式校验（支持 *.abc.com 泛解析）
        $domain = MD::checkDomain($d['domain']);
        if(!$domain) return $this->returnMsg("域名格式不正确，示例：abc.com 或 *.abc.com");
        $d['domain'] = $domain;
        if(MD::one(['domain'=>$d['domain']])) return $this->returnMsg("该域名已存在");
        $d['creator'] = $this->manUser['username'];
        MD::create($d);
        MD::flushMap();
        return $this->returnMsg("添加站点成功", 1);
    }

    /**
     * 编辑站点
     */
    public function edit(string $do = '')
    {
        if($do == 'up'){
            $d = $this->only(['@token'=>'','@siteid/d','av','af']);
            $field = in_array($d['af'],['ratio_301','weight','cache_hours','spider_show','state']) ? $d['af'] : '';
            if(!$field) return $this->returnMsg("参数错误");
            $rs = MD::one(['siteid'=>$d['siteid']]);
            if(!$rs) return $this->returnMsg("数据不存在");
            MD::flushMap(); //状态快编影响接管范围
            return $this->returnMsg($rs->save([$field=>intval($d['av'])]) ? "设置成功" : "设置失败", 1);
        }
        $d = $this->only(['@token'=>'','@siteid/d','@title/*/{2,60}/站点名称','@domain/*/{3,80}/泛解析域名如*.abc.com或abc.com/0/.*\-','template_id/d','ratio_301/d','weight/d','real_ip_head/h','cms_groupid/d','kw_mode/d','kw_lib','kw_price/r','kw_spider/d','kw_param/s','cache_hours/d','spider_show/d']);
        //SEO关键词库整理：每行一个
        $d['kw_lib'] = implode("\n", array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', strval($d['kw_lib'])))));
        //域名格式校验（支持 *.abc.com 泛解析）
        $domain = MD::checkDomain($d['domain']);
        if(!$domain) return $this->returnMsg("域名格式不正确，示例：abc.com 或 *.abc.com");
        $d['domain'] = $domain;
        $rs = MD::one(['siteid'=>$d['siteid']]);
        if(!$rs) return $this->returnMsg("数据不存在");
        $ex = MD::where('domain',$d['domain'])->whereNotIn('siteid',$d['siteid'])->find();
        if($ex) return $this->returnMsg("该域名已被其他站点使用");
        $d['editor'] = $this->manUser['username'];
        $result = $rs->save($d) !== false;
        if($result) MD::flushMap();
        return $this->returnMsg($result ? "编辑成功" : "编辑失败", 1);
    }

    /**
     * 删除站点
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','siteid'])['siteid'];
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        MD::destroy($id);
        MD::flushMap();
        return $this->returnMsg("删除成功", 1);
    }

}
