<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2017/12/18
 */

namespace App\Supports;

class WhereFilter
{
    /**
     * 字段过滤
     *
     * @param array           $filter       过滤数组
     * @param string          $field        字段
     * @param string|integer  $value        过滤值
     * @param string          $symbol       比较关系
     *
     * @return array
     */
    public function field(array $filter, string $field, $value, string $symbol = '=')
    {
        if ($field == '') {
            return $filter;
        }

        $filter[] = [
            'whereName'   => 'where',
            'whereParams' => [$field, $symbol, $value]
        ];

        return $filter;
    }

    /**
     * 时间过滤
     *
     * @param array  $filter        过滤数组
     * @param string $field         字段
     * @param string $betweenTime   开始时间
     * @param string $andTime       结束时间
     * @param string $format        时间格式化
     *
     * @return array
     */
    public function time(array $filter, string $field, string $betweenTime = '', string $andTime = '', string $format = 'Y-m-d H:i:s')
    {
        if ($field == '') {
            return $filter;
        }

        if (empty($betweenTime) && !empty($andTime)) {
            // 只有最大时间
            $andTime = date($format, strtotime($andTime));
            $filter[] = [
                'whereName'   => 'where',
                'whereParams' => [$field, '<=', $andTime]
            ];
        } elseif (!empty($betweenTime) && empty($andTime)) {
            // 只有最小时间
            $betweenTime = date($format, strtotime($betweenTime));
            $filter[] = [
                'whereName'   => 'where',
                'whereParams' => [$field, '>=', $betweenTime]
            ];
        } elseif (!empty($betweenTime) && !empty($andTime)) {
            // 最小时间和最大时间都存在
            $betweenTime = date($format, strtotime($betweenTime));
            $andTime = date($format, strtotime($andTime));
            $filter[] = [
                'whereName'   => 'whereBetween',
                'whereParams' => [$field, [$betweenTime, $andTime]]
            ];
        }

        return $filter;
    }

    /**
     * 'or'条件过滤
     *
     * @param array           $filter  过滤数组
     * @param string          $field   字段
     * @param string|integer  $value   过滤值
     * @param string          $symbol  比较符号
     *
     * @return array
     */
    public function orField(array $filter, string $field, $value, string $symbol = '=')
    {
        if ($field == '') {
            return $filter;
        }

        $filter[] = [
            'whereName'   => 'orWhere',
            'whereParams' => [$field, $symbol, $value]
        ];

        return $filter;
    }

    /**
     * 'in'条件过滤
     *
     * @param array  $filter  过滤数组
     * @param string $field   字段
     * @param array  $value   过滤值
     *
     * @return array
     */
    public function inField(array $filter, string $field, array $value = [])
    {
        if ($field == '' || !is_array($value)) {
            return $filter;
        }

        if (!empty($value)) {
            $filter[] = [
                'whereName'   => 'whereIn',
                'whereParams' => [$field, $value]
            ];
        }

        return $filter;
    }

    /**
     * 'between'条件过滤
     *
     * @param array           $filter         过滤数组
     * @param string          $field          字段
     * @param string|integer  $firstValue     过滤条件最小值
     * @param string|integer  $secondValue    过滤条件最大值
     * @return array
     */
    public function betweenField(array $filter, string $field, $firstValue, $secondValue) {
        if ($field == '') {
            return $filter;
        }

        if ($firstValue === null && $secondValue !== null) {
            // 只有最大值
            $filter[] = [
                'whereName'   => 'where',
                'whereParams' => [$field, '<=', $secondValue]
            ];
        } elseif ($firstValue !== null && $secondValue === null) {
            // 只有最小值
            $filter[] = [
                'whereName'   => 'where',
                'whereParams' => [$field, '>=', $firstValue]
            ];
        } elseif ($firstValue !== null && $secondValue !== null) {
            // 最小值和最大值都存在
            $filter[] = [
                'whereName'   => 'whereBetween',
                'whereParams' => [$field, [$firstValue, $secondValue]]
            ];
        }

        return $filter;
    }

    /**
     * 自定义过滤条件
     *
     * @param array  $filter  过滤数组
     * @param string $raw     自定义过滤SQL
     *
     * @return array
     */
    public function raw(array $filter, $raw = '')
    {
        if (!empty($raw)) {
            $filter[] = [
                'whereName'   => 'whereRaw',
                'whereParams' => [$raw]
            ];
        }

        return $filter;
    }
}