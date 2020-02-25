<?php

namespace application\library;

use OSS\Core\OssException;
use OSS\OssClient;
use Phalcon\DI;
use woodlsy\phalcon\library\Log;
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
    /*public function upload(string $fieldName) : ?string
    {
        $data = $this->uploadLocal($fieldName);

        return $this->uploadAliyun($data);
    }*/

    /**
     * 上传到本地
     *
     * @author yls
     * @param string $fieldName 上传文件字段名
     * @return array|mixed
     * @throws Exception
     */
    /*private function uploadLocal(string $fieldName)
    {
        $size     = '1M';         //上传文件最大尺寸
        $path     = $this->ossConfig->uploadPath;    //上传文件保存地址
        $fileName = time();       //上传文件名称，可不填，自动生成唯一文件名
        return (new Upload())->setFieldName($fieldName)->setMaxSize($size)->setUploadPath($path)->upload($fileName);
    }*/

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
        $object = 'book/' . $bookId . '/' . $articleId . '.txt';

        $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);

        $file = $ossClient->putObject($bucket, $object, $content);

        if (isset($file['oss-request-url']) && !empty($file['oss-request-url'])) {
            return $file['oss-request-url'];
        }
        return null;
    }

    /**
     * 获取bucket列表
     *
     * @author woodlsy
     * @return \OSS\Model\BucketInfo[]
     * @throws OssException
     */
    public function getBuckets()
    {
        $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);

        $bucketListInfo = $ossClient->listBuckets();
        $bucketList     = $bucketListInfo->getBucketList();
        return $bucketList;
    }

    /**
     * 获取文件列表
     *
     * @author woodlsy
     * @param string $bucket
     * @param string $prefix
     * @param string $marker
     * @param int    $row
     * @return \OSS\Model\ObjectListInfo
     * @throws OssException
     */
    public function getFiles(string $bucket, string $prefix, string $marker, int $row)
    {
        $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);

        $options        = array(
            'prefix'   => $prefix,
            'marker'   => $marker,
            'max-keys' => $row,
        );
        $listObjectInfo = $ossClient->listObjects($bucket, $options);
        return $listObjectInfo;
    }

    /**
     * 获取文件内容
     *
     * @author woodlsy
     * @param int $bookId
     * @param int $articleId
     * @return string
     * @throws ManageException
     */
    public function getString(int $bookId, int $articleId)
    {
        try{
            // 存储空间名称
            $bucket = "woodlsy-novel";
            // 文件名称
            $object = 'book/'.$bookId . '/'.$articleId.'.txt';

            $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);

            $content = $ossClient->getObject($bucket, $object);
            return $content;
        } catch(OssException $e) {
            Log::write('content', $e->getMessage(), 'aliyun');
            throw new ManageException('获取小说内容失败');
        }
    }

    /**
     * 删除文件
     *
     * @author woodlsy
     * @param int $bookId
     * @param int $articleId
     * @throws OssException
     */
    public function delFile(int $bookId, int $articleId)
    {
        // 存储空间名称
        $bucket = "woodlsy-novel";
        // 文件名称
        $object = 'book/'.$bookId . '/'.$articleId.'.txt';

        $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);
        $ossClient->deleteObject($bucket, $object);
    }

    /**
     * 批量删除文章
     *
     * @author woodlsy
     * @param int   $bookId
     * @param array $articleIdArray
     * @throws OssException
     */
    public function delFiles(int $bookId, array $articleIdArray)
    {
        // 存储空间名称
        $bucket = "woodlsy-novel";
        // 文件名称
        $objects = [];
        foreach ($articleIdArray as $val) {
            $objects[] = 'book/'.$bookId . '/'.$val.'.txt';
        }

        $ossClient = new OssClient($this->ossConfig->accessKeyId, $this->ossConfig->accessKeySecret, $this->ossConfig->endpoint);
        $ossClient->deleteObjects($bucket, $objects);
    }
}