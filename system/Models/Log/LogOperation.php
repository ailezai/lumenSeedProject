<?php

namespace System\Models\Log;

use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LogOperation
 * @package System\Models\Log
 * 日志：操作记录
 *
 * @property string  $id                      
 * @property string  $trace_id                请求标识(默认值:UNKNOWN)
 * @property string  $admin_user_id           用户id
 * @property string  $name                    管理员(默认值:*未知管理员)
 * @property string  $ip                      IP地址(默认值:0)
 * @property string  $session                 会话(默认值:)
 * @property string  $host                    根路径(默认值:)
 * @property string  $method                  请求方法(默认值:)
 * @property string  $path                    请求路由(默认值:)
 * @property string  $request                 请求参数(默认值:)
 * @property string  $error_message           错误信息(默认值:)
 * @property string  $status                  请求状态(默认值:)
 * @property string  $create_time             创建时间
 * @property string  $modify_time             修改时间
 *
 * @method LogOperation whereByFilter($filter)
 * @method LogOperation orderByFilter($filter)
 * @method LogOperation selectFullFields()
 */
class LogOperation extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'log_operation';

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
        return $query->select('id', 'trace_id', 'admin_user_id', 'name', 'ip', 'session', 'host', 'method', 'path', 'request', 'error_message', 'status', 'create_time', 'modify_time');
    }
}