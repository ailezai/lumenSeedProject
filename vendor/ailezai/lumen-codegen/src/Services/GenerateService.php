<?php
/**
 * Created by PhpStorm.
 * User: Frm
 * Date: 2018/5/30
 * Time: 21:08
 */

namespace AiLeZai\Util\Lumen\CodeGen\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class GenerateService
{
    protected $db;

    protected $modelPath;

    protected $repositoryPath;

    protected $servicePath;

    protected $controllerPath;

    protected $viewType;

    protected $resourcePath;

    protected $rootRoute;

    protected $params;

    protected $checkConfigService;

    protected $returnMsg;

    /**
     * GenerateService constructor.
     *
     * @param $db
     * @param $modelPath
     * @param $repositoryPath
     * @param $servicePath
     * @param $controllerPath
     * @param $viewType
     * @param $resourcePath
     * @param $rootRoute
     *
     * @throws Exception
     */
    public function __construct($db = null, $modelPath = null, $repositoryPath = null, $servicePath = null,
                                $controllerPath = null, $viewType = null, $resourcePath = null, $rootRoute = null)
    {
        // 只支持在本地环境开发
        if (env('APP_ENV', 'pdt') !== 'loc') {
            throw new Exception("只支持在本地环境使用代码生成模板...生成失败");
        }

        $this->db = $db;
        $this->modelPath = $modelPath;
        $this->repositoryPath = $repositoryPath;
        $this->servicePath = $servicePath;
        $this->controllerPath = $controllerPath;
        $this->viewType = $viewType;
        $this->resourcePath = $resourcePath;
        $this->rootRoute = $rootRoute;

        // 校验数据和文件类
        $this->checkConfigService = new CheckConfigService();
        $this->checkConfigService->checkRouteAndFile();

        $this->returnMsg = [];
    }

    public function init($db = null, $modelPath = null, $repositoryPath = null, $servicePath = null,
                         $controllerPath = null, $viewType = null, $resourcePath = null, $rootRoute = null)
    {
        $this->db = $db ?? $this->db;
        $this->modelPath = $modelPath ?? $this->modelPath;
        $this->repositoryPath = $repositoryPath ?? $this->repositoryPath;
        $this->servicePath = $servicePath ?? $this->servicePath;
        $this->controllerPath = $controllerPath ?? $this->controllerPath;
        $this->viewType = $viewType ?? $this->viewType;
        $this->resourcePath = $resourcePath ?? $this->resourcePath;
        $this->rootRoute = $rootRoute ?? $this->rootRoute;
    }

    /**
     * 生成模板
     *
     * @param $table
     *
     * @return array
     */
    public function generate($table)
    {
        $this->returnMsg = [];
        try {
            $this->initParams($table);
            $this->generateModel();
            $this->generateRepository();
            $this->generateService();
            $this->generateController();
            $this->generateView();
        } catch (Exception $e) {
            $this->returnMsg[] = $e->getMessage();
        }

        return $this->returnMsg;
    }

