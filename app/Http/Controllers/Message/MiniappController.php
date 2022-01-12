<?php

/**
 * Created by CodeGenerate
 * Version: v1.0
 * User: CodeGenerate made by FRM
 * TempleTime: 2018-01-24 20:34:41
 * CreateTime: 2018-02-11 23:14:25
 */

namespace App\Http\Controllers\Message;

use App\Services\Message\MessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ReflectionException;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class MiniappController extends BaseController
{

    /**
     * @var StatsEggsService
     */
    protected $messageService;

    protected $request;

    public function __construct(MessageService $messageService,Request $request)
    {
        $this->messageService = $messageService;
        $this->request = $request;
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
    public function index()
    {
        $result  = $this->messageService->getList();

        return view('message.index')
            ->with('list', $result)
            ->with('request',$this->request);
    }

    /**
     * 新增页面
     * @return View
     */
    public function addPage()
    {
        return view('message.addPage');
    }

    /**
     * 提交
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSubmit()
    {

        $this->validate($this->request, [
            'title' => 'required|string',
            'business_type' => 'required|string',
            'scence' => 'required|string',
            'keyword1' => 'required|string',

        ]);
        $params['title'] = $this->request->input('title');
        $params['business_type'] = $this->request->input('business_type');
        $params['scence'] = $this->request->input('scence');
        $params['keyword1'] = $this->request->input('keyword1');
        $params['keyword2'] = $this->request->input('keyword2');
        $params['keyword3'] = $this->request->input('keyword3');
        $params['keyword4'] = $this->request->input('keyword4');
        $params['url'] = $this->request->input('url');
        $this->messageService->addData($params);
        return ajax_response()->ajaxSuccessResponse('新增成功', [], 'reload');

    }

    /**
     * 发送消息
     */
    public function sendMsg()
    {
        $this->validate($this->request, [
            'id' => 'required|string',
        ]);
        $id = $this->request->input('id');

        $this->messageService->sendMsg($id);
        return ajax_response()->ajaxSuccessResponse('发送成功', [], 'reload');

    }

    /**
     * 删除
     * @return \Illuminate\Http\JsonResponse
     */
    public function del()
    {
        $this->validate($this->request, [
            'id' => 'required|string',
        ]);
        $id = $this->request->input('id');

        $this->messageService->delete([$id]);
        return ajax_response()->ajaxSuccessResponse('删除成功', [], 'reload');
    }
}