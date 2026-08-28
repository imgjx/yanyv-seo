<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 站群模板管理
 * 模板存储：public/template/{name}/ 含 index.tpl 与 {name}.conf(JSON)
 * .conf 内容：模板显示名(title)、路由规则(routes)等基础信息
 * ===========================================================================
 */
namespace app\system\controller\pool;

use app\system\controller\AdminBase;
use app\model\pool\Template as MD;

class Template extends AdminBase
{
    /**
     * 模板列表：数据库记录 + 目录扫描合并，标题以 .conf 为准
     */
    public function index(string $do = '')
    {
        if($do == 'json'){
            $rows = (new MD())->listQuery();
            $scan = MD::scan();
            //目录里存在但未登记的模板自动补登记
            $have = [];
            foreach($rows as $r){ $have[] = $r['name']; }
            foreach(array_diff($scan, $have) as $n){
                if(!preg_match('/^[a-z0-9_\-]{2,20}$/i', $n)) continue;
                try{ MD::create(['name'=>$n,'title'=>MD::readConf($n)['title'],'state'=>1,'creator'=>'sync','add_time'=>time()]); }catch(\Throwable $e){}
            }
            $rows = (new MD())->listQuery();
            foreach($rows as &$r){
                $conf = MD::readConf(strval($r['name']));
                $r['title']   = $conf['title'];                 //显示名以.conf为准
                $r['remark']  = strval($r['remark']) !== '' ? $r['remark'] : $conf['remark'];
                $r['routes']  = $conf['routes'];
                $files        = MD::tplFiles(strval($r['name']));
                $r['files']   = implode('、', $files);
                $r['exists']  = in_array($r['name'], $scan) ? 1 : 0;
            }
            return $this->returnMsg($rows);
        }
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 添加模板（创建 public/template/{name}/ 目录、示例 index.tpl 与 {name}.conf）
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','@name/*/{2,20}/模板目录需2-20位字母、数字、下划线或中划线/1,2/_-','@title/*/{2,50}/模板显示名','remark/h']);
        if(!preg_match('/^[a-z0-9_\-]{2,20}$/i', $d['name'])) return $this->returnMsg("目录名需2-20位字母、数字、下划线或中划线");
        if(MD::one(['name'=>$d['name']])) return $this->returnMsg("该目录名已存在");
        $dir = MD::dir($d['name']);
        if(is_dir($dir)) return $this->returnMsg("public/template/{$d['name']} 目录已存在，请更换目录名");
        if(!mkdir($dir, 0755, true)) return $this->returnMsg("目录创建失败，请检查权限");
        //写入基础示例模板
        file_put_contents($dir.DIRECTORY_SEPARATOR.'index.tpl',
'<!doctype html>
<html>
<head><meta charset="utf-8"/><title>{$site.title} - 烟雨蜘蛛池</title></head>
<body>
<h1>{$site.title}</h1>
<div>{$Content|raw}</div>
<ul>
{volist name="GuideLinks" id="vo"}
<li><a href="{$vo.url}">{$vo.url|mb_substr=0,60}</a></li>
{/volist}
</ul>
<p>'.vconfig('sys_copyright').'</p>
</body>
</html>');
        //写入 .conf 基础信息与示例路由规则
        MD::writeConf($d['name'], [
            'name'    => $d['name'],
            'title'   => $d['title'],
            'version' => '1.0',
            'author'  => $this->manUser['username'],
            'remark'  => trim(strval($d['remark'])),
            'index'   => 'index',
            'routes'  => "/post/(\\d+) => post?id=\$1",
        ]);
        $row = ['name'=>$d['name'], 'title'=>$d['title'], 'remark'=>trim(strval($d['remark'])),
                'state'=>1, 'creator'=>$this->manUser['username'], 'add_time'=>time()];
        MD::create($row);
        return $this->returnMsg("添加成功，目录 public/template/{$d['name']}/ 已生成", 1);
    }

    /**
     * 编辑模板（显示名/备注写入 .conf；路由规则写入 .conf）
     */
    public function edit()
    {
        //路由规则是正则文本，不能用 h 过滤(会剥掉 \ 和 /)，仅走默认 strip_sql
        $d = $this->only(['@token'=>'','@tid/d','@title/*/{2,50}/模板显示名','remark/h','routes']);
        $rs = MD::one(['tid'=>$d['tid']]);
        if(!$rs) return $this->returnMsg("数据不存在");
        $name = strval($rs->name);
        $conf = MD::readConf($name);
        $conf['title']  = $d['title'];
        $conf['remark'] = trim(strval($d['remark']));
        $conf['routes'] = str_replace("\r\n", "\n", strval($d['routes']));
        if(!MD::writeConf($name, $conf)) return $this->returnMsg("配置文件 {$name}.conf 写入失败");
        $rs->save(['title'=>$d['title'], 'remark'=>trim(strval($d['remark'])), 'editor'=>$this->manUser['username']]);
        return $this->returnMsg("编辑成功", 1);
    }

    /**
     * 删除模板记录（不删除模板目录文件；目录仍存在时下次列表会自动重新登记）
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','tid'])['tid'];
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        $inUse = \app\model\pool\Site::whereIn('template_id', $id)->column('siteid');
        if($inUse) return $this->returnMsg('该模板正在被站点使用，请先修改站点绑定');
        MD::destroy($id);
        return $this->returnMsg("删除成功", 1);
    }

}
