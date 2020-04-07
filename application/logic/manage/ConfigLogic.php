<?php
namespace application\logic\manage;

use application\library\HelperExtend;
use application\models\manage\Config;

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
     * 获取所有的配置，数组形式返回
     *
     * @author woodlsy
     * @return array
     */
    public function getAllPairs() : array
    {
        $configs = (new Config())->getAll([]);
        if (empty($configs)) {
            return [];
        }
        $arr = [];
        foreach ($configs as $val) {
            $arr[$val['type']][$val['config_key']] = $v['config_value'];
        }
        return $arr;
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