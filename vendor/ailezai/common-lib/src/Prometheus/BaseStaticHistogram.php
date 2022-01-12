<?php

namespace AiLeZai\Common\Lib\Prometheus;

use AiLeZai\Common\Lib\Log\LOG;
use AiLeZai\Common\Lib\Redis\RedisHelper;

class BaseStaticHistogram
{
    /**
     * 每次修改结构后, 都要将这个版本号改成当前日期
     * 保证不同schema的统计数据不会混起来
     */
    const SCHEMA_VER = '_';

    /**
     * @var string 第二段
     * `req_resp` （接口请求统计）
     * `task` （异步任务 或 脚本统计）
     * `rpc` （调用后台服务统计）
     * `db` （数据库操作统计）
     * (命名统一用`[a-zA-Z_][a-zA-Z0-9_]*`  类似变量命名)
     */
    const TYPE = '_';

    /**
     * @var string[] 不同的类型, labels定义不同;
     * (key名必须匹配`[a-zA-Z_][a-zA-Z0-9_]*`  类似变量命名
     *  添加一个label_key,需要对应写一个method注释: "l_{$label_key}")
     */
    const DEFAULT_LABELS = array(/*k,v*/);

    /**
     * @var int[]|float[] 以毫秒为单位的梯度
     * //暂时不支持buckets做动态自定义, 防止schema结构被玩坏
     */
    const DEFAULT_BUCKETS = array(/*v*/);

