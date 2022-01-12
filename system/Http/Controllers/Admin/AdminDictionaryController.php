<?php

namespace System\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;
use System\Services\Admin\AdminDictionaryService;

class AdminDictionaryController extends BaseController
{
    /**
     * @var AdminDictionaryService
     */
    protected $adminDictionaryService;

    /**
     * AdminDictionaryController constructor.
     *
     * @param AdminDictionaryService $adminDictionaryService
     */
    public function __construct(AdminDictionaryService $adminDictionaryService)
    {
        $this->adminDictionaryService = $adminDictionaryService;
    }

    /**
     * 字典表页面
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request)
    {
        $condition['name'] = $request->input('name', null);
        $list = $this->adminDictionaryService->listPaginateByCondition($condition)->appends($request->all());

        return view('_system.admin.dictionary.index')
            ->with('request', $request)
            ->with('list', $list);
    }

    /**
     * 新增字典页
     *
     * @return View
     */
    public function addPage()
    {
        return view('_system.admin.dictionary.addPage');
    }

    /**
     * 编辑字典页
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws DataNotFoundException
     */
    public function editPage(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric'
        ]);
        $id = $request->input('id');
        $dictionary = $this->adminDictionaryService->getById($id);
        if (empty($dictionary)) {
            throw new DataNotFoundException('字典不存在');
        }
        return view('_system.admin.dictionary.editPage')
            ->with('dictionary', $dictionary);
    }

    /**
     * 新建字典
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     */
    public function addSubmit(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
            'key' => 'required|array',
            'value' => 'required|array',
        ]);
        $name = $request->input('name');
        $desc = $request->input('desc');
        $key = $request->input('key');
        $value = $request->input('value');
        $this->adminDictionaryService->addSubmit($name, $desc, $key, $value);
        return ajax_response()->ajaxSuccessResponse('新建字典成功', [], 'reload');
    }

    /**
     * 修改字典
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     */
    public function editSubmit(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
            'desc' => 'required|string',
            'key' => 'required|array',
            'value' => 'required|array',
        ]);
        $id = $request->input('id');
        $desc = $request->input('desc');
        $key = $request->input('key');
        $value = $request->input('value');
        $this->adminDictionaryService->editSubmit($id, $desc, $key, $value);
        return ajax_response()->ajaxSuccessResponse('修改字典成功', [], 'reload');
    }
}