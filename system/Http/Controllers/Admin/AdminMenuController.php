<?php

namespace System\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use System\Services\Admin\AdminMenuService;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class AdminMenuController extends BaseController
{
    /**
     * @var AdminMenuService
     */
    protected $adminMenuService;

    /**
     * AdminMenuController constructor.
     *
     * @param AdminMenuService $adminMenuService
     */
    public function __construct(AdminMenuService $adminMenuService)
    {
        $this->adminMenuService = $adminMenuService;
    }

    /**
     * 菜单列表
     *
     * @return view
     */
    public function index()
    {
        $menus = $this->adminMenuService->getMenuTree();
        return view('_system.admin.menu.index')
            ->with('menus', $menus);
    }

    /**
     * 获取菜单详情
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     */
    public function detail(Request $request)
    {
        $this->validate($request, [
            'menu_id' => 'required|numeric'
        ]);
        $menu = $this->adminMenuService->getByMenuId($request->input('menu_id'));
        return ajax_response()->ajaxSuccessResponse('菜单数据获取成功', $menu->toArray());
    }

    /**
     * 修改菜单排序
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function order(Request $request)
    {
        $this->validate($request, [
            'order_json' => 'required|json'
        ]);
        $menus = json_decode($request->input('order_json'), true);
        $this->adminMenuService->editOrder($menus);

        return ajax_response()->ajaxSuccessResponse('菜单排序保存成功', [], 'reload');
    }

    /**
     * 添加 / 编辑菜单
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     */
    public function submit(Request $request)
    {
        $this->validate($request, [
            'menu_id'        => 'numeric',
            'parent_menu_id' => 'numeric',
            'title'          => 'required',
            'icon'           => 'required',
            'path'           => 'string'
        ]);
        $menuId = $request->input('menu_id');
        $parentMenuId = $request->input('parent_menu_id');
        $title = $request->input('title');
        $icon = $request->input('icon');
        $path = $request->input('path');
        $this->adminMenuService->submit($menuId, $parentMenuId, $title, $icon, $path);

        return ajax_response()->ajaxSuccessResponse('新增菜单成功', [], 'reload');
    }

    /**
     * 删除菜单
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws DataNotFoundException
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'menu_id' => 'required'
        ]);
        $this->adminMenuService->deleteMenu($request->input('menu_id'));

        return ajax_response()->ajaxSuccessResponse('删除成功', [], 'reload');
    }
}