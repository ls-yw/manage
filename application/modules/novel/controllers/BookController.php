<?php

namespace application\modules\novel\controllers;

use application\base\BaseController;
use application\logic\novel\BookLogic;
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

}