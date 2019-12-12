<?php

namespace application\modules\aliyun\controllers;

use application\base\BaseController;
use application\library\ManageException;
use application\logic\manage\AliyunLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class OssController extends BaseController
{
    public function indexAction()
    {
        try {

            $bucket  = $this->get('bucket');
            $prefix  = $this->get('path', 'string', '');
            $marker  = $this->get('offset', 'string', '');
            $page    = (int) $this->get('page', 'int', 1);
            $buckets = (new AliyunLogic())->getOSSBuckets();

            if (empty($bucket) && !empty($buckets)) {
                $bucket = key($buckets);
            }

            $files = (new AliyunLogic())->getOSSFiles($bucket, $prefix, $marker, 20);

            $backPrefix = '';
            if (!empty($prefix)) {
                $backPrefix = substr($prefix, 0, -1);
                $backPrefix = explode('/', $backPrefix);
                unset($backPrefix[(count($backPrefix) - 1)]);
            }

            $this->view->dirData    = $files->getPrefixList();
            $this->view->fileData   = $files->getObjectList();
            $this->view->buckets    = $buckets;
            $this->view->bucket     = $bucket;
            $this->view->backPrefix = empty($backPrefix) ? '' : implode('/', $backPrefix) . '/';
            $this->view->prefix     = $prefix;
            $this->view->marker     = $files->getNextMarker();
            $this->view->isNext     = $files->getIsTruncated();
            $this->view->page       = $page;
            $this->view->title      = 'oss';

        } catch (ManageException $e) {
            Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            Redis::getInstance()->setex('alert_error', 3600, '系统错误');
        }
    }
}