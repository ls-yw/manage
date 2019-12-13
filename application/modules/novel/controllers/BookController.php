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
        $keywords              = $this->get('keywords', 'string');
        $this->view->data      = (new BookLogic())->getList($keywords, null, $this->page, $this->size);
        $this->view->totalPage = ceil((new BookLogic())->getListCount($keywords) / $this->size);
        $this->view->page      = $this->page;
        $this->view->pageLink  = '?page={page}&keywords=' . $keywords;
        $this->view->title     = '小说管理';
    }

    public function setAction()
    {
        $id   = (int) $this->get('id');
        $book = (new BookLogic())->getById($id);
        if (!$book) {
            Redis::getInstance()->setex('alert_error', 3600, '小说不存在');
            die('<script>window.history.go(-1);</script>');
        }
        if ($this->request->isPost()) {
            $data = [
                'id'              => $id,
                'book_name'       => trim($this->post('book_name', 'string')),
                'book_category'   => (int) $this->post('book_category', 'int'),
                'book_author'     => trim($this->post('book_author', 'string')),
                'book_state'      => $this->post('book_state', 'int'),
                'book_keyword'    => trim($this->post('book_keyword', 'string')),
                'book_intro'      => trim($this->post('book_intro', 'string')),
                'book_is_collect' => $this->post('monitoring', 'int'),
                'book_img'        => trim($this->post('book_img', 'string')),
                'is_recommend'    => (int) $this->post('is_recommend', 'int'),
                'quality'         => (int) $this->post('quality', 'int'),
            ];

            $row = (new BookLogic())->save($data);
            if (!$row) {
                Redis::getInstance()->setex('alert_error', 3600, '保存小说失败');
                die('<script>window.history.go(-1);</script>');
            }
            Redis::getInstance()->setex('alert_success', 3600, '保存成功');
            return $this->response->redirect('/novel/book/index.html');
        } else {

            $this->view->moduleName = $this->router->getModuleName();
            $this->view->config     = $this->config;
            $this->view->category   = (new BookLogic())->getCategoryPairs();
            $this->view->book       = $book;
            $this->view->title      = '编辑《' . $book['book_name'] . '》';
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

        $this->view->data      = (new BookLogic())->getArticleList($bookId, 'article_sort asc', $this->page, $this->size);
        $this->view->totalPage = ceil((new BookLogic())->getArticleListCount($bookId) / $this->size);
        $this->view->page      = $this->page;
        $this->view->title     = '文章管理 ';
        $this->view->chapters  = HelperExtend::arrayToKeyValue((new BookLogic())->getChapter($bookId), 'id', 'chapter_name');
        $this->view->pageLink  = '?page={page}&book_id=' . $bookId;
        $this->view->book_id   = $bookId;
        $this->view->menuflag  = 'novel-book-index';
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

                $data = [
                    'title' => $this->post('title'),
                    'chapter_id' => $this->post('chapter_id'),
                    'article_sort' => $this->post('article_sort'),
                    'content' => $this->post('content'),
                    'book_id' => $bookId,
                ];
                $data['wordnumber'] = mb_strlen(strip_tags($data['content']), mb_detect_encoding($data['content']));
                if (empty($data['content'])) {
                    Redis::getInstance()->setex('alert_error', 3600, '文章内容不能为空');
                    die('<script>window.history.go(-1);</script>');
                }

                $row = (new BookLogic())->saveArticle($data, $articleId);
                if (empty($row)) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/novel/book/article.html?book_id='.$bookId);
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                die('<script>window.history.go(-1);</script>');
            }

        } else {
            $article = [];
            if (!empty($articleId)) {
                $article = (new BookLogic())->getArticleById($articleId, true);
            }

            $this->view->article  = $article;
            $this->view->chapters = HelperExtend::arrayToKeyValue((new BookLogic())->getChapter($bookId), 'id', 'chapter_name');
            $this->view->title    = '编辑文章';
            $this->view->menuflag = 'novel-book-index';
        }
    }

}