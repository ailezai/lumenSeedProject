<?php

namespace System\Models\Admin;

use Illuminate\Database\Eloquent\Builder;
use AiLeZai\Lumen\Framework\Models\BaseModel;

/**
 * Class AdminDictionary
 * @package System\Models\Admin
 * 字典表
 *
 * @property string  $id                      
 * @property string  $name                    字段名(默认值:UNNAMED)
 * @property string  $desc                    字段描述(默认值:)
 * @property string  $dictionary              字典[key => value]
 * @property string  $create_time             创建时间
 * @property string  $modify_time             更新时间
 *
 * @method AdminDictionary whereByFilter($filter)
 * @method AdminDictionary orderByFilter($filter)
 * @method AdminDictionary selectFullFields()
 */
class AdminDictionary extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'admin_dictionary';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

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
        return $query->select('id', 'name', 'desc', 'dictionary', 'create_time', 'modify_time');
    }
}