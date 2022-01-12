# 后台管理系统
### 项目初始化说明
- 打开`system/Sql`文件夹，依次执行`admin.sql`和`adminInit.sql`文件
- 检查`.env`文件，补充相关配置信息，并同步更新`.env.dev`、`.env.pre`、`.env.pdt`文件
- 检查`storage`文件夹权限，需要权限为777

### 项目使用说明
- 使用工具获取授权码
    - 参考工具`FreeOTP`，扫描`system`文件夹下的`authToken`图片，获取`root`和`admin`账号的授权码
- root账号拥有几乎所有权限，除了部分指定角色的授权外，主要用于账号跳转和最高权限的设置
- admin账号作为账号管理员，所有业务账号由admin账号负责创建用户和角色，并将用户分配到角色中
- 系统中的log表主要用于记录各操作产生的账号，在涉及到一些敏感操作或者特殊操作时可以进行追查，若无需使用，可删除`system/Http/Middleware/LogToDBMiddleware.php`文件

### 新项目发布须知
在邮件中标明相关信息，方便运维同学部署，也可提示自己检查部署问题，需要提供内容或信息参考如下：
- 项目名称（例：zp_settle(php)）
- 部署位置（例：zpsettle1, zpsettle2）
- 项目访问域名（如有需要）
  - 具体域名信息（例：zpsettle.julanling.com），包括访问端口
  -域名请求协议（http / https），并确认是同时支持两种协议，还是要求http强制跳转到https协议
- 是否需要新的负载均衡（SLB）
  - 如有需要，告知SLB前后端口及流量分配情况，参考如下
    - 访问端口80，后端服务器端口8080
    - 流量分配：zpsettle1 = 60; zpsettle2 = 100
- 项目的SSH部署链接（git@gitlab.julanling.com:php/zp_settle.git）
  - 部署前，确认已开启部署公钥（Setting ->Repository -> Deploy Keys）
- 部署信息
  - 部署分支
  - 节点hash值
  - 节点摘要信息
- 其他配置相关信息
  - 配置文件软连接（例： .env -> .env.pdt）
  - 文件夹权限（例：storage文件夹及子文件夹需要777权限）

### 更新日志

#### v2.0.6
- 修复了已知的BUG
- 增加了‘新项目发布须知’

#### v2.0.5
> 更新日期：2018年7月20日

- 更新Composer相关配置
    - 删除了二维码生成器（原用于授权码的二维码生成）
    - 禁止了packagist.org的访问

#### v2.0.4
> 更新日期：2018年7月19日  

- 引入代码生成器（root账户可在本地环境下生成代码）

#### v2.0.3
> 更新日期：2018年7月19日  

- 修复了 http/https 的url兼容问题，使用（`auto_url()`/`auto_asset()`）即可解决
- 增加了Commands中的Demo类，并提供了一种可行的参考执行方式
- 迁出`BaseEnum`类，使用`Julanling/lumen`中的`BaseEnum`进行替换
- 补充了类注释
- 去除了`OrderByFilter`
- 更新了配置文件信息
- 更新了`system`文件夹下的README.md
- 更新了视图模板（搜索部分）
- 补充了`root`和`admin`账户的授权码二维码