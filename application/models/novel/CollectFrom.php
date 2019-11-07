<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class CollectFrom extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{collect_from}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'              => 'ID',
            'from_book_id'    => '采集结果小说ID',
            'from_collect_id' => '采集节点ID',
            'from_article_id' => '目标文章ID',
            'from_url'        => '目标网址',
            'from_sort'       => '采集文章排序',
            'from_title'      => '小说标题',
            'from_state'      => '状态',
            'url'             => '文章内容地址',
            'create_at'       => '创建时间',
            'update_at'       => '更新时间',
            'create_by'       => '创建人（记录管理员）',
            'update_by'       => '更新人（记录管理员）',
        ];
    }
}