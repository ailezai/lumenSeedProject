INSERT INTO `admin_user` VALUES
  (1, 'root', '$2y$10$zfuLMh1dtxd43B9fgEGk7e6Ou3oI12qoNcw/tOZJAeyMyXmtOuNC2', 'WFVUVNK3KZN7L5Q4', '系统', NULL, NULL, 0, NULL, 'NORMAL', NOW(), NOW()), -- root
  (2, 'admin', '$2y$10$.ep/U71POVRlamGKDrtlbO9n/laoEnc5Ek7LzhxtQoNFRNltkMhIi', 'R3HGKYBXSRGY6J4K', '系统管理员', NULL, NULL, 0, NULL, 'NORMAL', NOW(), NOW()); -- admin

INSERT INTO `admin_role` VALUES
  (1, 'root', '系统', 0, '0', 'ADMIN', 'ROOT', NOW(), NOW()),
  (2, 'administrator', '系统管理员', 1, '0', 'ADMIN', 'ADMIN', NOW(), NOW());

INSERT INTO `admin_permission` VALUES
  (1, '*', '所有权限', 'ALL', '/*', NOW(), NOW()),
  (2, 'log', '日志权限', 'ALL', '/log/*', NOW(), NOW()),
  (3, 'system.user', '系统用户权限', 'ALL', '/system/user/*', NOW(), NOW()),
  (4, 'system.role', '系统角色权限', 'ALL', '/system/role/*', NOW(), NOW()),
  (5, 'system.permission', '系统权限权限', 'ALL', '/system/permission/*', NOW(), NOW()),
  (6, 'system.dictionary', '系统字典权限', 'ALL', '/system/dictionary/*', NOW(), NOW()),
  (7, 'system.menu', '系统菜单权限', 'ALL', '/system/menu/*', NOW(), NOW());

INSERT INTO `admin_user_role_grant` VALUES
  (1, 1, 1, 1, NOW(), NOW()),
  (2, 2, 2, 1, NOW(), NOW());

INSERT INTO `admin_role_permission_grant` VALUES
  (1, 1, 1, NOW(), NOW()),
  (2, 1, 2, NOW(), NOW()),
  (3, 1, 3, NOW(), NOW()),
  (4, 1, 4, NOW(), NOW()),
  (5, 1, 5, NOW(), NOW()),
  (6, 1, 6, NOW(), NOW()),
  (7, 1, 7, NOW(), NOW()),
  (8, 2, 3, NOW(), NOW()),
  (9, 2, 4, NOW(), NOW()),
  (10, 2, 5, NOW(), NOW()),
  (11, 2, 6, NOW(), NOW()),
  (12, 2, 7, NOW(), NOW());

INSERT INTO `admin_menu` VALUES
  (1, 0, 1, '首页', 'fa fa-bar-chart', '/index', NOW(), NOW()),
  (2, 0, 2, '配置', 'fa fa-gears', '', NOW(), NOW()),
  (3, 0, 3, '系统', 'fa fa-tasks', '', NOW(), NOW()),
  (4, 3, 4, '管理员', 'fa fa-users', 'system/user/index', NOW(), NOW()),
  (5, 3, 5, '角色', 'fa fa-user', 'system/role/index', NOW(), NOW()),
  (6, 3, 6, '权限', 'fa fa-ban', 'system/permission/index', NOW(), NOW()),
  (7, 3, 7, '菜单', 'fa fa-bars', 'system/menu/index', NOW(), NOW()),
  (8, 3, 8, '字典表', 'fa fa-book', 'system/dictionary/index', NOW(), NOW()),
  (9, 0, 9, '日志', 'fa fa-file-text-o', '', NOW(), NOW()),
  (10, 9, 10, '登录日志', 'fa fa-file-o', 'log/login/index', NOW(), NOW()),
  (11, 9, 11, '操作日志', 'fa fa-file-o', 'log/operation/index', NOW(), NOW()),
  (12, 9, 12, 'SQL日志', 'fa fa-file-o', 'log/sql/index', NOW(), NOW());