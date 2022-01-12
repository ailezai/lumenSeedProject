<?php

namespace System\Models\Admin;

use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use System\Traits\ModelTree;

/**
 * Class AdminMenu
 * @package System\Models\Admin
 * 系统管理-菜单
 *
 * @property string  $menu_id                 
 * @property string  $parent_menu_id          父菜单id
 * @property integer $order                   排序，升序排列(默认值:0)
 * @property string  $title                   标题
 * @property string  $icon                    图标
 * @property string  $path                    请求路由
 * @property string  $create_time             创建时间
 * @property string  $modify_time             更新时间
 *
 * @method AdminMenu whereByFilter($filter)
 * @method AdminMenu orderByFilter($filter)
 * @method AdminMenu selectFullFields()
 */
class AdminMenu extends BaseModel
{
    use ModelTree;

    protected $connection = 'mysql';

    protected $table = 'admin_menu';

    protected $primaryKey = 'menu_id';

    protected $guarded = ['menu_id'];

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
        return $query->select('menu_id', 'parent_menu_id', 'order', 'title', 'icon', 'path', 'create_time', 'modify_time');
    }
}