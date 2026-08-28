# 站群模板制作指南（template.md）

一个站群模板 = `public/template/{模板目录}/` 下的**一组文件**，最小包含：

```
public/template/mytpl/
├── index.tpl      入口模板（必须）
└── mytpl.conf     模板配置（JSON：显示名、路由规则等基础信息）
```

可附加任意静态资源（css/js/img）放同目录，前台直接相对路径引用；
其他页面模板如 `post.tpl`、`tag.tpl` 由 .conf 的路由规则调用。

## 一、mytpl.conf 配置说明

```json
{
    "name":    "mytpl",
    "title":   "我的站群模板",
    "version": "1.0",
    "author":  "",
    "remark":  "",
    "index":   "index",
    "routes":  "/post/(\\d+) => post?id=$1"
}
```

| 字段 | 说明 |
|------|------|
| title | 模板显示名（站点绑定选择器里显示的名称） |
| index | 默认入口模板文件名（不带 .tpl），任何未匹配路由的路径都渲染它 |
| routes | 路由规则，每行一条，格式见下 |

## 二、路由规则（站群路由核心）

格式：`正则 => 模板文件?变量=$N&...`

- 正则匹配当前访问路径（不含域名，如 `/post/11`）
- 匹配成功即渲染指定模板文件（ThinkPHP 模板语法）
- `$1 $2...` 依次替换为正则中的分组捕获值

示例：

```
/post/(\d+)        => post?id=$1        /post/11       -> post.tpl，模板里 {$id}=11
/tag/(\w+)         => tag?kw=$1         /tag/seo       -> tag.tpl，模板里 {$kw}=seo
/list-(\d+)-(\d+)  => list?cate=$1&page=$2
```

后台"模板管理"编辑框可直接改写规则并保存进 .conf。

## 三、模板可用变量

| 变量 | 说明 |
|------|------|
| {$site} | 当前站点信息数组：title / domain / host 等 |
| {$GuideLinks} | 投放的引导链接列表（仅蜘蛛来访时有值）：`{volist name="GuideLinks" id="vo"}{$vo.url}{/volist}` |
| {$Content} | CMS 驱动抓取的内容（站点配置了 cms_url 时有值） |
| {$Cms} | CMS 抓取的全部字段数组 |
| {$copyright} | 系统页脚版权 |
| {$engine.engine_id} | 命中的搜索引擎ID（0=普通访客） |
| 路由变量 | 路由规则赋值的变量，如上例的 {$id}、{$kw} |

> 注意链接列表是核心字段：蜘蛛池按需投喂一定数量的提交链接，
> 输出数量由站点的“蜘蛛展示条数(spider_show)”控制。

## 四、模板语法

直接使用 ThinkPHP8 官方 think-template 语法（与系统后台一致），例如：

```html
{volist name="GuideLinks" id="vo"}
<li><a href="{$vo.url|raw}">{$vo.url|raw}</a></li>
{/volist}

{$site.title}
<?php if(!empty($Content)){ echo '<div class="art">'.$Content.'</div>'; } ?>
```

## 五、发布流程

1. 后台【模板管理】→ 添加模板 → 自动生成目录骨架与 .conf
2. 通过代码编辑器（文件管理）修改 public/template/{目录}/ 下 .tpl 文件
3. 在【站点管理】把站点绑定到该模板即可生效；路由规则改动即时生效
