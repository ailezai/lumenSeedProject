<?php

namespace System\Http\Controllers\Log;

use Illuminate\Http\Request;
use Illuminate\View\View;
use System\Services\Log\LogSqlService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class LogSqlController extends BaseController
{
    /**
     * @var LogSqlService
     */
    protected $logSqlService;

    /**
     * LogSqlController constructor.
     *
     * @param LogSqlService $logSqlService
     */
    public function __construct(LogSqlService $logSqlService)
    {
        $this->logSqlService = $logSqlService;
    }

    /**
     * 登录日志
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'trace_id' => 'string',
            'time1' => 'numeric',
            'time2' => 'numeric',
        ]);
        $condition['trace_id'] = $request->input('trace_id', null);
        $condition['time1'] = $request->input('time1', null);
        $condition['time2'] = $request->input('time2', null);
        $list = $this->logSqlService->listPaginateByCondition($condition)->appends($request->all());
        return view('_system.log.sql')
            ->with('request', $request)
            ->with('list', $list);
    }

}