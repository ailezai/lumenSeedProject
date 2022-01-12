# 后台管理系统
### 项目初始化说明
- 打开`system/Sql`文件夹，依次执行`admin.sql`和`adminInit.sql`文件
- 检查`.env`文件，补充相关配置信息
- 检查`storage`文件夹权限，需要权限为777
- conf指向public下index文件

### 项目使用说明
- 使用工具获取授权码
    - 参考工具`FreeOTP`，扫描`system`文件夹下的`authToken`图片，获取`root`和`admin`账号的授权码
- root账号拥有几乎所有权限，除了部分指定角色的授权外，主要用于账号跳转和最高权限的设置
- admin账号作为账号管理员，所有业务账号由admin账号负责创建用户和角色，并将用户分配到角色中
- 系统中的log表主要用于记录各操作产生的账号，在涉及到一些敏感操作或者特殊操作时可以进行追查，若无需使用，可删除`system/Http/Middleware/LogToDBMiddleware.php`文件
