<?php
/**
 * ===========================================================================
 * 烟雨蜘蛛池系统 - 站群站点模型
 * ===========================================================================
 */
namespace app\model\pool;

use app\model\Base;

class Site extends Base
{
    //数据表名（自动命名会误判为 vt_site，须显式指定）
    protected $name = 'pool_site';

    protected $pk = 'siteid';

    /**
     * 站点列表（分页）
     */
    public function listQuery(array $where = [], string $fields = '')
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        if($kw !== '') $where[] = ['title|domain','LIKE','%'.$kw.'%'];
        return $this->where($where)->withoutField($fields)->order('siteid','desc')->paginate(intval($d['limit'] ?? 10));
    }

    /**
     * 根据当前访问域名匹配站点（支持泛解析 *.abc.com）
     * @param  string  $host  当前主机名
     * @return obj|null
     */
    public static function matchHost(string $host)
    {
        $host = strtolower($host);
        // 先精确匹配
        $rs = self::one(['domain'=>$host, 'state'=>1]);
        if($rs) return $rs;
        // 泛解析匹配：*.abc.com（逐级回退，如 a.b.abc.com 可命中 *.b.abc.com 或 *.abc.com）
        $parts = explode('.', $host);
        while(count($parts) > 2){
            array_shift($parts);
            if($rs = self::one(['domain'=>'*.'.implode('.', $parts), 'state'=>1])) return $rs;
        }
        return null;
    }

    /**
     * 当前域名是否被站群接管（整站绑定），带60秒本地缓存避免每次请求查库
     * @param  string  $host  当前主机名
     * @return bool
     */
    public static function takeover(string $host): bool
    {
        $host = strtolower(trim($host));
        if($host === '') return false;
        try{
            $file = app()->getRuntimePath().'pool/host_map.php';
            $map = [];
            if(is_file($file) && (time() - filemtime($file)) < 60){
                $map = include $file;
            }else{
                foreach(self::where('state',1)->column('domain') as $d){
                    $d = strtolower(trim(strval($d)));
                    if($d !== '') $map[$d] = 1;
                }
                if(!is_dir(dirname($file))) mkdir(dirname($file), 0755, true);
                file_put_contents($file, '<?php return '.var_export($map, true).';');
            }
        }catch(\Throwable $e){
            return false; //未安装或数据表不存在时不接管
        }
        //精确域名
        if(isset($map[$host])) return true;
        //泛解析逐级回退匹配
        $parts = explode('.', $host);
        while(count($parts) > 2){
            array_shift($parts);
            if(isset($map['*.'.implode('.', $parts)])) return true;
        }
        return false;
    }

    /**
     * 域名格式校验与规范化
     * 支持普通域名 abc.com / sub.abc.com 与泛解析 *.abc.com（仅允许头部通配）
     * @param  string  $domain  待校验域名
     * @return string  规范化后的域名，不合法返回空串
     */
    public static function checkDomain(string $domain): string
    {
        $domain = strtolower(trim(strip_sql($domain)));
        if($domain === '') return '';
        if(!preg_match('/^(\*\.)?([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/', $domain)) return '';
        return $domain;
    }

    /**
     * 清空域名接管缓存（站点增删改后调用，立即生效）
     */
    public static function flushMap(): void
    {
        try{ @unlink(app()->getRuntimePath().'pool/host_map.php'); }catch(\Throwable $e){}
    }

}
