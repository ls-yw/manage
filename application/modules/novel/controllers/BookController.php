<?php

namespace application\modules\novel\controllers;

use application\base\BaseController;
use application\library\AliyunOss;
use application\library\HelperExtend;
use application\library\ManageException;
use application\logic\novel\BookLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class BookController extends BaseController
{

    public function indexAction()
    {
        $type    = $this->get('type');
        $keyword = $this->get('keyword', 'string');

        if ('author' === $type) {
            $searchType = 'book_author';
        } else {
            $searchType = 'book_name';
        }

        $this->view->data      = (new BookLogic())->getList($searchType, $keyword, null, $this->page, $this->size);
        $this->view->totalPage = ceil((new BookLogic())->getListCount($searchType, $keyword) / $this->size);
        $this->view->page      = $this->page;
        $this->view->pageLink  = '?page={page}&keyword=' . $keyword . '&type=' . $type;
        $this->view->title     = '小说管理';

        $this->view->type    = $type;
        $this->view->keyword = $keyword;
    }

    public function setAction()
    {
        $id   = (int) $this->get('id');
        $book = [];
        if (!empty($id)) {
            $book = (new BookLogic())->getById($id);
            if (!$book) {
                Redis::getInstance()->setex('alert_error', 3600, '小说不存在');
                die('<script>window.history.go(-1);</script>');
            }
        }
        if ($this->request->isPost()) {
            try {
                $data = [
                    'id'              => $id,
                    'book_name'       => trim($this->post('book_name', 'string')),
                    'book_category'   => (int) $this->post('book_category', 'int'),
                    'book_author'     => trim($this->post('book_author', 'string')),
                    'book_state'      => (int) $this->post('book_state', 'int'),
                    'book_keyword'    => trim($this->post('book_keyword', 'string')),
                    'book_intro'      => trim($this->post('book_intro', 'string')),
                    'book_is_collect' => (int) $this->post('monitoring', 'int'),
                    'book_img'        => trim($this->post('book_img', 'string')),
                    'is_recommend'    => (int) $this->post('is_recommend', 'int'),
                    'quality'         => (int) $this->post('quality', 'int'),
                ];

                if (empty($data['book_img'])) {
                    Redis::getInstance()->setex('alert_error', 3600, '请上传小说封面图片');
                    die('<script>window.history.go(-1);</script>');
                }

                $row = (new BookLogic())->save($data);
                if (!$row) {
                    Redis::getInstance()->setex('alert_error', 3600, '保存小说失败');
                    die('<script>window.history.go(-1);</script>');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/novel/book/index.html');
            } catch (Exception $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            }
        } else {

            $this->view->moduleName = $this->router->getModuleName();
            $this->view->config     = $this->config;
            $this->view->category   = (new BookLogic())->getCategoryPairs();
            $this->view->book       = $book;
            $this->view->title      = empty($id) ? '新增小说' : '编辑《' . $book['book_name'] . '》';
            $this->view->menuflag   = 'novel-book-index';
        }
    }

    /**
     * 文章列表
     *
     * @author woodlsy
     */
    public function articleAction()
    {
        $bookId = (int) $this->get('book_id');

        $book = (new BookLogic())->getById($bookId);

        $crumbs   = [];
        $crumbs[] = ['url' => '/novel/book/index.html', 'name' => $book['book_name']];

        $this->view->data      = (new BookLogic())->getArticleList($bookId, 'article_sort asc', $this->page, $this->size);
        $this->view->totalPage = ceil((new BookLogic())->getArticleListCount($bookId) / $this->size);
        $this->view->page      = $this->page;
        $this->view->title     = '文章管理 ';
        $this->view->chapters  = HelperExtend::arrayToKeyValue((new BookLogic())->getChapter($bookId), 'id', 'chapter_name');
        $this->view->pageLink  = '?page={page}&book_id=' . $bookId;
        $this->view->book_id   = $bookId;
        $this->view->menuflag  = 'novel-book-index';
        $this->view->crumbs    = $crumbs;
    }

    public function chapterAction()
    {
        $bookId = (int) $this->get('book_id');
        $book   = (new BookLogic())->getById($bookId);

        $crumbs   = [];
        $crumbs[] = ['url' => '/novel/book/index.html', 'name' => $book['book_name']];

        $this->view->data     = (new BookLogic())->getChapter($bookId);
        $this->view->page     = $this->page;
        $this->view->title    = '分卷管理 ';
        $this->view->book_id  = $bookId;
        $this->view->menuflag = 'novel-book-index';
        $this->view->crumbs   = $crumbs;
    }

    /**
     * 编辑文章
     *
     * @author woodlsy
     * @return \Phalcon\Http\ResponseInterface
     * @throws ManageException
     */
    public function setArticleAction()
    {
        $bookId    = (int) $this->get('book_id');
        $articleId = (int) $this->get('id');
        if ($this->request->isPost()) {

            try {

                $data               = [
                    'title'        => $this->post('title'),
                    'chapter_id'   => (int)$this->post('chapter_id'),
                    'article_sort' => $this->post('article_sort'),
                    'content'      => $this->post('content'),
                    'book_id'      => $bookId,
                ];
                $data['wordnumber'] = mb_strlen(strip_tags($data['content']), mb_detect_encoding($data['content']));
                if (empty($data['content'])) {
                    Redis::getInstance()->setex('alert_error', 3600, '文章内容不能为空');
                    die('<script>window.history.go(-1);</script>');
                }

                $oldChapterId  = 0;
                $newChapterId = $data['chapter_id'];
                if (!empty($articleId)) {
                    $article = (new BookLogic())->getArticleById(0, $articleId);
                    $oldChapterId = (int)$article['chapter_id'];
                }
                if ($oldChapterId !== $newChapterId) {
                    if(!empty($oldChapterId)) {
                        (new BookLogic())->updateChapterArticleNum((int)$oldChapterId, 'decr', 1);
                    }
                    (new BookLogic())->updateChapterArticleNum((int)$newChapterId, 'incr', 1);
                }

                $row = (new BookLogic())->saveArticle($data, $articleId);
                if (empty($row)) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/novel/book/article.html?book_id=' . $bookId);
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                die('<script>window.history.go(-1);</script>');
            }

        } else {
            $title   = $this->get('title');
            $sort    = (int) $this->get('sort', 'int');
            $article = [];
            $book    = (new BookLogic())->getById($bookId);

            if (!empty($articleId)) {
                $article = (new BookLogic())->getArticleById($book['book_category'], $articleId, true);
            }

            $crumbs   = [];
            $crumbs[] = ['url' => '/novel/book/index.html', 'name' => $book['book_name']];

            $this->view->articleTitle = $title;
            $this->view->sort         = $sort;
            $this->view->article      = $article;
            $this->view->chapters     = HelperExtend::arrayToKeyValue((new BookLogic())->getChapter($bookId), 'id', 'chapter_name');
            $this->view->title        = '编辑文章';
            $this->view->menuflag     = 'novel-book-index';
            $this->view->crumbs       = $crumbs;
        }
    }

    public function setChapterAction()
    {
        $bookId    = (int) $this->get('book_id');
        $chapterId = (int) $this->get('id');
        $book      = (new BookLogic())->getById($bookId);
        if ($this->request->isPost()) {
            $data = [
                'chapter_name'  => $this->post('chapter_name'),
                'chapter_order' => (int) $this->post('chapter_order'),
                'book_name'     => $book['book_name'],
                'book_id'       => $bookId,
                'id'            => $chapterId
            ];
            try {

                if (empty($data['chapter_name'])) {
                    throw new ManageException('分卷名称不能为空');
                }

                $row = (new BookLogic())->saveChapter($data);
                if (empty($row)) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/novel/book/chapter.html?book_id=' . $bookId);
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                die('<script>window.history.go(-1);</script>');
            }
        } else {
            $chapter = [];

            if (!empty($chapterId)) {
                $chapter = (new BookLogic())->getChapterById($chapterId);
            }

            $crumbs   = [];
            $crumbs[] = ['url' => '/novel/book/index.html', 'name' => $book['book_name']];

            $this->view->chapter  = $chapter;
            $this->view->title    = '编辑分卷';
            $this->view->menuflag = 'novel-book-index';
            $this->view->crumbs   = $crumbs;
        }
    }

    /**
     * 删除章节
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function delArticleAction()
    {
        if ($this->request->isPost()) {
            $articleId = (int) $this->post('id');
            if (empty($articleId)) {
                return $this->ajaxReturn(1, '参数错误');
            }
            $article = (new BookLogic())->getArticleById(0, $articleId);
            try {
                (new AliyunOss())->delFile((int) $article['book_id'], $articleId);
            } catch (Exception $e) {
                return $this->ajaxReturn(1, '删除oss章节失败');
            }
            $row = (new BookLogic())->delArticleById($articleId);
            if (empty($row)) {
                return $this->ajaxReturn(1, '删除失败');
            }
            (new BookLogic())->updateChapterArticleNum((int)$article['chapter_id'], 'decr', 1);
            (new BookLogic())->updateBookArticleNumAndWordsNumber((int) $article['book_id']);
            return $this->ajaxReturn(0, '删除成功');
        }
    }

    /**
     * 删除分卷
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function delChapterAction()
    {
        if ($this->request->isPost()) {
            $id = (int) $this->post('id');
            if (empty($id)) {
                return $this->ajaxReturn(1, '参数错误');
            }
            $chapter = (new BookLogic())->getChapterById($id);
            if (!empty($chapter['chapter_articlenum'])) {
                return $this->ajaxReturn(1, '该分卷下还有章节，请先清空章节再删分卷');
            }
            $row = (new BookLogic())->delChapterById($id);
            if (empty($row)) {
                return $this->ajaxReturn(1, '删除失败');
            }
            return $this->ajaxReturn(0, '删除成功');
        }
    }

    /**
     * 清空小说文章
     *
     * @author woodlsy
     * @return \Phalcon\Http\ResponseInterface
     */
    public function clearArticleAction()
    {
        $bookId = (int) $this->post('id');
        if (empty($bookId)) {
            return $this->ajaxReturn(1, '参数错误');
        }

        // 先删除oss文件
        $list = (new BookLogic())->getArticleByOssRow($bookId, 1, 100);
        if (!empty($list)) {
            $articleIdArray = [];
            foreach ($list as $val) {
                $articleIdArray[] = $val['id'];
            }
            $msg = '<p>开始删除oss文件 文章ID：' . current($articleIdArray) . ' - ' . end($articleIdArray) . "</p>";
            try {
                (new AliyunOss())->delFiles($bookId, $articleIdArray);
            } catch (Exception $e) {
                $msg .= "<p>oss文件删除失败</p>";
                return $this->ajaxReturn(1, $msg . $e->getMessage());
            }
            $msg .= "<p>oss文件删除成功</p>";
            $row = (new BookLogic())->updateArticleOssState($articleIdArray, 0);
            if (empty($row)) {
                $msg .= "<p>oss状态更新失败</p>";
                return $this->ajaxReturn(1, $msg);
            } else {
                $msg .= "<p>oss状态更新成功</p>";
                return $this->ajaxReturn(200, $msg);
            }
        }

        // 再删除本地文章数据
        $book   = (new BookLogic())->getById($bookId);
        $delRes = HelperExtend::delBookDir($book['book_category'], $bookId);
        if (!$delRes) {
            return $this->ajaxReturn(1, "<p>本地小说文件夹删除失败</p>");
        }
        $msg = "<p>本地小说文件夹删除成功</p>";
        $row = (new BookLogic())->delArticleByBookId($bookId);
        (new BookLogic())->updateBookArticleNumAndWordsNumber($bookId);
        if (empty($row)) {
            return $this->ajaxReturn(1, $msg . "<p>删除数据库文章数据失败</p>");
        } else {
            return $this->ajaxReturn(0, $msg . "<p>删除数据库文章数据成功</p>");
        }

    }

    /**
     * 删除小说
     *
     * @author woodlsy
     * @return mixed
     */
    public function delAction()
    {
        $bookId = (int) $this->get('book_id');
        if (empty($bookId)) {
            $this->breakError('参数错误');
        }

        try {
            $row = (new BookLogic())->delBook($bookId);
            if (empty($row)) {
                $this->breakError('删除失败');
            }
            Redis::getInstance()->setex('alert_success', 3600, '删除成功');
            return $this->response->redirect('/novel/book/index.html');
        } catch (ManageException $e) {
            $this->breakError($e->getMessage());
        }

    }

    /**
     * 是否采集
     *
     * @author woodlsy
     * @return \Phalcon\Http\ResponseInterface
     */
    public function isCollectAction()
    {
        $id        = (int) $this->post('id');
        $isCollect = (int) $this->post('isCollect');
        $row       = (new BookLogic())->setIsCollect($id, $isCollect);
        if (!$row) {
            return $this->ajaxReturn(1, '更改失败');
        }
        return $this->ajaxReturn(0, '更改成功');
    }

    /**
     * 更改质量
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function changeQualityAction()
    {
        $id      = (int) $this->post('id');
        $quality = (int) $this->post('quality');
        $row     = (new BookLogic())->changeQuality($id, $quality);
        if (!$row) {
            return $this->ajaxReturn(1, '更改失败');
        }
        return $this->ajaxReturn(0, '更改成功');
    }
}