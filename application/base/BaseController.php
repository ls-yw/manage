<?php

namespace application\base;

use application\library\HelperExtend;
use application\logic\manage\ConfigLogic;
use Exception;
use woodlsy\phalcon\basic\BasicController;
use woodlsy\phalcon\library\Redis;

class BaseController extends BasicController
{
    public    $token = null;
    public    $admin = null;
    protected $page  = 1;

    protected $size = 20;

    protected $mConfig = [];

    /**
     * 初始化
     *
     * @author yls
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->page = (int) $this->get('page', 'int', 1);
        $this->size = (int) $this->get('size', 'int', 20);

        $this->token = $this->cookies->get('token')->getValue();
        $this->setAdmin();

        $this->checkLogin();

        $this->getConfig();

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
            $this->admin = HelperExtend::jsonDecode($admin);
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
            [
                'title' => '博客系统',
                'icon' => 'fa fa-book',
                'link' => '#',
                'children' => [
                    ['title' => '文章分类', 'link' => '/blog/category/index.html', 'flag' => 'blog-category-index'],
                ],
            ],
            ['title' => '小说系统', 'icon' => 'fa fa-book', 'link' => '#', 'children' => [
                ['title' => '内容管理', 'link' => '#', 'children' => [
                    ['title' => '栏目管理', 'link' => '/novel/category/index.html', 'flag' => 'novel-category-index'],
                    ['title' => '小说管理', 'link' => '/novel/book/index.html', 'flag' => 'novel-book-index'],
                ]],
                ['title' => '采集管理', 'link' => '#', 'children' => [
                    ['title' => '采集节点', 'link' => '/novel/collect/index.html', 'flag' => 'novel-collect-index'],
                    ['title' => '采集小说', 'link' => '/novel/collect/bookList.html', 'flag' => 'novel-collect-bookList'],
                ]],
                [
                    'title' => '会员管理', 'link' => '#', 'children' => [
                    ['title' => '会员列表', 'link' => '/novel/member/index.html', 'flag' => 'novel-member-index'],
                    ['title' => '收录申请', 'link' => '/novel/member/applyBook.html', 'flag' => 'novel-member-applyBook']
                ],
                ],
                ['title' => '系统设置', 'link' => '/novel/config/index.html', 'flag' => 'novel-config-index'],
            ]],
            [
                'title'    => 'OSS',
                'icon'     => 'fa fa-folder',
                'link'     => '#',
                'children' => [
                    ['title' => '文件管理', 'link' => '/aliyun/oss/index.html', 'flag' => 'aliyun-oss-index'],
                ]
            ],
            [
                'title' => '系统设置',
                'icon'  => 'fa fa-gear',
                'link'  => '/index/config/index.html',
                'flag'  => 'index-config-index'
            ],
        ];
    }

    /**
     * 错误返回上一页
     *
     * @author woodlsy
     * @param string $msg
     */
    protected function breakError(string $msg)
    {
        Redis::getInstance()->setex('alert_error', 3600, $msg);
        die('<script>window.history.go(-1);</script>');
    }

    /**
     * 获取配置
     *
     * @author woodlsy
     */
    public function getConfig()
    {
        $this->mConfig = (new ConfigLogic())->getAllPairs();
    }
}