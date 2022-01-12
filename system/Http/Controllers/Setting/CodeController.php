<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/7/20
 */

namespace System\Http\Controllers\Setting;

use Illuminate\Http\Request;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;
use AiLeZai\Util\Lumen\CodeGen\Services\GenerateService;

class CodeController extends BaseController
{
    /**
     * 代码生成器参数设置页
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $db = array_keys(config('database.connections'));
        $modelPath = 'app\Models';
        $repositoryPath = 'app\Repositories';
        $servicePath = 'app\Services';
        $controllerPath = 'app\Http\Controllers';
        $viewType = 'NONE';
        $resourcePath = 'tmp/path/';
        $rootRoute = 'tmp/route';
        return view('_system.setting.code.index')
            ->with('db', $db)
            ->with('modelPath', $modelPath)
            ->with('repositoryPath', $repositoryPath)
            ->with('servicePath', $servicePath)
            ->with('controllerPath', $controllerPath)
            ->with('viewType', $viewType)
            ->with('resourcePath', $resourcePath)
            ->with('rootRoute', $rootRoute);
    }

    /**
     * 代码生成
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function generate(Request $request)
    {
        $this->validate($request, [
            'db'              => 'required|string',
            'model_path'      => 'string',
            'repository_path' => 'string',
            'service_path'    => 'string',
            'controller_path' => 'string',
            'view_type'       => 'required|string',
            'resource_path'   => 'string',
            'root_route'      => 'string',
            'table'           => 'required|string',
        ]);
        $db             = $request->input('db', null);
        $modelPath      = $request->input('model_path', null);
        $repositoryPath = $request->input('repository_path', null);
        $servicePath    = $request->input('service_path', null);
        $controllerPath = $request->input('controller_path', null);
        $viewType       = $request->input('view_type', null);
        $resourcePath   = $request->input('resource_path', null);
        $rootRoute      = $request->input('root_route', null);

        $generateService = new GenerateService($db, $modelPath, $repositoryPath, $servicePath, $controllerPath, $viewType, $resourcePath, $rootRoute);
        $table          = $request->input('table', null);
        $result = $generateService->generate($table);
        return ajax_response()->ajaxSuccessResponse("生成结束", $result);
    }
}