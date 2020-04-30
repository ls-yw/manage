<?php

use application\base\BaseTask;
use application\library\HelperExtend;
use application\logic\novel\BookLogic;
use application\logic\novel\CollectLogic;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class NovelCollectTask extends BaseTask
{
    public function mainAction()
    {
        $books = (new BookLogic())->getList(null, null, 1);

        if (!empty($books)) {
            foreach ($books as $val) {
                Log::write($val['id'], "========================开始采集【{$val['book_name']}】【{$val['book_id']}】======================", 'collect');
                $this->start($val);
                Log::write($val['id'], "========================采集结束【{$val['book_name']}】【{$val['book_id']}】======================", 'collect');
            }
        }
    }

    private function _start(array $book)
    {
        $indexlink = $this->_getIndexlink((int)$book['book_collect_id'], $book['book_from_article_id']);
        $key = 'collect_' . $book['book_collect_id'] . '_' . $book['book_from_article_id'] . '_' . $indexlink . '_' . $book['id'];
        if (!Redis::getInstance()->exists($key)) {
            $data = (new CollectLogic())->article($book['book_collect_id'], $book['book_from_article_id'], $indexlink, $book['id']);
            Redis::getInstance()->setex($key, 600, HelperExtend::jsonEncode($data));
        }

        $data = HelperExtend::jsonDecode(Redis::getInstance()->get($key));
        // 判断是否已采集
        $collectForm = (new CollectLogic())->getCollectFromCount($book['id']);
        if (empty($collectForm)) {
            (new CollectLogic())->addCollectFrom($data);
        } else {
            $from = (new CollectLogic())->getCollectFrom($book['id']);

            $new_from = array();
            $add_from = array();
            foreach ($data as $key => $value) {
                if (isset($from[$key]) && $value['from_article_id'] == $from[$key]['from_article_id']) {
                    if ($from[$key]['from_state'] == 0) $new_from[] = $from[$key];
                } else {
                    $new_from[]     = $value;  //需要重新采集的内容
                    $add_from[$key] = $value;  //需要新增如数据库的内容
                }
            }

            if (isset($add_from['0']) && is_array($add_from['0'])) {
                (new CollectLogic())->addCollectFrom($add_from);
            }

            if (is_array($data['0']) && !isset($new_from['0'])) {
                Log::write($book['id'], '无新内容可采集', 'collect');
                return true;
            }


        }
    }

    /**
     * 获取indexLink
     *
     * @author woodlsy
     * @param int $collectId
     * @param     $targetId
     * @return string
     */
    private function _getIndexlink(int $collectId, $targetId) :string
    {
        $collect = (new CollectLogic())->getById($collectId);
        if (empty($collect['collect_indexlink'])) {
            return '';
        }
        $indexlink    = (new CollectLogic())->getUrl($collect, $targetId, 'collect_indexlink');
        if (false === $indexlink) {
            return '';
        }
        $indexlink    = HelperExtend::dealRegular($indexlink);
        if (false === $indexlink) {
            return '';
        }
        $result = '';
        //获取<{indexlink}>
        preg_match('/' . $indexlink[0] . $indexlink[3] . $indexlink[1] . '/i', $html, $result);
        return $result[1];
    }

    private function _chapter($bookId)
    {
        $chapterArray = (new BookLogic())->getChapter($bookId, '默认章节');

        if (empty($chapterArray)) {
            //默认新增章节 默认章节，采集的文章都放入此章节内
            $chapter                       = [];
            $chapter['chapter_name']       = '默认章节';
            $chapter['book_id']            = $bookId;
            $chapter['book_name']          = (new BookLogic())->getBookNameById($bookId);
            $chapter['chapter_articlenum'] = 0;
            $chapter['chapter_order']      = 1;
            $chapterId                     = (new BookLogic())->saveChapter($chapter);
            if (empty($chapterId)) {
                Log::write($bookId, '新增章节失败', 'collect');
                return false;
            }
            Log::write($bookId, '新增默认章节', 'collect');
        } else {
            $chapterId = $chapterArray[0]['id'];
        }
        //更新最后采集时间
        (new BookLogic())->updateCollectTime($bookId);
    }

    private function _article()
    {
        $res = (new CollectLogic())->collectArticle($bookId, $collectId, $chapterId, $categoryId, $fromSort);

        if (empty($res['new_from'])) {
            return $this->ajaxReturn(0, '<div class="col-xs-4">' . $res['msg'] . '</div> <div class="col-xs-4">采集完成</div>');
        }
    }
}