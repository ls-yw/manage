<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class Article extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{article}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'           => 'ID',
            'title'        => '文章标题',
            'chapter_id'   => '文章所属章节ID',
            'book_id'      => '文章所属小说ID',
            'article_sort' => '文章排序',
            'wordnumber'   => '文章字数',
            'url'          => 'oss地址',
            'is_oss'       => '是否上传到oss',
            'create_at'    => '创建时间',
            'update_at'    => '更新时间',
            'create_by'    => '创建人（记录管理员）',
            'update_by'    => '更新人（记录管理员）',
        ];
    }
}