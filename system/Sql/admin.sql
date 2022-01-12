-- 创建管理中心
DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user`(
  `admin_user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(30) NOT NULL COMMENT '用户名',
  `password` CHAR(128) NOT NULL COMMENT '密码',
  `token` CHAR(128) DEFAULT NULL COMMENT '用户令牌',
  `name` VARCHAR(30) NOT NULL DEFAULT '管理员' COMMENT '姓名',
  `mail` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `mobile` VARCHAR(11) DEFAULT NULL COMMENT '手机',
  `login_ip` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录IP',
  `login_time` DATETIME DEFAULT NULL COMMENT '登录时间',
  `status` CHAR(20) DEFAULT 'NORMAL' COMMENT '状态',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_admin_user_id`(`admin_user_id`),
  UNIQUE KEY `uk_username`(`username`) USING BTREE,
  UNIQUE KEY `uk_mail`(`mail`) USING BTREE,
  UNIQUE KEY `uk_mobile`(`mobile`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '系统管理-用户';

DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role`(
  `role_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias` VARCHAR(50) NOT NULL COMMENT '标识',
  `name` VARCHAR(30) NOT NULL COMMENT '角色名称',
  `parent_role_id` BIGINT UNSIGNED NOT NULL  DEFAULT 0 COMMENT '父级角色',
  `parent_role_list` VARCHAR(50) NOT NULL  DEFAULT '' COMMENT '父级角色链，\',\'分割',
  `type` VARCHAR(30) NOT NULL DEFAULT 'DEFAULT' COMMENT '角色类别',
  `group` VARCHAR(30) NOT NULL DEFAULT 'DEFAULT' COMMENT '类内分组',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_role_id`(`role_id`),
  UNIQUE KEY `uk_alias`(`alias`) USING BTREE,
  KEY `idx_parent_role_id`(`parent_role_id`),
  KEY `idx_type`(`type`),
  KEY `idx_group`(`group`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '系统管理-角色';

DROP TABLE IF EXISTS `admin_permission`;
CREATE TABLE `admin_permission`(
  `permission_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias` VARCHAR(50) NOT NULL COMMENT '标识',
  `name` VARCHAR(30) NOT NULL COMMENT '权限名称',
  `method` VARCHAR(50) NOT NULL DEFAULT 'NONE' COMMENT '请求方法',
  `path` CHAR(255) DEFAULT 'NONE' COMMENT '请求路由:","分隔，*通配',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_permission_id`(`permission_id`),
  UNIQUE KEY `uk_alias`(`alias`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '系统管理-权限';


-- 创建相互关系
DROP TABLE IF EXISTS `admin_user_role_grant`;
CREATE TABLE `admin_user_role_grant`(
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` BIGINT UNSIGNED NOT NULL COMMENT '角色ID',
  `is_admin` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否组管理员：0否，1是',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_id`(`id`),
  UNIQUE KEY `uk_admin_user_role`(`admin_user_id`, `role_id`),
  KEY `idx_role_id`(`role_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = '系统管理-用户-角色授权关系';

DROP TABLE IF EXISTS `admin_user_permission_grant`;
CREATE TABLE `admin_user_permission_grant`(
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  `permission_id` BIGINT UNSIGNED NOT NULL COMMENT '权限ID',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_id`(`id`),
  UNIQUE KEY `uk_admin_user_permission`(`admin_user_id`, `permission_id`),
  KEY `idx_permission_id`(`permission_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = '系统管理-用户-权限授权关系';

DROP TABLE IF EXISTS `admin_user_permission_forbid`;
CREATE TABLE `admin_user_permission_forbid`(
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  `permission_id` BIGINT UNSIGNED NOT NULL COMMENT '权限ID',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_id`(`id`),
  UNIQUE KEY `uk_admin_user_permission`(`admin_user_id`, `permission_id`),
  KEY `idx_permission_id`(`permission_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = '系统管理-用户权限禁用关系';

DROP TABLE IF EXISTS `admin_role_permission_grant`;
CREATE TABLE `admin_role_permission_grant`(
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` BIGINT UNSIGNED NOT NULL COMMENT '角色ID',
  `permission_id` BIGINT UNSIGNED NOT NULL COMMENT '权限ID',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_id`(`id`),
  UNIQUE KEY `uk_role_permission`(`role_id`, `permission_id`),
  KEY `idx_permission_id`(`permission_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = '系统管理-角色-权限授权关系';


-- 菜单
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu`(
  `menu_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_menu_id` BIGINT UNSIGNED NOT NULL COMMENT '父菜单id',
  `order` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序，升序排列',
  `title` VARCHAR(50) DEFAULT NULL COMMENT '标题',
  `icon` VARCHAR(50) DEFAULT NULL COMMENT '图标',
  `path` VARCHAR(50) DEFAULT NULL COMMENT '请求路由',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY `pk_menu_id`(`menu_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '系统管理-菜单';


-- 日志
DROP TABLE IF EXISTS `log_login`;
CREATE TABLE `log_login` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(30) NOT NULL DEFAULT 'username' COMMENT '登录名',
  `ip` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IP地址',
  `session` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '会话',
  `status` VARCHAR(30) NOT NULL DEFAULT 'UNKNOWN' COMMENT '请求状态',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY `pk_id`(`id`),
  KEY `idx_user` (`username`) USING BTREE,
  KEY `idx_ip` (`ip`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '日志：登录记录';

DROP TABLE IF EXISTS `log_operation`;
CREATE TABLE `log_operation` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trace_id` VARCHAR(30) NOT NULL DEFAULT 'UNKNOWN' COMMENT '请求标识',
  `admin_user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  `name` VARCHAR(50) NOT NULL DEFAULT '*未知管理员' COMMENT '管理员',
  `ip` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IP地址',
  `session` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '会话',
  `host` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '根路径',
  `method` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '请求方法',
  `path` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '请求路由',
  `request` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '请求参数',
  `error_message` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '错误信息',
  `status` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '请求状态',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY `pk_id`(`id`),
  KEY `idx_name` (`name`) USING BTREE,
  KEY `idx_trace_id` (`trace_id`) USING BTREE,
  KEY `idx_path` (`path`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '日志：操作记录';

DROP TABLE IF EXISTS `log_sql`;
CREATE TABLE `log_sql` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trace_id` VARCHAR(30) NOT NULL DEFAULT 'UNKNOWN' COMMENT '请求标识',
  `query` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT 'sql预编译语句',
  `bindings` VARCHAR(3000) NOT NULL DEFAULT '' COMMENT '绑定数据',
  `time` DOUBLE NOT NULL DEFAULT 0 COMMENT '执行时间（毫秒）',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY `pk_id`(`id`),
  KEY `idx_trace_id` (`trace_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '日志：SQL记录';


-- 字典表
DROP TABLE IF EXISTS `admin_dictionary`;
CREATE TABLE `admin_dictionary` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL DEFAULT 'UNNAMED' COMMENT '字段名',
  `desc` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '字段描述',
  `dictionary` text DEFAULT NULL COMMENT '字典[key => value]',
  `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
  `modify_time` DATETIME DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY `pk_id`(`id`),
  UNIQUE KEY `uk_name` (`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '字典表';