<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<title>{if condition="!empty($Article['detail'])"}{$Article.detail.title} - {/if}{$site.title}</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<style>
body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;margin:0;color:#333;background:#f7f8fa}
.wrap{max-width:920px;margin:0 auto;padding:24px 16px}
article{background:#fff;border-radius:8px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.04);line-height:1.9;font-size:15px;word-break:break-all}
article h1{font-size:20px;margin:0 0 16px;color:#1d2129}
ul{list-style:none;padding:0}
li{padding:6px 0;font-size:13px}
a{color:#4e5969;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
    <article>
        {if condition="!empty($Article['detail'])"}
        <h1>{$Article.detail.title}</h1>
        {$Article.detail.content|raw}
        {else /}
        {$Content|raw}
        {/if}
    </article>
    <ul>
    {volist name="Article.list" id="vo"}
        <li><a href="{$vo.url}">{$vo.title}</a></li>
    {/volist}
    </ul>
    <ul>
    {volist name="GuideLinks" id="vo"}
        <li><a href="{$vo.url}" target="_blank">{$vo.url|raw}</a></li>
    {/volist}
    </ul>
    <p>{$copyright|raw}</p>
</div>
</body>
</html>