    /**
     * 配置参数
     *
     * @param string $table 表名
     *
     * @throws Exception
     */
    private function initParams(string $table)
    {
        $database = config("database.connections.{$this->db}.database");
        $sql = "SELECT * FROM `information_schema`.`tables` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` = ?;";
        $tableDesc = DB::connection($this->db)->select($sql, [$database, $table]);
        if (empty($tableDesc)) {
            throw new Exception("找不到表信息");
        }

        $sql = "SHOW FULL COLUMNS FROM `{$table}`";
        $columns = DB::connection($this->db)->select($sql);
        foreach ($columns as $column) {
            if ($column->Key === 'PRI') {
                $this->params['primaryKey'] = $column->Field;
                $this->params['primaryKeyType'] = CodeGenUtil::getPHPType($column->Type);
            }
        }

        $this->params['createTime'] = date('Y-m-d H:i:s');
        $this->params['table'] = $table;
        $this->params['connection'] = $this->db;
        $this->params['humpPrimaryKey'] = CodeGenUtil::convertUnderline($this->params['primaryKey'], false);
        $this->params['ucPrimaryKey'] = CodeGenUtil::convertUnderline($this->params['primaryKey'], true);
        $this->params['tableDesc'] = $tableDesc[0]->TABLE_COMMENT;
        $this->params['columns'] = $columns;

        $this->params['model_namespace'] = ucfirst($this->modelPath ?? '');
        $this->params['model_className'] = CodeGenUtil::convertUnderline($table, true);
        $this->params['model_variableClassName'] = CodeGenUtil::convertUnderline($table, false);

        $this->params['repository_namespace'] = ucfirst($this->repositoryPath ?? '');
        $this->params['repository_className'] = CodeGenUtil::convertUnderline($table, true)."Repository";
        $this->params['repository_variableClassName'] = CodeGenUtil::convertUnderline($table, false)."Repository";

        $this->params['service_namespace'] = ucfirst($this->servicePath ?? '');
        $this->params['service_className'] = CodeGenUtil::convertUnderline($table, true)."Service";
        $this->params['service_variableClassName'] = CodeGenUtil::convertUnderline($table, false)."Service";

        $this->params['controller_namespace'] = ucfirst($this->controllerPath ?? '');
        $this->params['controller_className'] = CodeGenUtil::convertUnderline($table, true)."Controller";
        $this->params['controller_variableClassName'] = CodeGenUtil::convertUnderline($table, false)."Controller";
    }

    /**
     * 生成Model
     */
    private function generateModel()
    {
        if (empty($this->modelPath)) {
            return;
        }
        $source = base_path("codeGen".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."model".DIRECTORY_SEPARATOR."template");
        $target = base_path(str_replace('\\', DIRECTORY_SEPARATOR, lcfirst($this->params['model_namespace'])).DIRECTORY_SEPARATOR.$this->params['model_className'].".php");
        $params = [];

        $columnFields = [];
        $modelComments = [];
        foreach ($this->params['columns'] as $column) {
            $string = ' * @property ';
            $string .= sprintf('%-7s', CodeGenUtil::getPHPType($column->Type));
            $string .= sprintf('%-25s', ' $'.$column->Field);
            $string .= " {$column->Comment}";
            if ($column->Default !== NULL) {
                $string .= "(默认值:{$column->Default})";
            }
            $columnFields[] = "'{$column->Field}'";
            $modelComments[] = $string;
        }
        $params['column_fields_forSelect'] = implode(', ', $columnFields);
        $params['modelComments'] = implode("\n", $modelComments);

        try {
            $this->returnMsg[] = CodeGenUtil::generateFile($source, $target, array_merge($this->params, $params));
        } catch (Exception $e) {
            $this->returnMsg[] = $e->getMessage();
        }
    }

    /**
     * 生成Repository
     */
    private function generateRepository()
    {
        if (empty($this->repositoryPath)) {
            return;
        }
        $source = base_path("codeGen".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."repository".DIRECTORY_SEPARATOR."template");
        $target = base_path(str_replace('\\', DIRECTORY_SEPARATOR, lcfirst($this->params['repository_namespace'])).DIRECTORY_SEPARATOR.$this->params['repository_className'].".php");
        $params = [];

        try {
            $this->returnMsg[] = CodeGenUtil::generateFile($source, $target, array_merge($this->params, $params));
        } catch (Exception $e) {
            $this->returnMsg[] = $e->getMessage();
        }
    }

    /**
     * 生成Service
     */
    private function generateService()
    {
        if (empty($this->servicePath)) {
            return;
        }
        $source = base_path("codeGen".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."service".DIRECTORY_SEPARATOR."template");
        $target = base_path(str_replace('\\', DIRECTORY_SEPARATOR, lcfirst($this->params['service_namespace'])).DIRECTORY_SEPARATOR.$this->params['service_className'].".php");
        $params = [];

        try {
            $this->returnMsg[] = CodeGenUtil::generateFile($source, $target, array_merge($this->params, $params));
        } catch (Exception $e) {
            $this->returnMsg[] = $e->getMessage();
        }
    }

