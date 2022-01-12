<?php
namespace System\Repositories\Admin;

use Exception;
use System\Models\Admin\AdminUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AdminUserRepository
{
    /**
     * @var AdminUser
     */
    protected $adminUser;

    /**
     * AdminUserRepository constructor.
     *
     * @param AdminUser $adminUser
     */
    public function __construct(AdminUser $adminUser)
    {
        $this->adminUser = $adminUser;
    }

    /**
     * 返回空对象
     *
     * @return AdminUser
     */
    public function getEmptyObject()
    {
        return $this->adminUser;
    }

    /**
     * 根据主键admin_user_id查找
     *
     * @param $adminUserId
     *
     * @return AdminUser|null
     */
    public function getByAdminUserId($adminUserId)
    {
        return $this->adminUser
            ->selectFullFields()
            ->with('role_grant')
            ->with('permission_grant')
            ->with('permission_forbid')
            ->where('admin_user_id', $adminUserId)
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
        return $this->adminUser
            ->selectFullFields()
            ->whereByFilter($filter)
            ->paginate($size);
    }

    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return AdminUser
     */
    public function create(array $data)
    {
        return $this->adminUser->create($data);
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
        return $this->adminUser->firstOrCreate($attributes, $joining);
    }

    /**
     * 根据主键admin_user_id更新
     *
     * @param $adminUserId
     * @param array $data        更新字段
     *
     * @return int
     */
    public function updateByAdminUserId($adminUserId, array $data)
    {
        return $this->adminUser
            ->where('admin_user_id', $adminUserId)
            ->update($data);
    }

    /**
     * 根据主键admin_user_id更新或创建数据
     *
     * @param array $attributes    比较字段
     * @param array $values        更新字段
     *
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->adminUser->updateOrCreate($attributes, $values);
    }

    /**
     * 根据主键admin_user_id删除
     *
     * @param $adminUserId
     *
     * @return int
     *
     * @throws Exception
     */
    public function deleteByAdminUserId($adminUserId)
    {
        return $this->adminUser
            ->where('admin_user_id', $adminUserId)
            ->delete();
    }

    /**
     * 根据username获取数据
     *
     * @param string $username
     *
     * @return AdminUser
     */
    public function getByUsername(string $username)
    {
        return $this->adminUser
            ->selectFullFields()
            ->where('username', $username)
            ->first();
    }

    /**
     * 获取所有管理员id和姓名
     *
     * @return \Illuminate\Support\Collection
     */
    public function listAllAdminUserName()
    {
        return $this->adminUser
            ->select('admin_user_id', 'name')
            ->get();
    }

    /**
     * 获取所有管理员id和姓名、邮箱
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function listAllAdminUserNameAndMail()
    {
        return $this->adminUser
            ->select('admin_user_id', 'name', 'mail')
            ->get();

    }
}