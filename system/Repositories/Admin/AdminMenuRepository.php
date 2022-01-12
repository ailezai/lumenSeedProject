<?php

namespace System\Repositories\Admin;

use System\Models\Admin\AdminMenu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdminMenuRepository
{
    /**
     * @var AdminMenu
     */
    protected $adminMenu;

    /**
     * AdminMenuRepository constructor.
     *
     * @param AdminMenu $adminMenu
     */
    public function __construct(AdminMenu $adminMenu)
    {
        $this->adminMenu = $adminMenu;
    }

    /**
     * 返回空对象
     *
     * @return AdminMenu
     */
    public function getEmptyObject()
    {
        return $this->adminMenu;
    }

    /**
     * 根据主键menu_id查找
     *
     * @param $menuId
     *
     * @return AdminMenu|null
     */
    public function getByMenuId($menuId)
    {
        return $this->adminMenu
            ->selectFullFields()
            ->where('menu_id', $menuId)
            ->first();
    }

    /**
     * 根据filter过滤，分页查询
     *
     * @param array   $filter 过滤条件
     * @param integer $size   分页大小
     *
     * @return LengthAwarePaginator
     */
    public function listPaginateByFilter($filter = [], $size = 20)
    {
        return $this->adminMenu
            ->selectFullFields()
            ->whereByFilter($filter)
            ->paginate($size);
    }

    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return AdminMenu
     */
    public function create(array $data)
    {
        return $this->adminMenu->create($data);
    }

    /**
     * 创建数据或返回已存在数据
     *
     * @param array $attributes 比较字段
     * @param array $joining    更新字段
     *
     * @return Model
     */
    public function firstOrCreate(array $attributes, array $joining)
    {
        return $this->adminMenu->firstOrCreate($attributes, $joining);
    }

    /**
     * 根据主键menu_id更新
     *
     * @param $menuId
     * @param array $data        更新字段
     *
     * @return int
     */
    public function updateByMenuId($menuId, array $data)
    {
        return $this->adminMenu
            ->where('menu_id', $menuId)
            ->update($data);
    }

    /**
     * 根据主键menu_id更新或创建数据
     *
     * @param array $attributes    比较字段
     * @param array $values        更新字段
     *
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->adminMenu->updateOrCreate($attributes, $values);
    }

    /**
     * 根据主键menu_id删除
     *
     * @param $menuId
     *
     * @return int
     */
    public function deleteByMenuId($menuId)
    {
        return $this->adminMenu
            ->where('menu_id', $menuId)
            ->delete();
    }

    /**
     * 获取所有菜单
     *
     * @return \Illuminate\Support\Collection
     */
    public function listAll()
    {
        return $this->adminMenu
            ->selectFullFields()
            ->orderBy('order', 'ASC')
            ->get();
    }

    /**
     * 菜单树状图
     *
     * @return array
     */
    public function menuToTree()
    {
        return $this->adminMenu->toTree();
    }

    /**
     * 统计字菜单个数
     *
     * @param $parentMenuId
     *
     * @return int
     */
    public function countByParentMenuId($parentMenuId)
    {
        return $this->adminMenu
            ->where('parent_menu_id', $parentMenuId)
            ->count();
    }
}