<?php

namespace application\modules\novel;

use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;

class Module implements ModuleDefinitionInterface
{
    /**
     * 注册自定义加载器
     */
    public function registerAutoloaders(DiInterface $di = null){}

    /**
     * 注册自定义服务
     */
    public function registerServices(DiInterface $di)
    {
        // Registering a dispatcher
        $di->set(
            "dispatcher",
            function () {
                $dispatcher = new Dispatcher();

                $dispatcher->setDefaultNamespace("application\\modules\\novel\\controllers");

                return $dispatcher;
            }
        );
        $di->set(
            "view",
            function () {
                $view = new View();

                $view->setViewsDir("../application/modules/novel/views/");
                return $view;
            }
        );
    }
}