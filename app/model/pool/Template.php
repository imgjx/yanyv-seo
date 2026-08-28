<?php
/**
 * ===========================================================================
 * YanyvSEO - 站群模板模型
 * 模板目录：public/template/{name}/
 *   - index.tpl        入口模板（必须）
 *   - *.tpl            其他页面模板，由 .conf 路由规则调用
 *   - {name}.conf      模板配置(JSON)：显示名/作者/路由规则等基础信息
 *   - 附加静态资源(css/js/img)可放同目录，前台可直接引用
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;

class Template extends Base
{
    //数据表名（自动命名会误判为 vt_template，须显式指定）
    protected $name = 'pool_template';

    protected $pk = 'tid';

    /**
     * 模板列表（分页）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        if($kw !== '') $where[] = ['name|title','LIKE','%'.$kw.'%'];
        return $this->where($where)->withoutField($fields)->order('tid','desc')->paginate(intval($d['limit'] ?? 10));
    }

    /**
     * 模板根目录
     */
    public static function root(): string
    {
        return VT_PUBLIC.'template/';
    }

    /**
     * 模板目录路径
     * @param  string  $name  目录名
     * @return string
     */
    public static function dir(string $name): string
    {
        return self::root().$name;
    }

    /**
     * 模板配置文件路径
     */
    public static function confFile(string $name): string
    {
        return self::dir($name).'/'.$name.'.conf';
    }

    /**
     * 某目录下的全部模板文件(*.tpl)
     */
    public static function tplFiles(string $name): array
    {
        $arr = [];
        foreach(glob(self::dir($name).'/*.tpl') as $f) $arr[] = basename($f);
        sort($arr);
        return $arr;
    }

    /**
     * 读取模板配置（读取失败返回默认结构）
     */
    public static function readConf(string $name): array
    {
        $file = self::confFile($name);
        $conf = ['title'=>$name,'version'=>'1.0','author'=>'','remark'=>'','index'=>'index','routes'=>''];
        if(is_file($file)){
            $j = json_decode(strval(file_get_contents($file)), true);
            if(is_array($j)) $conf = array_merge($conf, $j);
        }
        return $conf;
    }

    /**
     * 写入模板配置
     */
    public static function writeConf(string $name, array $data): bool
    {
        $file = self::confFile($name);
        return is_numeric(file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)));
    }

    /**
     * 扫描 public/template/ 下全部模板目录名
     */
    public static function scan(): array
    {
        $arr = [];
        foreach(glob(self::root().'*', GLOB_ONLYDIR) as $d){
            //合法模板目录：存在任意 .tpl 文件
            if(glob($d.'/*.tpl')) $arr[] = basename($d);
        }
        sort($arr);
        return $arr;
    }

    /**
     * 路由规则解析匹配
     * 规则行格式：正则 => 模板文件?变量=替换值&...
     * 例： /post/(\d+) => post?id=$1   （tpl 允许省略 .tpl 后缀）
     * @param  string $routesRaw 多行规则文本
     * @param  string $path      当前路径 如 /post/11
     * @return array|null 匹配则 ['tpl'=>'post', 'params'=>['id'=>'11']]
     */
    public static function matchRoute(string $routesRaw, string $path): ?array
    {
        $path = '/'.ltrim(trim($path), '/');
        foreach(explode("\n", str_replace("\r", '', $routesRaw)) as $line){
            $line = trim($line);
            if($line === '' || stripos($line, '=>') === false) continue;
            list($rule, $target) = array_map('trim', explode('=>', $line, 2));
            if($rule === '') continue;
            if(!@preg_match('#^'.$rule.'$#u', $path)) continue;
            $tpl = 'index'; $params = [];
            if(($q = strpos($target, '?')) !== false){
                parse_str(substr($target, $q + 1), $params);
                $target = substr($target, 0, $q);
            }else{
                parse_str(parse_url($path, PHP_URL_QUERY) ?? '', $params);
            }
            $tpl = str_replace('.tpl', '', strtolower(trim($target)));
            if(preg_match('/^[a-z0-9_\-]+$/', $tpl) !== 1) $tpl = 'index';
            //参数值做 $N 替换
            foreach($params as $k => $v){
                if(preg_match('/^\$(\d)$/', strval($v))){
                    $params[$k] = preg_replace('#^'.$rule.'$#u', '$'.substr($v, 1), $path);
                }
            }
            return ['tpl'=>$tpl, 'params'=>$params];
        }
        return null;
    }

    /**
     * 以 .conf 为准同步数据库中的显示名（站点下拉标题展示用）
     */
    public static function syncTitles(): void
    {
        try{
            foreach(self::where('state','>',0)->column('title','tid') as $tid => $t){
                $rs = self::one(['tid'=>$tid]);
                $c = self::readConf(strval($rs->name));
                if($c['title'] !== $rs->title) $rs->save(['title'=>$c['title']]);
            }
        }catch(\Throwable $e){}
    }

}
