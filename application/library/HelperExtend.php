<?php

namespace application\library;

use woodlsy\phalcon\library\Helper;

class HelperExtend extends Helper
{
    public static function dealRegular($str)
    {
        if (empty($str)) {
            return $str;
        }
        $str = addslashes($str);
        $str = preg_replace('/\//', '\/', $str);
        $str = preg_replace('/\(/', '\(', $str);
        $str = preg_replace('/\)/', '\)', $str);
        $str = preg_replace('/\./', '\.', $str);
        $str = preg_replace('/\?/', '\?', $str);
        $str = preg_replace('/(\r\n)|(\n)/', '[\r\n|\n]*', $str);
        $str = preg_replace('/@/', '&nbsp;', $str);
        if (strpos($str, '!!!!')) {
            $str    = explode('!!!!', $str);
            $str[3] = '([^<>]*)';
        } elseif (strpos($str, '$$$$')) {
            $str    = explode('$$$$', $str);
            $str[3] = '(\d*)';
        } elseif (strpos($str, '~~~~')) {
            $str    = explode('~~~~', $str);
            $str[3] = '([^<>\'\"]*)';
        } elseif (strpos($str, '^^^^')) {
            $str    = explode('^^^^', $str);
            $str[3] = '([^<>\d]*)';
        } elseif (strpos($str, '****')) {
            $str    = explode('****', $str);
            $str[3] = '([\w\W]*)';
        }
        for ($i = 0; $i <= 1; $i++) {
            $str[$i] = preg_replace('/(?<!\])\*/', '[\s\S]*', $str[$i]);
            $str[$i] = preg_replace('/(?<!\<)!/', '[^<>]*', $str[$i]);
            $str[$i] = preg_replace('/\$/', '\d*', $str[$i]);
            $str[$i] = preg_replace('/~/', '[^<>\'\"]*', $str[$i]);
            $str[$i] = preg_replace('/(?<!\[)\^/', '[^<>\d]*', $str[$i]);
            //$str[$i] = preg_replace('/\s*/', '\s*', $str[$i]);
        }
        return $str;
    }

    /**
     * 补全链接地址
     *
     * @author yls
     * @param $links
     * @param $URI
     * @return string|string[]|null
     */
    public static function expandlinks($links, $URI, $host)
    {

        preg_match("/^[^\?]+/", $URI, $match);

        $match      = preg_replace("|/[^\/\.]+\.[^\/\.]+$|", "", $match[0]);
        $match      = preg_replace("|/$|", "", $match);
        $match_part = parse_url($match);
        $match_root =
            $match_part["scheme"] . "://" . $match_part["host"];

        $search = array("|^http://" . preg_quote($host) . "|i",
                        "|^(\/)|i",
                        "|^(?!http://)(?!https://)(?!mailto:)|i",
                        "|/\./|",
                        "|/[^\/]+/\.\./|"
        );

        $replace = array("",
                         $match_root . "/",
                         $match . "/",
                         "/",
                         "/"
        );

        $expandedLinks = preg_replace($search, $replace, $links);

        return $expandedLinks;
    }

    /**
     * 处理htmlspecialchars_decode后的文章内容
     *
     * @author yls
     * @param string $content 未处理的文章内容
     * @param string $type
     * @return string|string[]|null 已处理的文章内容
     */
    public static function doWriteContent(string $content, $type = 'add')
    {
        $content = stripslashes($content);
        if ($type == 'add') {
            $content = preg_replace("/[\s]?style[\s]?=[\s]?\"[^\"]*\"/i", '', $content);
        }
        $content = preg_replace("/<\/p>/i", "</p>\r\n", $content);
        $content = addslashes($content);
        return $content;
    }

    /**
     * 保存到本地
     *
     * @author woodlsy
     * @param $categoryId
     * @param $bookId
     * @param $articleId
     * @param $content
     */
    public static function writeBookText($categoryId, $bookId, $articleId, $content)
    {
        $ipath = APP_PATH."/../public/booktext";
        $spath = "/$categoryId";
        $bpath = "/$bookId";
        if(!is_dir($ipath)) mkdir($ipath);
        if(!is_dir($ipath.$spath)) mkdir($ipath.$spath);
        if(!is_dir($ipath.$spath.$bpath)) mkdir($ipath.$spath.$bpath);
        $bookfile = $ipath.$spath.$bpath."/$articleId.inc";
        $fp = fopen($bookfile,'w');
        flock($fp, LOCK_EX);
        fwrite($fp,$content);
        fclose($fp);
    }

    /**
     * 读取小说内容
     *
     * @author woodlsy
     * @param $categoryId
     * @param $bookId
     * @param $articleId
     * @return string
     */
    public static function getBookText($categoryId,$bookId,$articleId)
    {
        $bookfile = APP_PATH."/../public/booktext/".$categoryId.'/'.$bookId."/{$articleId}.inc";
        if(!file_exists($bookfile))
        {
            return '';
        }
        else
        {
            return file_get_contents($bookfile);
        }
    }

    /**
     * 删除文章内容inc文件
     *
     * @author woodlsy
     * @param $categoryId
     * @param $bookId
     * @param $articleId
     * @return bool
     */
    public static function delBookText($categoryId,$bookId,$articleId)
    {
        $path = APP_PATH."/../public/booktext/".$categoryId.'/'.$bookId."/{$articleId}.inc";
        if(!file_exists($path))return false;
        if(unlink($path)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 删除小说文件夹
     *
     * @author woodlsy
     * @param $categoryId
     * @param $bookId
     * @return bool
     */
    public static function delBookDir($categoryId,$bookId)
    {
        $path = APP_PATH."/../public/booktext/".$categoryId.'/'.$bookId;
        if (!is_dir($path)) {
            return true;
        }

        return self::delDir($path);
    }

    /**
     * 删除文件夹
     *
     * @author woodlsy
     * @param $dirname
     * @return bool
     */
    public static function delDir($dirname)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $handle = opendir($dirname); //打开目录
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                //排除"."和"."
                $dir = $dirname .'/' . $file;
                is_dir($dir) ? self::delDir($dir) : unlink($dir);
            }
        }
        closedir($handle);
        $result = rmdir($dirname) ? true : false;
        return $result;
    }
}