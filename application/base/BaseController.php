<?php

namespace application\base;

use Exception;
use woodlsy\phalcon\basic\BasicController;
use woodlsy\phalcon\library\Helper;
use woodlsy\phalcon\library\Redis;

class BaseController extends BasicController
{
    public    $token = null;
    public    $admin = null;
    protected $page  = 1;

    protected $size = 20;

    /**
     * 初始化
     *
     * @author yls
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->page = (int)$this->get('page', 'int', 1);
        $this->size = (int)$this->get('size', 'int', 20);

        $this->token = $this->cookies->get('token')->getValue();
        $this->setAdmin();

        $this->checkLogin();

        $menus                = $this->getMenu();
        $this->view->menus    = $menus;
        $this->view->menuflag = $this->router->getModuleName() . '-' . $this->router->getControllerName() . '-' . $this->router->getActionName();

        $alertError = Redis::getInstance()->get('alert_error');
        if (!empty($alertError)) {
            $this->view->alertError = $alertError;
            Redis::getInstance()->del('alert_error');
        }
        $alertSuccess = Redis::getInstance()->get('alert_success');
        if (!empty($alertSuccess)) {
            $this->view->alertSuccess = $alertSuccess;
            Redis::getInstance()->del('alert_success');
        }
    }

    /**
     * 获取登录信息
     *
     * @author yls
     */
    private function setAdmin()
    {
        if (!$this->token) {
            return;
        }
        if (Redis::getInstance()->exists($this->token)) {
            $admin       = Redis::getInstance()->get($this->token);
            $this->admin = Helper::jsonDecode($admin);
        }
    }

    /**
     * 检测是否登录
     *
     * @author yls
     */
    private function checkLogin() : void
    {
        if ('login' === $this->router->getControllerName()) {
            return;
        }
        if (empty($this->admin)) {
            $this->response->redirect('index/login/index.html');
        }
    }

    private function getMenu()
    {
        return [
            ['title' => '工作台', 'icon' => 'fa fa-dashboard', 'link' => '/index/index/index.html', 'flag' => 'workbench'],
            ['title' => '小说系统', 'icon' => 'fa fa-book', 'link' => '#', 'children' => [
                ['title' => '内容管理', 'link' => '#', 'children' => [
                    ['title' => '栏目管理', 'link' => '/novel/category/index.html', 'flag' => 'novel-category-index'],
                ]],
                ['title' => '采集管理', 'link' => '#', 'children' => [
                    ['title' => '采集节点', 'link' => '/novel/collect/index.html', 'flag' => 'novel-collect-index'],
                    ['title' => '采集小说', 'link' => '/novel/collect/bookList.html', 'flag' => 'novel-collect-bookList'],
                ]],
                ['title' => '系统设置', 'link' => '/index/article/index.html', 'flag' => 'article'],
            ]]
        ];
    }
}