    /**
     * 生成Controller
     */
    private function generateController()
    {
        if (empty($this->controllerPath)) {
            return;
        }
        $source = base_path("codeGen".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."controller".DIRECTORY_SEPARATOR."template");
        $target = base_path(str_replace('\\', DIRECTORY_SEPARATOR, lcfirst($this->params['controller_namespace'])).DIRECTORY_SEPARATOR.$this->params['controller_className'].".php");
        $params = [];
        $params['view_resource_point'] = str_replace("/", '.', trim($this->resourcePath, "/"));

        $requestParams = [];
        $controllerParams = [];
        foreach ($this->params['columns'] as $column) {
            if (stripos($column->Key, "PRI") !== false) {
                continue;
            }
            $requestParams[] = '            "'.$column->Field.'" => "'.CodeGenUtil::getPHPType($column->Type).'",';
            $controllerParams[] = '        $params["'.$column->Field.'"] = $request->input("'.$column->Field.'");';
        }
        $params['request_params'] = implode("\n", $requestParams);
        $params['controller_params'] = implode("\n", $controllerParams);

        try {
            $this->returnMsg[] = CodeGenUtil::generateFile($source, $target, array_merge($this->params, $params));
        } catch (Exception $e) {
            $this->returnMsg[] = $e->getMessage();
        }
    }

    /**
     * 生成页面
     */
    private function generateView()
    {
        if ($this->viewType == "PJAX") {
            $source = base_path("codeGen".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."viewPjax".DIRECTORY_SEPARATOR);
        } else if ($this->viewType == "PAGE") {
            $source = base_path("codeGen".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."viewPage".DIRECTORY_SEPARATOR);
        } else {
            return;
        }
        $target = str_replace("\\", DIRECTORY_SEPARATOR, base_path("resources".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR.$this->resourcePath.DIRECTORY_SEPARATOR));

        $params = [];
        $params['view_route'] = $this->rootRoute;
        $params['table_title'] = $this->setTableTitle();
        $params['table_content'] = $this->setTableContent();
        $params['addPage_content'] = $this->setAddPageContent();
        $params['editPage_content'] = $this->setEditPageContent();

        // view页面
        try {
            $this->returnMsg[] = CodeGenUtil::generateFile($source."index", $target."index.blade.php", array_merge($this->params, $params));
            $this->returnMsg[] = CodeGenUtil::generateFile($source."addPage", $target."addPage.blade.php", array_merge($this->params, $params));
            $this->returnMsg[] = CodeGenUtil::generateFile($source."editPage", $target."editPage.blade.php", array_merge($this->params, $params));
        } catch (Exception $e) {
            $this->returnMsg[] = $e->getMessage();
        }

        // 追加路由
        $file = base_path("routes".DIRECTORY_SEPARATOR."web.php");
        $data = '';
        $data .= "\n";
        $data .= '$app->get(\''.$this->rootRoute.'/index\',        \''.$this->params['controller_className'].'@index\');'."\n";
        $data .= '$app->get(\''.$this->rootRoute.'/add_page\',     \''.$this->params['controller_className'].'@addPage\');'."\n";
        $data .= '$app->get(\''.$this->rootRoute.'/edit_page\',    \''.$this->params['controller_className'].'@editPage\');'."\n";
        $data .= '$app->post(\''.$this->rootRoute.'/add_submit\',  \''.$this->params['controller_className'].'@addSubmit\');'."\n";
        $data .= '$app->post(\''.$this->rootRoute.'/edit_submit\', \''.$this->params['controller_className'].'@editSubmit\');'."\n";
        $data .= '$app->get(\''.$this->rootRoute.'/delete\',       \''.$this->params['controller_className'].'@delete\');'."\n";
        $data .= "\n";
        file_put_contents($file, $data, FILE_APPEND);
    }

