<?php
namespace System\Services\Admin;

use Exception;
use Illuminate\Support\Facades\DB;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use System\Models\Admin\AdminMenu;
use System\Repositories\Admin\AdminMenuRepository;
use System\Supports\HotRefresh;

class AdminMenuService
{
    /**
     * @var AdminMenuRepository
     */
    protected $adminMenuRepository;

    /**
     * AdminMenuService constructor.
     *
     * @param AdminMenuRepository $adminMenuRepository
     */
    public function __construct(AdminMenuRepository $adminMenuRepository)
    {
        $this->adminMenuRepository = $adminMenuRepository;
    }

    /**
     * 获取菜单树状图
     *
     * @return array
     */
    public function getMenuTree()
    {
        return $this->adminMenuRepository->menuToTree();
    }

    /**
     * 修改菜单排序
     *
     * @param array $menus
     */
    public function editOrder(array $menus)
    {
        $order = 1;
        foreach ($menus as $menu) {
            $data = [
                'parent_menu_id' => 0,
                'order' => $order
            ];
            $this->adminMenuRepository->updateByMenuId($menu['id'], $data);
            $order++;
            if (!empty($menu['children'])) {
                foreach ($menu['children'] as $childMenu) {
                    $data = [
                        'parent_menu_id' => $menu['id'],
                        'order' => $order
                    ];
                    $this->adminMenuRepository->updateByMenuId($childMenu['id'], $data);
                    $order++;
                    if (!empty($childMenu['children'])) {
                        foreach ($childMenu['children'] as $grandChild) {
                            $data = [
                                'parent_menu_id' => $childMenu['menu_id'],
                                'order' => $order
                            ];
                            $this->adminMenuRepository->updateByMenuId($grandChild['id'], $data);
                            $order++;
                        }
                    }
                }
            }
        }

        // 刷新权限
        HotRefresh::refreshAll();
    }

    /**
     * 添加 / 编辑菜单
     *
     * @param integer|null $menuId
     * @param integer|null $parentMenuId
     * @param string       $title
     * @param string       $icon
     * @param string       $path
     *
     * @throws DataNotFoundException
     */
    public function submit($menuId, $parentMenuId, string $title, string $icon, string $path)
    {
        DB::beginTransaction();
        try {

            /**
             * @var AdminMenu $menu
             */
            $menu = $this->adminMenuRepository->firstOrCreate(['menu_id' => $menuId], []);

            if (empty($menu->parent_menu_id)) {
                $menu->parent_menu_id = $parentMenuId;
            }
            $menu->title = $title;
            $menu->icon = $icon;
            $menu->path = $path;
            $menu->save();

            DB::commit();
        } catch (Exception $e) {
            // 系统异常
            DB::rollback();
            throw new DataNotFoundException('提交失败');
        }

        // 刷新权限
        HotRefresh::refreshAll();
    }

    /**
     * 删除菜单
     *
     * @param $menuId
     *
     * @throws DataNotFoundException
     */
    public function deleteMenu($menuId)
    {
        $childCount = $this->adminMenuRepository->countByParentMenuId($menuId);
        if ($childCount > 0) {
            throw new DataNotFoundException('删除失败，请先删除子菜单');
        }

        $this->adminMenuRepository->deleteByMenuId($menuId);

        // 刷新权限
        HotRefresh::refreshAll();
    }

    /**
     * 获取菜单详情
     *
     * @param int $menuId
     *
     * @return null|AdminMenu
     *
     * @throws DataNotFoundException
     */
    public function getByMenuId(int $menuId)
    {
        $menu = $this->adminMenuRepository->getByMenuId($menuId);
        if (empty($menu->menu_id)) {
            throw new DataNotFoundException('数据不存在');
        }
        return $menu;
    }
}