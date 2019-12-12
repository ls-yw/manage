<?php

namespace application\modules\novel\controllers;

use application\base\BaseController;
use application\logic\novel\ConfigLogic;

class ConfigController extends BaseController
{
    public function indexAction()
    {
        $this->view->systemConfig = (new ConfigLogic())->getPairs('system');
        $this->view->title        = '系统设置';
    }

    public function systemAction()
    {
        if ($this->request->isPost()) {
            $data = [
                'host'                 => $this->post('host'),
                'host_name'            => $this->post('host_name'),
                'host_seo_name'        => $this->post('host_seo_name'),
                'host_seo_keywords'    => $this->post('host_seo_keywords'),
                'host_seo_description' => $this->post('host_seo_description'),
                'powerby'              => $this->post('powerby'),
                'record'               => $this->post('record'),
                'notice'               => $this->post('notice'),
            ];

            (new ConfigLogic())->save('system', $data);
            return $this->response->redirect('/index/index/error.html?msg=保存成功&url=-1&s=2');
        }
    }
}