    /**
     * @param string $oldCategory 传入的category值
     * @return string 修正符合命名规范后的category值
     */
    private static function _fixCategory($oldCategory)
    {
        $newCategory = preg_replace('/[^a-zA-Z0-9_]/', '_', $oldCategory);
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $newCategory) !== 1) {
            $newCategory .= '_'; //替换后还是不符合命名规范 <空>或者数字开头 加`_`解决
        }
        return $newCategory;
    }

    /**
     * @var BaseStaticHistogram[] 父类的这个$instanceMap是用不到的
     */
    protected static $instanceMap = array();

    /**
     * 获取指定$category的实例
     * @param string $category (命名统一用`[a-zA-Z_][a-zA-Z0-9_]*`  类似变量命名)
     * @return static
     */
    public static function category($category)
    {
        $fixedCategory = self::_fixCategory($category);
        if (empty(static::$instanceMap[$fixedCategory])) {
            static::$instanceMap[$fixedCategory] = new static($fixedCategory);
        }
        return static::$instanceMap[$fixedCategory];
    }


    // ===== 下面是单例部分 ====


    protected static $currentCategory = '_';

    /**
     * @param string $currentCategory 全局设置的category, 一般用当前项目名
     * @param string[] $initLabels 初始labels值
     */
    public static function init($currentCategory, $initLabels = array())
    {
        static::$currentCategory = $currentCategory;

        $mergedLabels = array_replace(static::DEFAULT_LABELS, array_intersect_key($initLabels, static::DEFAULT_LABELS));
        static::get()->labels($mergedLabels);
    }

    /**
     * @return static 获取通过init()初始化过的`当前`全局统计类
     */
    public static function get()
    {
        return static::category(static::$currentCategory);
    }


    // ===== 下面是动态部分 ====


    /**
     * @var string 第一段
     * 一般用项目名, 例如: jjb_api, java_proxy
     * (命名统一用`[a-zA-Z_][a-zA-Z0-9_]*`  类似变量命名)
     */
    protected $category = '';

    /**
     * BaseStaticHistogram constructor.
     * @param $category
     */
    public function __construct($category)
    {
        $this->category = self::_fixCategory($category);
        $this->labels = static::DEFAULT_LABELS;
    }

    /**
     * @var string[] 结构必须与DEFAULT_LABELS严格保持一致
     */
    protected $labels = array();

    /**
     * 用于提前埋入部分labels值
     * //可多次调用, 内部做提取合并
     * @param string[] $labels
     * @return $this
     */
    public function labels($labels)
    {
        foreach (array_intersect_key($labels, $this->labels) as $k => $v) {
            $this->labels[$k] = strval($v); //将$v转成string类型
        }
        return $this;
    }

    /**
     * 单独更新某个label的值
     * @param string $name 命名: "l_{$label_key}"
     * @param string[] $arguments [0]对应label的值 [1]非空~仅覆盖默认值
     * @return static
     */
    public function __call($name, $arguments)
    {
        $label_key = preg_replace('/^l_/', '', $name);
        if (isset($this->labels[$label_key])) {
            if (empty($arguments[1]) || ($this->labels[$label_key] == static::DEFAULT_LABELS[$label_key])) {
                $this->labels[$label_key] = strval($arguments[0]); //将$v转成string类型
            }
        }
        return $this;
    }

    protected function getKeyName()
    {
        return sprintf('%s:%s:', $this->category, static::TYPE);
    }

    protected function buildRedisKey($schemaMd5)
    {
        return sprintf('PROMETHEUS_:histogram:%s:%s__%s', static::SCHEMA_VER, $this->getKeyName(), $schemaMd5);
    }

    private static function _escapeMeasurement($str)
    {
        return str_replace([' ', ','], ['\ ', '\,'], $str);
    }

    private static function _escapeTagKV_FieldK($str)
    {
        return str_replace([' ', '=', ','], ['\ ', '\=', '\,'], $str);
    }

    private static function _escapeFieldV($v)
    {
        if (is_string($v)) {
            return '"' . str_replace(['"'], ['\"'], $v) . '"'; //string
        } elseif (is_int($v)) {
            return strval($v) . 'i'; //int
        } else {
            return strval($v); //float, bool
        }
    }

    private static function _fmtTag($k, $v)
    {
        return sprintf('%s=%s',
            self::_escapeTagKV_FieldK($k),
            self::_escapeTagKV_FieldK($v)); // <tag_key>=<tag_value>
    }

    private static function _fmtField($k, $v)
    {
        return sprintf('%s=%s',
            self::_escapeTagKV_FieldK($k),
            self::_escapeFieldV($v)); // <field_key>=<field_value>
    }

    /**
     * https://docs.influxdata.com/influxdb/v1.5/write_protocols/line_protocol_reference/
     *
     * @param int|float $millisecond 耗时(毫秒)
     * @return string
     */
    private function _toInfluxDBLineProtocol($millisecond)
    {
        $payload = self::_escapeMeasurement($this->getKeyName()); // <measurement>
        foreach ($this->labels as $k => $v) {
            $payload .= ',' . self::_fmtTag($k, $v); // [,<tag_key>=<tag_value>]
        }

        $payload .= ' ' . self::_fmtField('value', $millisecond); // <field_key>=<field_value>

        //$payload .= ' ' . (microtime(true) * 1000000000); //<timestamp>

        return $payload;
    }

    /**
     * @param int|float $millisecond 耗时(毫秒)
     */
    protected function _writeToUDP($millisecond)
    {
        static $sock = null;
        if (empty($sock)) {
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            // never call `@socket_close($sock);`
        }

        $payload = $this->_toInfluxDBLineProtocol($millisecond);

        $ret = @socket_sendto($sock, $payload, strlen($payload), 0, '127.0.0.1', 7094);
        if ($ret != strlen($payload)) {
            LOG::stat_error(sprintf('%s <- socket_sendto(%s)', $ret, $payload));
        }
    }

    /**
     * @param int|float $millisecond 耗时(毫秒)
     */
    protected function _writeToRedis($millisecond)
    {
        $bIncr = '+Inf';
        foreach (static::DEFAULT_BUCKETS as $b) {
            // 保证buckets是从小到大的顺序, 找到最适合容下$value的bucket
            if ($millisecond <= $b) {
                $bIncr = $b;
                break;
            }
        }

        /**
         * 将`sum`加上$msec ; 将$bIncr计数加1
         * 如果是首次, 将`__meta`存下来; 另外将当前统计key加入全局key集合
         */
        $script = '
redis.call("hIncrByFloat", KEYS[1], KEYS[2], ARGV[1])
local increment = redis.call("hIncrBy", KEYS[1], KEYS[3], 1)
if increment == 1 then
    redis.call("hSet", KEYS[1], "__meta", ARGV[2])
    redis.call("sAdd", KEYS[4], KEYS[1])
end
';
        //$KEY1 = $this->buildRedisKey();
        $KEY2 = json_encode(array('b' => 'sum', 'labelValues' => $this->labels));
        $KEY3 = json_encode(array('b' => $bIncr, 'labelValues' => $this->labels));
        $KEY4 = 'PROMETHEUS_histogram_METRIC_KEYS';

        $ARGV1 = $millisecond;
        $ARGV2 = json_encode(array(
            'name' => $this->getKeyName(),
            'help' => static::SCHEMA_VER,
            'type' => 'histogram',
            'labelNames' => array_keys($this->labels),
            'buckets' => static::DEFAULT_BUCKETS,
        ));

        // 将$schemaMd5作为key名的一部分, 避免不同版本的数据混淆
        $KEY1 = $this->buildRedisKey(md5($ARGV2));

        try {
            RedisHelper::getConn('prometheus')->eval($script,
                array(
                    $KEY1,
                    $KEY2,
                    $KEY3,
                    $KEY4,
                    $ARGV1,
                    $ARGV2,
                ),
                4
            );
        } catch (\Exception $e) {
            LOG::stat_error(LOG::e2str($e));
        }
    }

    /**
     * @var int 默认只写UDP
     * 0b1  : 写Redis
     * 0b10 : 写UDP (influxDB line protocol)
     */
    public static $STAT_DEST_BITS = 0b10;

    /**
     * @param int|float $millisecond 耗时(毫秒)
     */
    public function stat($millisecond)
    {
        try {
            if (static::$STAT_DEST_BITS & 0b1) {
                $this->_writeToRedis($millisecond);
            }

            if (static::$STAT_DEST_BITS & 0b10) {
                $this->_writeToUDP($millisecond);
            }
        } catch (\Exception $e) {
            LOG::stat_error(LOG::e2str($e));
        }
    }
}
