<?php

namespace application\modules\blog\controllers;

use application\base\BaseController;
use application\library\ManageException;
use application\logic\blog\ArticleLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class CategoryController extends BaseController
{
    private $crumbs = [['name' => '博客系统']];

    public function indexAction()
    {
        $category       = (new ArticleLogic())->getCategory();
        $parentCategory = (new ArticleLogic())->getCategoryPairs(0);

        $this->view->title    = '分类列表';
        $this->view->data     = $category;
        $this->view->category = $parentCategory;
        $this->view->crumbs   = $this->crumbs;
    }

    public function setCategoryAction()
    {
        if ($this->request->isPost()) {
            try {
                $id        = $this->post('id');
                $name      = $this->post('name');
                $pid       = $this->post('pid');
                $isDeleted = (int) $this->post('is_deleted');

                if (empty($name)) {
                    throw new ManageException('分类名称不能为空');
                }

                $data = [
                    'id'         => $id,
                    'name'       => $name,
                    'pid'        => $pid,
                    'is_deleted' => $isDeleted,
                ];

                $row = (new ArticleLogic())->saveCategory($data);
                if (!$row) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/blog/category/index.html');
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                return $this->response->redirect($this->request->getHTTPReferer());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                return $this->response->redirect($this->request->getHTTPReferer());
            }
        } else {
            $id = (int) $this->get('id');

            $parentCategory       = (new ArticleLogic())->getCategoryPairs(0);
            $this->view->menuflag = 'blog-category-index';
            $this->view->title    = '设置分类';
            $this->view->category = $parentCategory;
            $this->view->data     = empty($id) ? '' : (new ArticleLogic())->getCategoryById($id);
            $this->view->crumbs   = $this->crumbs;
        }
    }
}