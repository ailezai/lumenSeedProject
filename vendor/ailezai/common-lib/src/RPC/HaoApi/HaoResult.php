<?php
namespace AiLeZai\Common\Lib\RPC\HaoApi;

class HaoResult
{
    /** @var int 错误码 */
    public $errorCode = -1;
    /** @var string 错误信息 */
    public $errorStr = '';
    /** @var array 额外信息 */
    public $extraInfo;
    /** @var int 结果集的数量 */
    public $resultCount;
    /** @var array|string|null 结果数据（多为model组成的数组，或单个model） */
    public $results;
    /** @var int 是否有下一页 */
    public $endMark;
    /** @var float 耗时(秒) */
    public $timeCost;
    /** @var string 服务端完成处理的时间点 */
    public $timeNow;
    /** @var string 一般=`HaoResult` */
    public $modelType;

    /**
     * @var string 响应的jwt
     */
    public $token;

    public function __construct($haoResult)
    {
        // 将同名字段依次赋值到$this
        foreach (get_object_vars($this) as $property => $v) {
            if (array_key_exists($property, $haoResult)) {
                $this->$property = $haoResult[$property];
            }
        }

        // 特殊处理errorCode  (0表示成功, <空> => -1)
        if ($this->errorCode === 0 || $this->errorCode === '0') {
            $this->errorCode = 0;
        } else {
            $this->errorCode = intval($this->errorCode);
            if (empty($this->errorCode)) {
                $this->errorCode = -1;
            }
        }
    }
}
