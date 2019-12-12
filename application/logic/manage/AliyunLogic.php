<?php

namespace application\logic\manage;

use application\library\AliyunOss;
use application\library\HelperExtend;
use woodlsy\phalcon\library\Redis;

class AliyunLogic
{
    /**
     * 获取oss bucket
     *
     * @author woodlsy
     * @return array|object|\OSS\Model\BucketInfo[]
     * @throws \OSS\Core\OssException
     */
    public function getOSSBuckets()
    {
        $bucketKey = 'oss_buckets';
        if (!Redis::getInstance()->exists($bucketKey)) {
            $buckets = (new AliyunOss())->getBuckets();
            $arr     = [];
            if (!empty($buckets)) {
                foreach ($buckets as $val) {
                    $arr[$val->getName()] = $val->getLocation() . ':' . $val->getName();
                }
            }
            Redis::getInstance()->setex($bucketKey, 600, HelperExtend::jsonEncode($arr));
        }
        $buckets = HelperExtend::jsonDecode(Redis::getInstance()->get($bucketKey));
        return $buckets;
    }

    /**
     * 获取oss文件列表
     *
     * @author woodlsy
     * @param string $bucket
     * @param string $prefix
     * @param string $marker
     * @param int    $row
     * @return \OSS\Model\ObjectListInfo
     * @throws \OSS\Core\OssException
     */
    public function getOSSFiles(string $bucket, string $prefix, string $marker, int $row)
    {
        return (new AliyunOss())->getFiles($bucket, $prefix, $marker, $row);
    }
}