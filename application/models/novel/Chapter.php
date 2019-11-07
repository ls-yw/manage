<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class Chapter extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{chapter}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'                 => 'ID',
            'chapter_name'       => '章节名称',
            'book_id'            => '章节所属小说的ID',
            'book_name'          => '章节所属小说的名称',
            'chapter_articlenum' => '该章节下小说内容数量',
            'chapter_order'      => '章节排序',
            'create_at'          => '创建时间',
            'update_at'          => '更新时间',
            'create_by'          => '创建人（记录管理员）',
            'update_by'          => '更新人（记录管理员）',
        ];
    }
}