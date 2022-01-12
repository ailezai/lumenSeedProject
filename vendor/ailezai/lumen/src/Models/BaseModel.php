<?php

namespace AiLeZai\Lumen\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * 通过指定$filter，获取where查询条件
     *
     * @param $query
     * @param array $filter
     *
     * @return $this
     */
    public function scopeWhereByFilter($query, array $filter)
    {
        $model = $query;
        foreach ($filter as $item) {
            $whereName   = $item['whereName'];
            $whereParams = $item['whereParams'];
            $model       = $model->$whereName(...$whereParams);
        }
        return $model;
    }
}