<?php
namespace application\logic\manage;

use application\library\ManageException;
use application\models\manage\Admin;
use woodlsy\phalcon\library\HelperExtend;
use woodlsy\phalcon\library\Redis;

class AdminLogic
{
    /**
     * 管理员登录
     *
     * @author yls
     * @param string $username
     * @param string $password
     * @param int    $remember
     * @return string|null
     * @throws ManageException
     */
    public function login(string $username, string $password, int $remember)
    {
        $admin = (new Admin())->getOne(['username' => $username]);
        if (empty($admin)) {
            throw new ManageException('帐号不存在');
        }

        $token = crypt(md5($password), $admin['salt']);
        if ($token !== $admin['password']) {
            throw new ManageException('密码错误');
        }

        Redis::getInstance()->setex($token, ($remember ? 86400 * 30 : 86400), HelperExtend::jsonEncode($admin));
        return $token;
    }
}