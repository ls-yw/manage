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
                Log::write($val['id'], "========================开始采集【{$val['book_name']}】【{$val['id']}】======================", 'collect');
                try {
                    $this->_start($val);
                } catch (Exception $e) {
                    Log::write($val['id'], '采集文章时错误：' . strip_tags($e->getMessage()), 'collect');
                }
                Log::write($val['id'], "========================采集结束【{$val['book_name']}】【{$val['id']}】======================", 'collect');

                sleep(5);
                Log::write($val['id'], "========================开始上传OSS【{$val['book_name']}】【{$val['id']}】======================", 'collect');
                $this->_ossUpload((int) $val['id']);
                Log::write($val['id'], "========================结束上传OSS【{$val['book_name']}】【{$val['id']}】======================", 'collect');
                sleep(5);
            }
        }
    }

    /**
     * 开始采集
     *
     * @author woodlsy
     * @param array $book
     * @return bool
     * @throws \application\library\ManageException
     * @throws \woodlsy\httpClient\HttpClientException
     */
    private function _start(array $book)
    {
//        $indexlink = $this->_getIndexlink((int) $book['book_collect_id'], $book['book_from_article_id']);
        $indexlink = '';
        $key       = 'collect_' . $book['book_collect_id'] . '_' . $book['book_from_article_id'] . '_' . $indexlink . '_' . $book['id'];
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
        $this->_chapter($book);
        return true;
    }

    /**
     * 获取indexLink
     *
     * @author woodlsy
     * @param int $collectId
     * @param     $targetId
     * @return string
     */
/*    private function _getIndexlink(int $collectId, $targetId) : string
    {
        $collect = (new CollectLogic())->getById($collectId);
        if (empty($collect['collect_indexlink'])) {
            return '';
        }
        $indexlink = (new CollectLogic())->getUrl($collect, $targetId, 'collect_indexlink');
        if (false === $indexlink) {
            return '';
        }
        $indexlink = HelperExtend::dealRegular($indexlink);
        if (false === $indexlink) {
            return '';
        }
        $result = '';
        //获取<{indexlink}>
        preg_match('/' . $indexlink[0] . $indexlink[3] . $indexlink[1] . '/i', $html, $result);
        return $result[1];
    }*/

    /**
     * 获取章节
     *
     * @author woodlsy
     * @param array $book
     * @return bool
     */
    private function _chapter(array $book)
    {
        $bookId       = $book['id'];
        $chapterArray = (new BookLogic())->getChapter($bookId, '默认章节');

        if (empty($chapterArray)) {
            //默认新增章节 默认章节，采集的文章都放入此章节内
            $chapter                       = [];
            $chapter['chapter_name']       = '默认章节';
            $chapter['book_id']            = $bookId;
            $chapter['book_name']          = (new BookLogic())->getBookNameById($bookId);
            $chapter['chapter_articlenum'] = 0;
            $chapter['chapter_order']      = 1;
            $chapterId                     = (int) (new BookLogic())->saveChapter($chapter);
            if (empty($chapterId)) {
                Log::write($bookId, '新增章节失败', 'collect');
                return false;
            }
            Log::write($bookId, '新增默认章节', 'collect');
        } else {
            $chapterId = (int) $chapterArray[0]['id'];
        }
        //更新最后采集时间
        (new BookLogic())->updateCollectTime($bookId);
        $this->_article($book, $chapterId);
        return true;
    }

    /**
     * 文章采集
     *
     * @author woodlsy
     * @param array $book
     * @param int   $chapterId
     */
    private function _article(array $book, int $chapterId)
    {
        $fromSort = 0;
        do {
            try {
                $res      = (new CollectLogic())->collectArticle($book['id'], $book['book_collect_id'], $chapterId, $book['book_category'], $fromSort);
                $fromSort = $res['from_sort'];
                Log::write($book['id'], strip_tags($res['msg']), 'collect');
            } catch (Exception $e) {
                Log::write($book['id'], '采集文章时错误：' . strip_tags($e->getMessage()), 'collect');
                $res['new_from'] = 0;
            }
        } while (!empty($res['new_from']));
        Log::write($book['id'], '采集完成', 'collect');
    }

    /**
     * 上传OSS
     *
     * @author woodlsy
     * @param int $bookId
     */
    private function _ossUpload(int $bookId)
    {
        $ossArticles = (new BookLogic())->getArticleByOssAll($bookId, 0, 'article_sort asc');
        if (empty($ossArticles)) {
            Log::write('OSS', '该小说无待上传OSS文章', 'collect');
            return;
        }
        foreach ($ossArticles as $val) {
            try {
                (new CollectLogic())->uploadOss($bookId, $val);
                Log::write('OSS', $val['title'] . '上传成功', 'collect');
            } catch (Exception $e) {
                Log::write('OSS', $val['title'] . '上传失败，失败原因：' . $e->getMessage(), 'collect');
            }
        }
    }
}