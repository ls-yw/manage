<?php

namespace application\models\novel;

use woodlsy\phalcon\basic\BasicModel;

class Collect extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{collect}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'                     => 'ID',
            'collect_name'           => '采集名称',
            'collect_host'           => '节点网站',
            'collect_subchapterid'   => '章节子序号运算方式',
            'collect_subarticleid'   => '文章子序号运算方式',
            'collect_iconv'          => '目标网站编码',
            'collect_urlarticle'     => '文章信息页面地址',
            'collect_articletitle'   => '文章标题采集规则',
            'collect_author'         => '作者采集规则',
            'collect_sort'           => '文章类型采集规则',
            'collect_sortid'         => '文章类型对应关系',
            'collect_keyword'        => '关键字采集规则',
            'collect_intro'          => '内容简介采集规则',
            'collect_articleimage'   => '封面图片采集规则',
            'collect_filterimage'    => '过滤的封面图片',
            'collect_indexlink'      => '目录页面链接采集规则',
            'collect_fullarticle'    => '全文标记采集规则',
            'collect_urlindex'       => 'collect_urlindex',
            'collect_volume'         => '分卷名称采集规则',
            'collect_chapter'        => '章节名称采集规则',
            'collect_chapterid'      => '章节序号采集规则',
            'collect_urlchapter'     => '章节内容页面地址',
            'collect_content'        => '章节内容采集规则',
            'collect_contentfilter'  => '章节内容过滤规则',
            'collect_contentreplace' => '章节内容替换规则',
            'collect_status'         => '采集状态',
            'is_deleted'             => '是否删除',
            'create_at'              => '创建时间',
            'update_at'              => '更新时间',
            'create_by'              => '创建人（记录管理员）',
            'update_by'              => '更新人（记录管理员）',
        ];
    }
}