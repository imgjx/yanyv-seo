<!doctype html>
<html>
<head><meta charset="utf-8"/><title>文章页 #{$id} - {$site.title}</title></head>
<body>
<h1>{$site.title}</h1>
<p>当前文章ID：{$id}</p>
<div>{$Content|raw}</div>
<ul>
{volist name="GuideLinks" id="vo"}
<li><a href="{$vo.url}">{$vo.url|raw}</a></li>
{/volist}
</ul>
<p>{$copyright|raw}</p>
</body>
</html>