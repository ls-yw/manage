<?php

namespace application\modules\index\controllers;

use application\base\BaseController;
use application\library\ManageException;
use application\logic\manage\AdminLogic;
use Exception;
use Phalcon\Mvc\View;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class LoginController extends BaseController
{

    public function indexAction()
    {
//        echo crypt(md5('y1030424'), 'Hi29b63F');exit;
//        $this->view->alertError = Redis::getInstance()->get('alert_error');
//        Redis::getInstance()->del('alert_error');

        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    /**
     * 登录
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function loginAction()
    {
        try {
            $username = $this->post('username');
            $password = $this->post('password');
            $remember = (int) $this->post('remember');

            if (strlen($username) < 6 || strlen($password) < 6) {
                Redis::getInstance()->setex('alert_error', 3600, '帐号或密码错误');
                return $this->response->redirect('/index/login/index.html');
            }

            $token = (new AdminLogic())->login($username, $password, $remember);
            $this->cookies->set('token', $token, time() + Redis::getInstance()->ttl($token));
            return $this->response->redirect('/index/index/index.html');
        } catch (ManageException $e) {
            Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
            return $this->response->redirect('/index/login/index.html');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            Redis::getInstance()->setex('alert_error', 3600, '系统错误');
            return $this->response->redirect('/index/login/index.html');
        }
    }

}
