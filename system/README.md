# 角色权限说明
## session
- admin_user_id (int): 管理员id（eg: 1）
- admin_user_name (string): 管理员姓名(eg: 张三)
- admin_user_role_ids (array): 所在角色组id(eg: [1, 2])
- admin_user_role (string): 所在角色组名称（eg: 管理员 测试组）
- role_alias_grant (array): 所在角色组标识（eg: ['admin', 'test']）
- permission_alias (array): 权限标识
```php
[
    'grant'  => ['admin', 'test'], // 授权
    'forbid' => ['log']            // 禁止
]
```
- permission_path (array): 权限路由
```php
[
    'grant'  => ['GET']['admin/*', 'test/*'],  // 授权
    'forbid' => ['POST']['log/*']              // 禁止
]
```
- system_user (int): 系统管理员标识（只有admin会有该参数，且为1）

## redis
- {APP_NAME}_admin_user_id_${admin_user_id} (string[json]): 管理员信息，过期时间7天
```php
关键信息
[
    'session' => session()->getId()  // 对应sessionId值
    'needRefresh' => 0               // 是否需要更新权限：0正常，1需要更新
]
```

## jwt跳转登录
- 生效时间：密钥签发时间
- 过期时间：1分钟
- 参数
    - iss: 签发项目
    - issuer_admin_user_id: 签发管理员id
    - type: JWT_LOGIN
> 两个系统之间的jwt登录需要进行相互协作，暂时无法提供比较成熟的解决方案