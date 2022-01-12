<?php

namespace AiLeZai\Util\Lumen\CodeGen\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use AiLeZai\Util\Lumen\CodeGen\Services\CheckConfigService;
use AiLeZai\Util\Lumen\CodeGen\Services\CodeGenUtil;
use AiLeZai\Util\Lumen\CodeGen\Services\ControllerGenerateService;
use AiLeZai\Util\Lumen\CodeGen\Services\GenerateService;
use AiLeZai\Util\Lumen\CodeGen\Services\ModelGenerateService;
use AiLeZai\Util\Lumen\CodeGen\Services\RepositoryGenerateService;
use AiLeZai\Util\Lumen\CodeGen\Services\ServiceGenerateService;

class CodeGenerateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'code:gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate code temple';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle()
    {
        // 只支持在本地环境开发
        if (env('APP_ENV', 'pdt') !== 'loc') {
            echo "只支持在本地环境使用代码生成模板...生成失败\n";
            exit;
        }

        // 获取数据库连接
        $dbConfigs = config('database.connections');
        $dbConfig = "";
        echo "请选择数据库连接：\n";
        $db = [];
        $idx = 1;
        foreach ($dbConfigs as $key => $value) {
            $db[$idx] = $key;
            echo "{$idx}: {$key}\n";
            $idx++;
        }
        while (true) {
            $get = trim(fgets(STDIN));
            if (empty($db[$get])) {
                echo "选择配置不存在，请重新输入\n";
                continue;
            }
            $dbConfig = $db[$get];
            break;
        }

        // 检查数据库连接
        try {
            DB::connection($dbConfig);
        } catch (Exception $e) {
            throw new Exception("数据库连接失败");
        }

        $generateService = new GenerateService($dbConfig);

        // 获取配置
        try {
            $config = include_once base_path("codeGen/config.php");
            $generateService->init($dbConfig,
                $config['model'] ?? '',
                $config['repository'] ?? '',
                $config['service'] ?? '',
                $config['controller'] ?? '',
                $config['viewType'] ?? '',
                $config['resourcePath'] ?? '',
                $config['rootRoute'] ?? '');
        } catch (Exception $e) {
            echo "主配置文件打开失败\n";
            exit;
        }

        while (true) {
            echo "请输入表名：";
            $table = trim(fgets(STDIN));
            if (empty($table)) {
                exit;
            }
            $msg = $generateService->generate($table);
            foreach ($msg as $item) {
                echo $item."\n";
            }
            echo "生成结束\n";
        }
    }
}
