<?php

namespace application\library;

class Helper
{
    public static function dealRegular($str)
    {
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
                        "|^(?!http://)(?!mailto:)|i",
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

    public static function writeBookText($categoryId, $bookId, $articleId, $content)
    {
        $body = stripslashes($content);
        $ipath = "./Public/booktext";
        $spath = "/$sort_id";
        $bpath = "/$book_id";
        if(!is_dir($ipath)) mkdir($ipath);
        if(!is_dir($ipath.$spath)) mkdir($ipath.$spath);
        if(!is_dir($ipath.$spath.$bpath)) mkdir($ipath.$spath.$bpath);
        $bookfile = $ipath.$spath.$bpath."/bk_$article_id.inc";
        $body = "<"."?php error_reporting(0); exit();\r\n".$body."\r\n?".">";
        @$fp = fopen($bookfile,'w');
        @flock($fp);
        @fwrite($fp,$body);
        @fclose($fp);


    }
}