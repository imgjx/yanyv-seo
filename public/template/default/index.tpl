<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<title>{if condition="!empty($Article['detail'])"}{$Article.detail.title} - {/if}{$site.title}</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="keywords" content="{$site.title},{$site.host}"/>
<meta name="description" content="{$Content|mb_substr=0,120}"/>
<style>
body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;margin:0;color:#333;background:#f7f8fa}
.wrap{max-width:920px;margin:0 auto;padding:24px 16px}
header{border-bottom:1px solid #e5e6eb;padding-bottom:12px;margin-bottom:24px}
header h1{font-size:22px;margin:0 0 4px;color:#1d2129}
header .host{font-size:12px;color:#86909c}
article{background:#fff;border-radius:8px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.04);line-height:1.9;font-size:15px;word-break:break-all}
article h1{font-size:20px;margin:0 0 16px;color:#1d2129}
.links{margin-top:28px;background:#fff;border-radius:8px;padding:20px 28px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.links h2{font-size:16px;border-left:4px solid #165dff;padding-left:10px;margin:0 0 14px;line-height:1.4}
.links ul{list-style:none;margin:0;padding:0}
.links li{padding:6px 0;border-bottom:1px dashed #f0f1f3;font-size:13px}
.links li a{color:#4e5969;text-decoration:none}
.links li a:hover{color:#165dff}
footer{text-align:center;color:#86909c;font-size:12px;padding:30px 0}
</style>
</head>
<body>
<div class="wrap">
    <header>
        <h1>{$site.title}</h1>
        <div class="host">{$site.host}{if condition="isset($cid) && $cid != ''"} · #{$cid}{/if}</div>
    </header>
    <article>
        {if condition="!empty($Article['detail'])"}
        <h1>{$Article.detail.title}</h1>
        {$Article.detail.content|raw}
        {else /}
        {$Content|raw}欢迎来到{$site.title}，本站提供丰富的资讯内容，为您持续更新优质信息。
        {/if}
    </article>
    <div class="links">
        <h2>推荐阅读</h2>
        <ul>
        {volist name="Article.list" id="vo"}
            <li><a href="{$vo.url}">{$vo.title}</a> <span style="color:#86909c">{$vo.time}</span></li>
        {/volist}
        </ul>
    </div>
    {if condition="!empty($GuideLinks)"}
    <div class="links">
        <h2>友情链接</h2>
        <ul>
        {volist name="GuideLinks" id="vo"}
            <li><a href="{$vo.url}" target="_blank">{$vo.url|mb_substr=0,80}</a></li>
        {/volist}
        </ul>
    </div>
    {/if}
    <footer>{$copyright}</footer>
</div>
</body>
</html>
