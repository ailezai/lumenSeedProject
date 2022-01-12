<?php

namespace System\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AdminPermission
 * @package System\Models\Admin
 * 系统管理-权限
 *
 * @property string  $permission_id           
 * @property string  $alias                   标识
 * @property string  $name                    权限名称
 * @property string  $method                  请求方法(默认值:NONE)
 * @property string  $path                    请求路由:","分隔，*通配(默认值:NONE)
 * @property string  $create_time             创建时间
 * @property string  $modify_time             更新时间
 *
 * @property object  $roles                   管理员
 * @property object  $user_grant              授予权限
 * @property object  $user_forbid             禁用权限
 *
 * @method AdminPermission whereByFilter($filter)
 * @method AdminPermission orderByFilter($filter)
 * @method AdminPermission selectFullFields()
 */
class AdminPermission extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'admin_permission';

    protected $primaryKey = 'permission_id';

    protected $guarded = ['permission_id'];

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
        return $query->select('permission_id', 'alias', 'name', 'method', 'path', 'create_time', 'modify_time');
    }

    /**
     * Permission belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permission_grant', 'permission_id', 'role_id')
            ->withTimestamps('create_time', 'modify_time');
    }

    /**
     * Permission belongs to many users.
     *
     * @return BelongsToMany
     */
    public function user_grant() : BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'admin_user_permission_grant', 'permission_id', 'admin_user_id')
            ->withTimestamps('create_time', 'modify_time');
    }

    /**
     * Permission belongs to many users which are forbidden.
     *
     * @return BelongsToMany
     */
    public function user_forbid() : BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'admin_user_permission_forbid', 'permission_id', 'admin_user_id')
            ->withTimestamps('create_time', 'modify_time');
    }
}