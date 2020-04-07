<?php

namespace application\models\blog;

use woodlsy\phalcon\basic\BasicModel;

class Article extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{article}}';

    protected $_targetDb = 'blog';

    public function attribute()
    {
        return [
            'id'           => 'ID',
            'category_id'  => '分类ID',
            'title'        => '标题',
            'desc'         => '简介',
            'content'      => '内容',
            'img_url'      => '文章首图',
            'open_comment' => '是否允许评论',
            'comment_num'  => '评论数量',
            'likes'        => '点赞数',
            'clicks'       => '查看数',
            'tags'         => 'tags',
            'is_deleted'   => '是否删除',
            'is_push'      => '是否发布',
            'sort'         => '排序',
            'fixed_time'   => '定时发布',
            'create_at'    => '创建时间',
            'update_at'    => '更新时间',
            'create_by'    => '创建人（记录管理员）',
            'update_by'    => '更新人（记录管理员）',
        ];
    }
}