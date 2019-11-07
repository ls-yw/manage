<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class Category extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{category}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'          => 'ID',
            'name'        => '栏目名称',
            'parent_id'   => '父级',
            'seo_name'    => 'SEO标题',
            'keyword'     => '栏目关键字',
            'description' => '栏目描述',
            'sort'        => '栏目排序',
            'create_at'   => '创建时间',
            'update_at'   => '更新时间',
            'create_by'   => '创建人（记录管理员）',
            'update_by'   => '更新人（记录管理员）',
        ];
    }
}