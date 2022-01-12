<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/7/19
 */

namespace AiLeZai\Lumen\Framework\Enums;

use ReflectionClass;
use ReflectionException;

class BaseEnum
{
    /**
     * 获取枚举类描述名称（默认为数组第0位）
     * 若为数组，则默认为枚举常量
     * 若为字符串，则默认为枚举常量名
     *
     * @param $param
     * @param $default
     *
     * @return string|array
     * @throws ReflectionException
     */
    public static function getName($param, $default = '')
    {
        // 若为数组，则默认为枚举常量
        if (is_array($param)) {
            if (empty($param) || empty($param[0])) {
                return $default;
            }
            return $param[0];
        }

        // 若为字符串，则默认为枚举常量名
        $allConst = static::getAllConst();
        foreach ($allConst as $const) {
            if (!empty($const) && !empty($const[0]) && $const[0] == $param) {
                return $const[0];
            }
        }

        return $default;
    }

    /**
     * 获取枚举类中文（默认为数组第1位）
     * 若为数组，则默认为枚举常量
     * 若为字符串，则默认为枚举常量名
     *
     * @param $param
     * @param $default
     *
     * @return string|array
     * @throws ReflectionException
     */
    public static function getDesc($param, $default = '')
    {
        // 若为数组，则默认为枚举常量
        if (is_array($param)) {
            if (empty($param) || empty($param[1])) {
                return $default;
            }
            return $param[1];
        }

        // 若为字符串，则默认为枚举常量名
        $allConst = static::getAllConst();
        foreach ($allConst as $const) {
            if (!empty($const) && !empty($const[0]) && $const[0] == $param) {
                return $const[1];
            }
        }

        return $default;
    }

    /**
     * 获得该枚举类所有成员
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public static function getAllConst()
    {
        $reflect = new ReflectionClass(static::class);
        $constant = $reflect->getConstants();
        $formatConstant = [];
        foreach ($constant as $item) {
            $formatConstant[$item[0]] = $item;
        }
        return $formatConstant;
    }
}