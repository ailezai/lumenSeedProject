<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/2/4
 */

namespace AiLeZai\Util\Lumen\CodeGen\Services;

use Exception;

class CheckConfigService
{
    /**
     * @var string 项目根路径
     */
    protected $basePath;

    /**
     * @var string codeGen的项目路径
     */
    protected $vendorPath;

    protected $codeGenDir;

    protected $configFile;

    /**
     * CheckConfigService constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        // 只支持在本地环境开发
        if (env('APP_ENV', 'pdt') !== 'loc') {
            throw new Exception("只支持在本地环境使用代码生成模板...生成失败");
        }
        $this->basePath = base_path().DIRECTORY_SEPARATOR;
        $this->vendorPath = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
        $this->codeGenDir = $this->basePath.'codeGen'.DIRECTORY_SEPARATOR;
        $this->configFile = $this->codeGenDir.'config.php';
    }

    /**
     * 检查路径和文件
     */
    public function checkRouteAndFile()
    {
        if (!is_dir($this->codeGenDir)) {
            mkdir($this->codeGenDir,0777,true);
        }

        // 主配置文件（及使用说明）
        $exampleConfigFile = $this->vendorPath.'codeGen'.DIRECTORY_SEPARATOR.'config.php';
        if (!is_file($this->configFile)) {
            $this->checkOrCopyFile($exampleConfigFile, $this->configFile);
            $this->checkOrCopyFile($this->vendorPath.'codeGen'.DIRECTORY_SEPARATOR.'README.md', $this->codeGenDir.'README.md');
        }

        // 模板文件
        $sourceDir = $this->vendorPath.'codeGen'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
        $targetDir = $this->codeGenDir.'templates'.DIRECTORY_SEPARATOR;
        if (!is_dir($targetDir)) {
            $this->copyDir($sourceDir, $targetDir);
        }
    }

    /**
     * 复制文件夹
     *
     * @param string $source
     * @param string $target
     */
    public function copyDir(string $source, string $target)
    {
        if (!file_exists($target)) mkdir($target);
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_source = $source . DIRECTORY_SEPARATOR . $item;
            $_target = $target . DIRECTORY_SEPARATOR . $item;
            if (is_file($_source)) copy($_source, $_target);
            if (is_dir($_source)) $this->copyDir($_source, $_target);
        }
        closedir($handle);
    }

    /**
     * 拷贝文件（仅用于项目中无文件时拷贝默认文件）
     *
     * @param string $resource    源文件
     * @param string $destination 目标文件
     */
    private function checkOrCopyFile(string $resource, string $destination)
    {
        if (file_exists($resource)) {
            $rOpen = fopen($resource,"r");

            //定位
            $position = strripos($destination, DIRECTORY_SEPARATOR);
            $dir = substr($destination, 0, $position);
            if (!file_exists($dir)) {
                //可创建多级目录
                mkdir($dir,0777,true);
            }

            $dOpen = fopen($destination,"w+");

            //边读边写
            $buffer = 1024;
            while (!feof($rOpen)) {
                fwrite($dOpen, fread($rOpen, $buffer));
            }

            fclose($rOpen);
            fclose($dOpen);
        } else {
            exit;
        }
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getVendorPath(): string
    {
        return $this->vendorPath;
    }

    /**
     * @return string
     */
    public function getCodeGenDir(): string
    {
        return $this->codeGenDir;
    }

    /**
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }
}