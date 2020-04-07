<?php
namespace application\modules\index\controllers;

use application\base\BaseController;
use application\library\ManageException;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\upload\Upload;

class UploadController extends BaseController
{
    public function imgAction()
    {
        try {
            $type = $this->get('type');
            if (empty($type)) {
                throw new ManageException('项目错误');
            }
            $url = $this->mConfig['upload']['url'].'/upload/img?project='.$type;
            $data      = (new Upload())->setMaxSize('1M')->setServerUrl($url)->upload();
            return $this->ajaxReturn(0, "ok", $this->mConfig['upload']['url'].'/'.$data['url']);
        } catch (ManageException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(1, "系统错误");
        }
    }
}