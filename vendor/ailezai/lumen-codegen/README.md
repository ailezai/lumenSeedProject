# lumen-code-generate
lumen代码模板生成

1. 在项目中`Kernel.php`中加入`CodeGenerateCommand::class`后即可使用代码生成器
2. 在命令行执行 `php artisan code:gen` 即可生成代码
    1. 首次执行时，会在项目根目录下创建`codeGen`文件夹，并复制模板和配置信息，使用时，按需修改相关的配置信息即可。
    2. 首次使用时，所有的配置都会按照默认配置执行
3. 配置修改后需要重新启用命令才能生效

所有代码模板的生成均以`templates`文件夹下，各个子文件夹里的template文件为准，其他文件只是用于模板参考