<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class User extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{user}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'        => 'ID',
            'username'  => '用户名',
            'password'  => '密码',
            'salt'      => '随机盐值',
            'last_ip'   => 'ip',
            'last_time' => '最后登录时间',
            'count'     => '登录次数',
            'create_at' => '创建时间',
            'update_at' => '更新时间',
            'create_by' => '创建人（记录管理员）',
            'update_by' => '更新人（记录管理员）',
        ];
    }
}