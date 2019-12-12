<?php
namespace application\logic\novel;

use application\library\HelperExtend;
use application\models\novel\Config;

class ConfigLogic
{
    /**
     * 配置数组
     *
     * @author woodlsy
     * @param string $type
     * @return array
     */
    public function getPairs(string $type)
    {
        return HelperExtend::arrayToKeyValue((new Config())->getAll(['type' => $type]), 'config_key', 'config_value');
    }

    /**
     * 保存配置
     *
     * @author woodlsy
     * @param string $type
     * @param array  $data
     */
    public function save(string $type, array $data)
    {
        foreach ($data as $key => $value) {
            (new Config())->updateData(['config_value' => $value], ['type' => $type, 'config_key' => $key]);
        }
    }
}