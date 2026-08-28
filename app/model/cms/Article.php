<?php
/**
 * ===========================================================================
 * YanyvSEO - CMS文章模型
 * ===========================================================================
 */
namespace app\model\cms;

use app\model\Base;

class Article extends Base
{
    protected $name = 'cms_article';

    protected $pk = 'articleid';

    /**
     * 文章列表（分页，支持分组/关键词筛选）
     */
    public function listQuery(array $where = [])
    {
        $d = request()->get('','','strip_sql');
        if(($d['kw'] ?? '') !== '') $where[] = ['title','LIKE','%'.$d['kw'].'%'];
        return $this->where($where)->order('articleid','desc')->paginate(intval($d['limit'] ?? 10));
    }

    /**
     * 前台输出：分组伪原创 + MD转HTML 后的内容
     */
    public function displayContent(int $articleid): array
    {
        $a = $this->where([['articleid','=',$articleid],['state','=',1]])->find();
        if(!$a) return [];
        $a = $a->toArray();
        $a['content'] = self::renderContent(intval($a['groupid']), strval($a['content']));
        return $a;
    }

    /**
     * 渲染输出：伪原创实时替换 + Markdown转HTML（原生HTML内容透传）
     */
    public static function renderContent(int $groupid, string $content): string
    {
        return \tool\Markdown::toHtml(Group::pseudo($groupid, $content));
    }
}
