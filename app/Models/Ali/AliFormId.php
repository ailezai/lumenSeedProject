<?php

/**
 * Created by CodeGenerate
 * Version: v1.0
 * User: CodeGenerate made by FRM
 * TempleTime: 2018-01-24 20:34:41
 * CreateTime: 2018-02-11 23:14:25
 */

namespace App\Models\Ali;

use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AliFormId
 * @package App\Models\Message
 * 消息发送配置
 *
 * @method LogLogin whereByFilter($filter)
 * @method LogLogin orderByFilter($filter)
 * @method LogLogin selectFullFields()
 */
class AliFormId extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'ali_form_id';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    public $timestamps = true;

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';


    /**
     * 查询所有字段
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSelectFullFields($query)
    {
        return $query->select('id',
            'open_id',
            'app_id',
            'system_source',
            'used_num',
            'form_id',
            'create_time',
            'update_time');
    }
    public function aliFormIdOpenId()
    {
        return $this->hasMany(AliFormId::class, 'open_id', 'open_id');
    }

}