<?php
/**
 * aliyun
 * [
 * 'oss' => [
 * 'accessKeyId'     => '',
 * 'accessKeySecret' => '',
 * 'endpoint'        => 'http://oss-cn-hangzhou.aliyuncs.com',
 * 'uploadPath'      => '/data/upload/' . APP_NAME,
 * 'maxSize'         => '1M',
 * ],
 * ];
 * db
 * [
 * 'master' => [
 * 'adapter'  => 'mysql',
 * 'host'     => '127.0.0.1',
 * 'username' => 'root',
 * 'password' => '',
 * 'port'     => '3306',
 * 'dbname'   => 'user',
 * 'prefix'   => 'pr_',
 * 'charset'  => 'utf8',
 * ],
 * ];
 */
return [
    'modules'       => [
        'index',
        'novel',
        'aliyun',
        'blog',
    ],
    'db'            => require_once APP_PATH . '/config/database.php',
    'csrf'          => false,
    'limit_request' => false,
    'suffix'        => 'html',
    'aliyun'        => require_once APP_PATH . '/config/aliyun.php',
];