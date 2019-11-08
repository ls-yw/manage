<?php

namespace application\modules\novel\controllers;

use application\base\BaseController;
use application\library\ManageException;
use application\logic\novel\BookLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class CategoryController extends BaseController
{
    /**
     * 分类列表
     *
     * @author yls
     */
    public function indexAction()
    {
        $this->view->data      = (new BookLogic())->getCategoryList($this->page, $this->size);
        $this->view->totalPage = ceil((new BookLogic())->getCategoryListCount() / $this->size);
        $this->view->page      = $this->page;
        $this->view->title     = '栏目管理';
        $this->view->pageLink  = '?page={page}';
    }

    public function setAction()
    {
        $id       = (int) $this->get('id', 'int', 0);
        $category = (new BookLogic())->getCategoryById($id);

        if ($this->request->isPost()) {
            $data = [
                'id'          => $id,
                'name'        => $this->post('name'),
                'seo_name'    => $this->post('seo_name'),
                'keyword'     => $this->post('keyword'),
                'description' => $this->post('description'),
                'sort'        => $this->post('sort'),
            ];
            try {

                if (empty($data['name'])) {
                    throw new ManageException('栏目名称不能为空');
                }

                $row = (new BookLogic())->saveCategory($data);
                if (!$row) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/novel/category/index.html');
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                die('<script>window.history.go(-1);</script>');
            }

        } else {

            $this->view->info     = $category;
            $this->view->title    = '设置栏目菜单';
            $this->view->menuflag = 'novel-category-index';
        }
    }
}