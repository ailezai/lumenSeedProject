<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/2/6
 */

namespace AiLeZai\Util\Lumen\CodeGen\Services;

use Exception;

class CodeGenUtil
{
    public static $columnTypes = [
        'varchar'   =>  'string',
        'char'      =>  'string',
        'text'      =>  'string',
        'int'       =>  'int',
        'tinyint'   =>  'int',
        'bigint'    =>  'int',
        'float'     =>  'float',
        'double'    =>  'float',
        'datetime'  =>  'string',
        'timestamp' =>  'string',
        'time'      =>  'string',
        'date'      =>  'string',
    ];

    /**
     * CodeGenUtil constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        // 只支持在本地环境开发
        if (env('APP_ENV', 'pdt') !== 'loc') {
            throw new Exception("只支持在本地环境使用代码生成模板...生成失败");
        }
    }

    /**
     * 下划线转驼峰
     *
     * @param $str
     * @param bool $ucFirst
     *
     * @return string
     */
    public static function convertUnderline($str , $ucFirst = true)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ','',lcfirst($str));
        return $ucFirst ? ucfirst($str) : $str;
    }

    /**
     * 变量填充
     *
     * @param string $str
     * @param array $params
     *
     * @return string
     */
    public static function variableFill(string $str, array $params)
    {
        $rule = '/\${(.*?)}/';
        while (true) {
            preg_match($rule, $str, $match);
            if (count($match) > 0) {
                if (empty($params[$match[1]])) {
                    $replace = '';
                } else {
                    $replace = $params[$match[1]];
                }
                $str = str_replace($match[0], $replace, $str);
            } else {
                break;
            }
        }
        return $str;
    }

    /**
     * 获取映射关系类型
     *
     * @param $string
     *
     * @return mixed|string
     */
    public static function getPHPType($string)
    {
        if (($pos = strpos($string, '(')) !== false
            || ($pos = strpos($string, ' ')) !== false) {
            $string = substr($string, 0, $pos);
        }
        return static::$columnTypes[$string] ?? 'string';
    }

    /**
     * 判断路径是否存在，不存在则创建
     *
     * @param $dir
     *
     * @return bool
     */
    public static function checkDir($dir)
    {
        return is_dir($dir) || static::checkDir(dirname($dir)) && mkdir($dir, 0777);
    }

    /**
     * 生成模板文件
     *
     * @param string $resourceFile 模板来源
     * @param string $targetFile   目标文件
     * @param array $params        替换参数
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function generateFile(string $resourceFile, string $targetFile, array $params)
    {
        $folder = dirname($targetFile);
        CodeGenUtil::checkDir($folder);
        if(file_exists($targetFile)) {
            throw new Exception("文件 {$targetFile} 已存在");
        }

        $resource = fopen($resourceFile, 'r');
        $target = fopen($targetFile, 'w');
        try {
            while(!feof($resource)){
                $str = fgets($resource);
                $str = CodeGenUtil::variableFill($str, $params);
                fwrite($target, $str);
            }
            return "{$targetFile}生成完成";
        } catch (Exception $e) {
            throw $e;
        } finally {
            fclose($resource);
            fclose($target);
        }
    }
}