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

    public function editorUploadAction()
    {
        try {
            $type = $this->get('type');
            if (empty($type)) {
                throw new ManageException('项目错误');
            }
            $url = $this->mConfig['upload']['url'].'/upload/img?project='.$type;
            $data      = (new Upload())->setMaxSize('1M')->setFieldName('upload')->setServerUrl($url)->upload();

            return $this->ajaxReturn(0, "ok", null, ['uploaded' => 1, 'fileName' => $data['name'], 'url' => $this->mConfig['upload']['url'].'/'.$data['url']]);
        } catch (ManageException $e) {
            return $this->ajaxReturn(1, $e->getMessage(), null, ['uploaded' => 0, 'error' => ['message' => $e->getMessage()]]);
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(1, "系统错误", null, ['uploaded' => 0, 'error' => ['message' => "系统错误"]]);
        }
    }
}