<?php

namespace System\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AdminRole
 * @package System\Models\Admin
 * 系统管理-角色
 *
 * @property string  $role_id                 
 * @property string  $alias                   标识
 * @property string  $name                    角色名称
 * @property string  $parent_role_id          父级角色(默认值:0)
 * @property string  $parent_role_list        父级角色链，','分割(默认值:)
 * @property string  $type                    角色类别(默认值:DEFAULT)
 * @property string  $group                   类内分组(默认值:DEFAULT)
 * @property string  $create_time             创建时间
 * @property string  $modify_time             更新时间
 *
 * @property object  $users                   管理员
 * @property object  $permission_grant        授予权限
 *
 * @method AdminRole whereByFilter($filter)
 * @method AdminRole orderByFilter($filter)
 * @method AdminRole selectFullFields()
 * @method AdminRole withUsers()
 * @method AdminRole withPermissionGrant()
 */
class AdminRole extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'admin_role';

    protected $primaryKey = 'role_id';

    protected $guarded = ['role_id'];

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
        return $query->select('role_id', 'alias', 'name', 'parent_role_id', 'parent_role_list', 'type', 'group', 'create_time', 'modify_time');
    }

    /**
     * 关联父级角色
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent_role()
    {
        return $this->hasOne(AdminRole::class, 'role_id', 'parent_role_id');
    }

    /**
     * 关联子角色
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children_role()
    {
        return $this->hasMany(AdminRole::class, 'parent_role_id', 'role_id');
    }

    /**
     * A role belongs to many users.
     *
     * @return BelongsToMany
     */
    public function users() : BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'admin_user_role_grant', 'role_id', 'admin_user_id')->withPivot('is_admin')
            ->withTimestamps('create_time', 'modify_time');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWithUsers($query)
    {
        return $query->with('users');
    }

    /**
     * A role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permission_grant() : BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission_grant', 'role_id', 'permission_id')
            ->withTimestamps('create_time', 'modify_time');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWithPermissionGrant($query)
    {
        return $query->with('permission_grant');
    }
}