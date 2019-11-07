<?php

namespace application\library;

use OSS\Core\OssException;
use OSS\OssClient;
use Phalcon\DI;
use woodlsy\upload\Upload;
use Exception;

/**
 * AliyunOss 上传类
 *
 * @author yls
 * @package library
 */
class AliyunOss
{
    /**
     * @var object|null oss配置
     */
    private $ossConfig = null;

    /**
     * 初始化
     *
     * @author yls
     */
    public function __construct()
    {
        $this->ossConfig = DI::getDefault()->get('config')->aliyun->oss;
    }

    /**
     * 上传入口
     *
     * @author yls
     * @param string $fieldName 上传文件字段名
     * @return string|null 成功时返回图片url，失败返回null
     * @throws Exception
     * @throws OssException
     */
    public function upload(string $fieldName) : ?string
    {
        $data = $this->uploadLocal($fieldName);

        return $this->uploadAliyun($data);
    }

    /**
     * 上传到本地
     *
     * @author yls
     * @param string $fieldName 上传文件字段名
     * @return array|mixed
     * @throws Exception
     */
    private function uploadLocal(string $fieldName)
    {
        $size     = '1M';         //上传文件最大尺寸
        $path     = $this->ossConfig->uploadPath;    //上传文件保存地址
        $fileName = time();       //上传文件名称，可不填，自动生成唯一文件名
        return (new Upload())->setFieldName($fieldName)->setMaxSize($size)->setUploadPath($path)->upload($fileName);
    }

    /**
     * 阿里云保存字符串
     *
     * @author yls
     * @param int    $bookId
     * @param int    $articleId
     * @param string $content
     * @return string|null
     * @throws OssException
     */
    public function saveString(int $bookId, int $articleId, string $content) : ?string
    {
        // 存储空间名称
        $bucket = "woodlsy-novel";
        // 文件名称
        $object = 'book/'.$bookId . '/'.$articleId.'.txt';

        $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);

        $file = $ossClient->putObject($bucket, $object, $content);

        if (isset($file['oss-request-url']) && !empty($file['oss-request-url'])) {
            return $file['oss-request-url'];
        }
        return null;
    }
}