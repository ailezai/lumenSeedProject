<?php
namespace AiLeZai\Common\Lib\Common;

/**
 * Class ScopeExit
 * @package AiLeZai\Common\Lib\Common
 *
 * 析构时自动调用 $fn()
 */
class ScopeExit
{
    private $fn = null;

    public function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    /**
     * 提前执行 $fn()
     */
    public function execute()
    {
        if (is_callable($this->fn)) {
            call_user_func($this->fn);
            $this->fn = null; // reset to null after execute()
        }
    }

    /**
     * 取消 $fn()
     */
    public function cancel()
    {
        $this->fn = null; // reset to null on cancel()
    }

    public function __destruct()
    {
        // 析构时保证 $fn() 会被执行
        $this->execute();
    }
}
