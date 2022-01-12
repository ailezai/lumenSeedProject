<?php

namespace System\Models\Log;

use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LogLogin
 * @package System\Models\Log
 * 日志：登录记录
 *
 * @property string  $id                      
 * @property string  $username                登录名(默认值:username)
 * @property string  $ip                      IP地址(默认值:0)
 * @property string  $session                 会话(默认值:)
 * @property string  $status                  请求状态(默认值:UNKNOWN)
 * @property string  $create_time             创建时间
 * @property string  $modify_time             修改时间
 *
 * @method LogLogin whereByFilter($filter)
 * @method LogLogin orderByFilter($filter)
 * @method LogLogin selectFullFields()
 */
class LogLogin extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'log_login';

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
        return $query->select('id', 'username', 'ip', 'session', 'status', 'create_time', 'modify_time');
    }
}