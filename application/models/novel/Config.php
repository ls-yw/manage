<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class Config extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{config}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'           => 'ID',
            'type'         => '类型',
            'config_key'   => 'key',
            'config_value' => 'value',
            'create_at'    => '创建时间',
            'update_at'    => '更新时间',
            'create_by'    => '创建人（记录管理员）',
            'update_by'    => '更新人（记录管理员）',
        ];
    }
}