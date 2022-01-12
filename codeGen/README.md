## 参数及变量含义

### 通用参数（params）
```php
[
    'createTime'      => '2018-01-01 12:12:12',          // 模板创建时间

    'table'           => 'admin_user_role',              // 表名
    'connection'      => 'mysql',                        // 使用的数据库连接
    'primaryKey'      => 'admin_user_id',                // 主键
    'humpPrimaryKey'  => 'adminUserId',                  // 主键（驼峰，首字母小写）
    'ucPrimaryKey'    => 'AdminUserId',                  // 主键（驼峰，首字母大写）
    'primaryKeyType'  => 'int',                          // 主键类型
    'tableDesc'       => 'admin',                        // 表备注
    'colums'          => Object,                         // 表字段的所有信息（用于其他页面处理）
    
    'model_namespace'              => 'App\Model\Admin', // model命名空间
    'model_className'              => 'AdminUser',       // model类名
    'model_variableClassName'      => 'adminUser',       // model类变量名
    
    'repository_namespace'         => ''  // 类同model
    'repository_className'         => ''  // 类同model
    'repository_variableClassName' => ''  // 类同model
    
    'service_namespace'            => ''  // 类同model
    'service_className'            => ''  // 类同model
    'service_variableClassName'    => ''  // 类同model
    
    'controller_namespace'         => ''  // 类同model
    'controller_className'         => ''  // 类同model
    'controller_variableClassName' => ''  // 类同model
]
```

### Model参数
```php
[
    'column_fields_forSelect'    => '',  // 用于scopeSelectFullFields函数，选择所有列
    'modelComments'              => '',  // Model注释
]
```

### Controller参数
```php
[
    'view_route_point'    => '',  // 视图文件夹路径（点分割）
    'request_params'      => '',  // 和Model相关的请求参数验证
    'controller_params'   => '',  // Controler的参数绑定，用于service传参
]
```

### View参数
```php
[
    'view_route'                   => ''  // 视图所在文件夹
    'table_title'                  => ''  // 列表标题
    'table_content'                => ''  // 列表正文
    'addPage_content'              => ''  // 数据添加页内容
    'editPage_content'             => ''  // 数据编辑页内容
]
```