    private function setTableTitle()
    {
        $tableTitle = '';
        foreach ($this->params['columns'] as $column) {
            $tableTitle .= '                                <th>'.$column->Comment.'</th>'."\n";
        }
        $tableTitle .= '                                <th>操作</th>';
        return $tableTitle;
    }

    private function setTableContent()
    {
        $tableContent = '';
        foreach ($this->params['columns'] as $column) {
            $tableContent .= '                                        <td>{{ $item->'.trim($column->Field, '\'').' }}</td>'."\n";
        }
        $tableContent .= '                                        <td> -- </td>';
        return $tableContent;
    }

    private function setAddPageContent()
    {
        if ($this->viewType == "PJAX") {
            $template = '';
            $template .= "\n";
            $template .= '                <div class="form-group">'."\n";
            $template .= '                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-${field}">${comment}</label>'."\n";
            $template .= '                    <div class="col-sm-10 col-md-10 col-lg-10">'."\n";
            $template .= '                        <input type="text" class="form-control" id="pjax-form-${field}" name="${field}" required>'."\n";
            $template .= '                        <span class="help-block m-b-none"></span>'."\n";
            $template .= '                    </div>'."\n";
            $template .= '               </div>'."\n";
        } else if ($this->viewType == "PAGE") {
            $template = '';
            $template .= "\n";
            $template .= '                            <div class="form-group">'."\n";
            $template .= '                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-${field}">${comment}</label>'."\n";
            $template .= '                                <div class="col-sm-10 col-md-10 col-lg-10">'."\n";
            $template .= '                                    <input type="text" class="form-control" id="page-edit-form-${field}" name="${field}" required>'."\n";
            $template .= '                                    <span class="help-block m-b-none"></span>'."\n";
            $template .= '                                </div>'."\n";
            $template .= '                            </div>'."\n";
        } else {
            return "";
        }

        $content = "";
        foreach ($this->params['columns'] as $column) {
            if (stripos($column->Key, "PRI") !== false) {
                continue;
            }
            $params = [
                'field' => $column->Field,
                'comment' => $column->Comment,
            ];
            $content .= CodeGenUtil::variableFill($template, array_merge($this->params, $params));
        }
        return $content;
    }

    private function setEditPageContent()
    {
        if ($this->viewType == "PJAX") {
            $template = '';
            $template .= "\n";
            $template .= '                <div class="form-group">'."\n";
            $template .= '                    <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="pjax-form-${field}">${comment}</label>'."\n";
            $template .= '                    <div class="col-sm-10 col-md-10 col-lg-10">'."\n";
            $template .= '                        <input type="text" class="form-control" id="pjax-form-${field}" name="${field}" value="{{ $${model_variableClassName}->${field} }}" required>'."\n";
            $template .= '                        <span class="help-block m-b-none"></span>'."\n";
            $template .= '                    </div>'."\n";
            $template .= '               </div>'."\n";
        } else if ($this->viewType == "PAGE") {
            $template = '';
            $template .= "\n";
            $template .= '                            <div class="form-group">'."\n";
            $template .= '                                <label class="col-sm-2 col-md-2 col-lg-2 control-label" for="page-edit-form-${field}">${comment}</label>'."\n";
            $template .= '                                <div class="col-sm-10 col-md-10 col-lg-10">'."\n";
            $template .= '                                    <input type="text" class="form-control" id="page-edit-form-${field}" name="${field}" value="{{ $${model_variableClassName}->${field} }}" required>'."\n";
            $template .= '                                    <span class="help-block m-b-none"></span>'."\n";
            $template .= '                                </div>'."\n";
            $template .= '                            </div>'."\n";
        } else {
            return "";
        }

        $content = "";
        foreach ($this->params['columns'] as $column) {
            if (stripos($column->Key, "PRI") !== false) {
                continue;
            }
            $params = [
                'field' => $column->Field,
                'comment' => $column->Comment,
            ];
            $content .= CodeGenUtil::variableFill($template, array_merge($this->params, $params));
        }
        return $content;
    }
}