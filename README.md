# 烟雨蜘蛛池系统

> 多用户站群 SEO 蜘蛛池系统 · 基于 Veitool 框架（ThinkPHP8.x + Layui2.13.x）

烟雨蜘蛛池系统是一套面向多用户的站群蜘蛛池解决方案：普通用户提交链接并按积分计费，系统通过泛解析站群承接搜索引擎蜘蛛流量，按规则将引导跳转（302 强引）或链接曝光计费给链接所属用户。
DEMO:[https://demo-seo.yanyv.cc/system/](https://demo-seo.yanyv.cc/system/)
## 功能特性

### 用户与权限
- 统一登录后台（管理员与普通用户共用 `/system` 登录入口），图片验证码内置
- 三级角色体系：超级管理员（id=1）/ 管理员 / 注册用户，菜单按角色渲染
- 注册用户登录后进入个人中心，无系统管理权限；清缓存等敏感操作仅超级管理员可用
- 个人中心支持资料修改与密码修改（无头像上传模块）

### 积分与充值
- 积分体系：1 元 = 100 积分（比例可配置）
- 对接易支付兼容 API，支持固定金额 / 自定义金额 / 混合三种模式，可设最低/最高充值限额与固定金额档位
- 积分明细流水：充值、消费、调整、退还全记录

### SEO 引擎管理
- 内置百度 / 谷歌 / 必应 / 360 / 搜狗 / 神马六大引擎预置规则
- 支持 UA 匹配 / IP 匹配 / IP+UA 三种识别模式
- 每引擎独立配置普通引导与 301 强引单价，引导数必须从预设选项中选择（如 1000/3000/5000/10000）

### 站群系统
- 泛解析域名绑定（`*.abc.com`），子域与主域均可独立接管渲染
- 每站点独立配置：302 强引占比、轮询权重、真实 IP 识别头（默认 `X-Forwarded-For`）、蜘蛛单次展示链接数
- CMS 内容抓取引擎（正则规则可配，带缓存）
- 蜘蛛来访按强引占比执行 302 跳转或页面渲染引导，实时扣减链接额度并记录计费流水

### 模板系统（站群核心）
- 模板存储于 `public/template/{模板名}/`，最小结构：

```
public/template/default/
├── index.tpl          # 首页模板
└── default.conf       # 模板配置（JSON）：显示名、路由规则等
```

- **路由规则**（写在 `.conf` 的 `routes` 字段，正则 => 模板?参数）：

```
/post/(\d+) => post?id=$1
```

访问 `/post/11` 即由 `post.tpl` 渲染，模板内 `{$id}` 取值 `11`
- 直接使用 **ThinkPHP 模板引擎**解析，无自研模板语法，`{$site.title}`、`{volist}` 等原生可用
- 模板可用变量：`$site`（站点信息）、`$GuideLinks`（投放链接列表 `[['url'=>...],...]`）、`$Content`（CMS 内容）、`$id` 等路由参数、`$copyright`
- 模板制作完整文档见 [public/template/template.md](public/template/template.md)

### 链接投放
- 批量提交链接（每行一条，一次最多 1000 条，自动去重）
- 引导次数从引擎预设选项中选择，不支持自定义输入
- 可配置是否允许用户自选投放站群（关闭则全站轮询）
- 预付费扣积分，额度耗尽自动完成，支持暂停/续投

## 环境要求

- PHP >= 8.1（需 pdo_mysql、mbstring、curl、gd、fileinfo 扩展）
- MySQL 5.7+
- Nginx / Apache（PHP 内置服务器可用于开发调试）

## 安装

1. 下载项目文件到站点目录，将网站运行目录指向 `public/`
2. 访问 `http://你的域名/install` 进入安装向导，按提示配置数据库与管理员账号
3. 安装完成后访问 `/system` 登录后台（使用安装时设置的管理员账号）
4. 站群域名请解析泛域名 `*.你的站群域名` 到服务器，并在后台「蜘蛛池管理 → 站群站点」中绑定

开发调试可用：

``` bash
php think run          # 默认 8000 端口
```

> 注意：PHP 内置服务器调试时请使用 `php -S 127.0.0.1:8000 public/router.php` 以支持多级路径。

## 目录结构

```
├── app/
│   ├── index/            # 前台应用（站群渲染 Wz、支付回调 Pay）
│   ├── system/           # 后台应用（pool.* 站群管理 / my.* 个人中心 / system.* 系统管理）
│   ├── model/            # 数据模型
│   └── middleware/       # 站群域名接管中间件
├── config/               # 框架配置
├── public/
│   ├── template/         # 站群模板目录（含 template.md 模板制作文档）
│   ├── install/          # 安装程序（安装完成后请删除或保持 install.lock）
│   └── static/           # 静态资源
└── route/                # 路由定义
```

## 版权信息

- 本系统基于 [Veitool V2.3.5](https://www.veitool.com) 框架二次开发，框架采用 [Apache2.0](https://opensource.org/license/apache-2-0/) 协议发布
- 本系统以 Apache License 2.0 协议开源（详见 [LICENSE](LICENSE)）

Copyright (C) 2026 烟雨蜘蛛池系统 All Rights Reserved. Powered by [YanyvSEO](https://seo.yanyv.cc/)
