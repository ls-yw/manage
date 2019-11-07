<?php

namespace application\models\manage;

use woodlsy\phalcon\basic\BasicModel;

class Admin extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{admin}}';

    protected $_targetDb = 'manage';

    public function attribute()
    {
        return [
            'id'        => 'ID',
            'username'  => '用户名',
            'password'  => '密码',
            'salt'      => '盐值',
            'create_at' => '创建时间',
            'update_at' => '更新时间',
            'create_by' => '创建人（记录管理员）',
            'update_by' => '更新人（记录管理员）',
        ];
    }
}