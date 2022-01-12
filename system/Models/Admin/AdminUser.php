<?php

namespace System\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AdminUser
 * @package System\Models\Admin
 * 系统管理-用户
 *
 * @property string  $admin_user_id           
 * @property string  $username                用户名
 * @property string  $password                密码
 * @property string  $token                   用户令牌
 * @property string  $name                    姓名(默认值:管理员)
 * @property string  $mail                    邮箱
 * @property string  $mobile                  手机
 * @property string  $login_ip                登录IP(默认值:0)
 * @property string  $login_time              登录时间
 * @property string  $status                  状态(默认值:NORMAL)
 * @property string  $create_time             创建时间
 * @property string  $modify_time             更新时间
 *
 * @property object  $role_grant              角色
 * @property object  $permission_grant        授予权限
 * @property object  $permission_forbid       禁用权限
 *
 * @method AdminUser whereByFilter($filter)
 * @method AdminUser orderByFilter($filter)
 * @method AdminUser selectFullFields()
 */
class AdminUser extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'admin_user';

    protected $primaryKey = 'admin_user_id';

    protected $guarded = ['admin_user_id'];

    public $timestamps = true;

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'modify_time';

    /**
     * 查询所有字段
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSelectFullFields($query)
    {
        return $query->select('admin_user_id', 'username', 'password', 'token', 'name', 'mail', 'mobile', 'login_ip', 'login_time', 'status', 'create_time', 'modify_time');
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function role_grant() : BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_user_role_grant', 'admin_user_id', 'role_id')->withPivot('is_admin')
            ->withTimestamps('create_time', 'modify_time');
    }

    /**
     * A user has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permission_grant() : BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_user_permission_grant', 'admin_user_id', 'permission_id')
            ->withTimestamps('create_time', 'modify_time');
    }

    /**
     * A user has and belongs to many permissions which are forbidden.
     *
     * @return BelongsToMany
     */
    public function permission_forbid()
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_user_permission_forbid', 'admin_user_id', 'permission_id')
            ->withTimestamps('create_time', 'modify_time');
    }
}