<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class Book extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{book}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'                     => 'ID',
            'book_name'              => '小说名称',
            'book_category'          => '小说分类id',
            'book_author'            => '小说作者',
            'book_keyword'           => '小说关键字',
            'book_description'       => '小说描述',
            'book_intro'             => '小说简介',
            'book_img'               => '小说封面图片',
            'book_click'             => '小说浏览次数',
            'book_monthclick'        => '月点击量',
            'book_weekclick'         => '周点击量',
            'book_dayclick'          => '日点击量',
            'book_recommend'         => '推荐次数',
            'book_coll'              => '收藏次数',
            'book_flag'              => '小说状态标记',
            'book_state'             => '书本连载状态',
            'book_articlenum'        => '小说章节数（文章数）',
            'book_wordsnumber'       => '小说字数',
            'book_collect_id'        => '采集节点的ID',
            'book_from_article_id'   => '采集目标文章序号',
            'book_is_collect'        => '是否可以继续采集',
            'book_last_collect_time' => '最后采集时间',
            'book_lasttime'          => '小说最后点击时间',
            'create_at'              => '创建时间',
            'update_at'              => '更新时间',
            'create_by'              => '创建人（记录管理员）',
            'update_by'              => '更新人（记录管理员）',
        ];
    }
}