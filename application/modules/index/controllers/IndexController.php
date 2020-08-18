<?php

namespace application\modules\index\controllers;

use application\base\BaseController;
use Phalcon\Mvc\View;
use woodlsy\phalcon\library\Redis;

class IndexController extends BaseController
{

    public function indexAction()
    {
        echo 111;
    }

    public function errorAction()
    {
        $message    = $this->get('msg', 'string', '404');
        $url        = $this->get('url', 'string', '/');
        $waitSecond = $this->get('s', 'int', 2);
        $this->view->disableLevel(
            [
                View::LEVEL_LAYOUT => false
            ]
        );
        if ('-1' === $url) {
            $url = 'javascript:history.go(-1)';
        }
        $this->view->message    = $message;
        $this->view->url        = $url;
        $this->view->waitSecond = $waitSecond;
    }

    public function delRedisAction()
    {
        $key = $this->get('key');
        if (!empty($key)) {
            echo Redis::getInstance()->del($key);
        }
    }

}
