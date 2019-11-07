<?php
return [
    'modules'    => [
        'index',
        'novel',
    ],
    'db'         => require_once APP_PATH . '/config/database.php',
    'csrf'       => false,
    'uploadUrl'  => 'http://dev.img.woodlsy.com/upload/img?project=',
    'uploadPath' => 'http://dev.img.woodlsy.com',
    'aliyun'     => require_once APP_PATH . '/config/aliyun.php',
];