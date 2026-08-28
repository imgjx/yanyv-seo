<?php
/**
 * ===========================================================================
 * YanyvSEO - Markdown 转 HTML 工具（轻量实现，零依赖）
 * 支持：标题/段落/引用/有序无序列表/代码块/行内代码/加粗/斜体/链接/图片/分割线
 * 兼容：块级原生 HTML 直接透传（抓取入库的 HTML 内容不被破坏）
 * ===========================================================================
 */
namespace tool;

class Markdown
{
    /**
     * Markdown 转 HTML
     * @param string $md 源文本
     * @return string
     */
    public static function toHtml(string $md): string
    {
        $md = str_replace("\r\n", "\n", $md);
        $md = str_replace("\r", "\n", $md);
        $blocks = preg_split('/\n{2,}/', trim($md));
        $out = [];
        foreach($blocks as $b){
            $b = trim($b);
            if($b === '') continue;
            //代码块
            if(preg_match('/^(```|~~~)/', $b, $m)){
                $lang = trim(substr($b, 3, strpos($b."\n", "\n") - 3));
                $code = preg_replace('/^(```|~~~)[^\n]*\n/', '', $b);
                $code = preg_replace('/(```|~~~)\s*$/', '', $code);
                $out[] = '<pre><code>'.htmlspecialchars($code, ENT_QUOTES).'</code></pre>';
                continue;
            }
            //块级 HTML 透传
            if($b[0] === '<' && preg_match('/^<(\/?)(p|div|ul|ol|li|table|thead|tbody|tr|td|th|h[1-6]|section|article|header|footer|blockquote|figure|video|audio|iframe|script|style|form|br|hr|img)\b/i', $b)){
                $out[] = $b;
                continue;
            }
            //标题
            if(preg_match('/^(#{1,6})\s+(.+)$/m', $b) && preg_match_all('/^(#{1,6})\s+(.*)$/', $b, $hs, PREG_SET_ORDER)){
                $h = '';
                foreach($hs as $t){
                    $lv = min(6, strlen($t[1]));
                    $h .= '<h'.$lv.'>'.self::inline(trim($t[2])).'</h'.$lv.'>';
                }
                $out[] = $h;
                continue;
            }
            //分割线
            if(preg_match('/^(\*{3,}|-{3,}|_{3,})$/', $b)){
                $out[] = '<hr/>';
                continue;
            }
            //引用
            if($b[0] === '>'){
                $quote = trim(preg_replace('/^>\s?/m', '', $b));
                $out[] = '<blockquote>'.nl2br(self::inline($quote)).'</blockquote>';
                continue;
            }
            //有序/无序列表
            if(preg_match_all('/^\s*(\d+[.)]|[-*+])\s+(.*)$/', $b, $ls, PREG_SET_ORDER)){
                $ordered = preg_match('/^\s*\d+[.)]\s/', $b) ? true : false;
                $tag = $ordered ? 'ol' : 'ul';
                $li = '';
                foreach($ls as $l){ $li .= '<li>'.self::inline(trim($l[2])).'</li>'; }
                $out[] = '<'.$tag.'>'.$li.'</'.$tag.'>';
                continue;
            }
            //段落
            $out[] = '<p>'.nl2br(self::inline($b)).'</p>';
        }
        return implode("\n", $out);
    }

    /**
     * 行内元素解析
     */
    protected static function inline(string $t): string
    {
        //图片
        $t = preg_replace('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"[^"]*")?\)/', '<img src="$2" alt="$1"/>', $t);
        //链接
        $t = preg_replace('/\[([^\]]+)\]\(([^)\s]+)(?:\s+"[^"]*")?\)/', '<a href="$2">$1</a>', $t);
        //加粗+斜体
        $t = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $t);
        $t = preg_replace('/___(.+?)___/', '<strong><em>$1</em></strong>', $t);
        $t = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $t);
        $t = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $t);
        $t = preg_replace('/(?<!\*)\*([^*\s][^*]*?)\*(?!\*)/', '<em>$1</em>', $t);
        $t = preg_replace('/(?<!_)_([^_\s][^_]*?)_(?!_)/', '<em>$1</em>', $t);
        //删除线
        $t = preg_replace('/~~(.+?)~~/', '<del>$1</del>', $t);
        //行内代码（放最后避免内部标记被二次解析）
        $t = preg_replace_callback('/`([^`]+)`/', function($m){
            return '<code>'.htmlspecialchars($m[1], ENT_QUOTES).'</code>';
        }, $t);
        return $t;
    }
}
