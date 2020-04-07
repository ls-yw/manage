<?php

namespace application\models\blog;

use woodlsy\phalcon\basic\BasicModel;

class Category extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{category}}';

    protected $_targetDb = 'blog';

    public function attribute()
    {
        return [
            'id'         => 'ID',
            'name'       => '分类名称',
            'pid'        => '父类ID',
            'is_deleted' => '是否删除',
            'create_at'  => '创建时间',
            'update_at'  => '更新时间',
            'create_by'  => '创建人（记录管理员）',
            'update_by'  => '更新人（记录管理员）',
        ];
    }
}