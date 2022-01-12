<?php
namespace AiLeZai\Common\Lib\Config;

trait IConfigCache
{

    public $conf_path;

    public $current_conf;

    /**
     * @param $conf_path
     * @throws \Exception
     */
    public function initConfigKey($conf_path)
    {
        $this->conf_path = $conf_path;
        $this->hasConfigChanged();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasConfigChanged()
    {
        $last_conf = $this->current_conf;
        $this->current_conf = CFG::get($this->conf_path);

        if (empty($this->current_conf)) {
            throw new \Exception("CFG::get(" . $this->conf_path . ") return empty!");
        }

        return json_encode($last_conf) != json_encode($this->current_conf);
    }
}

