<?php

/**
 * Created by CodeGenerate
 * Version: v1.0
 * User: CodeGenerate made by FRM
 * TempleTime: 2018-01-24 20:34:41
 * CreateTime: 2018-02-11 23:14:25
 */

namespace App\Http\Controllers\Stats;

use Illuminate\Http\Request;
use Illuminate\View\View;
use ReflectionException;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;
use App\Services\Stats\StatsEggsService;

class StatsEggsController extends BaseController
{

    /**
     * @var StatsEggsService
     */
    protected $statsEggsService;

    public function __construct(StatsEggsService $statsEggsService)
    {
        $this->statsEggsService = $statsEggsService;
    }

    /**
     * 登录日志
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws ReflectionException
     */
    public function index(Request $request)
    {
        $result  = $this->statsEggsService->eggsStatsResult();

        return view('stats.eggs.index')
            ->with('list', $result)
            ->with('request',$request);
    }
}