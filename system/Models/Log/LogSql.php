<?php
namespace System\Models\Log;

use AiLeZai\Lumen\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LogSql
 * @package System\Models\Log
 * 日志：SQL记录
 *
 * @property string  $id                      
 * @property string  $trace_id                请求标识(默认值:UNKNOWN)
 * @property string  $query                   sql预编译语句(默认值:)
 * @property string  $bindings                绑定数据(默认值:)
 * @property float   $time                    执行时间（毫秒）(默认值:0)
 * @property string  $create_time             创建时间
 * @property string  $modify_time             修改时间
 *
 * @method LogSql whereByFilter($filter)
 * @method LogSql orderByFilter($filter)
 * @method LogSql selectFullFields()
 */
class LogSql extends BaseModel
{
    protected $connection = 'mysql';

    protected $table = 'log_sql';

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
        return $query->select('id', 'trace_id', 'query', 'bindings', 'time', 'create_time', 'modify_time');
    }
}