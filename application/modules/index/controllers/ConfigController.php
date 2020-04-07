<?php

namespace application\modules\index\controllers;

use application\base\BaseController;
use application\logic\manage\ConfigLogic;

class ConfigController extends BaseController
{
    public function indexAction()
    {
        $this->view->systemConfig = (new ConfigLogic())->getPairs('upload');
        $this->view->title        = '上传设置';
    }

    public function systemAction()
    {
        if ($this->request->isPost()) {
            $data = [
                'url'  => $this->post('url'),
            ];

            (new ConfigLogic())->save('upload', $data);
            return $this->response->redirect('/index/index/error.html?msg=保存成功&url=-1&s=2');
        }
    }
}