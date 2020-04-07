<?php

namespace application\models\blog;

use woodlsy\phalcon\basic\BasicModel;

class ArticleTag extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{article_tag}}';

    protected $_targetDb = 'blog';

    public function attribute()
    {
        return [
            'id'         => 'ID',
            'article_id' => '文章ID',
            'tag'        => 'tag',
            'is_deleted' => '是否删除',
            'create_at'  => '创建时间',
            'update_at'  => '更新时间',
            'create_by'  => '创建人（记录管理员）',
            'update_by'  => '更新人（记录管理员）',
        ];
    }
}