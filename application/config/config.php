<?php
return [
    'modules'       => [
        'index',
        'novel',
        'aliyun',
    ],
    'db'            => require_once APP_PATH . '/config/database.php',
    'csrf'          => false,
    'limit_request' => false,
    'uploadUrl'     => 'http://dev.img.woodlsy.com/upload/img?project=',
    'uploadPath'    => 'http://dev.img.woodlsy.com',
    'aliyun'        => require_once APP_PATH . '/config/aliyun.php',
];