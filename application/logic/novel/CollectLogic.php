<?php

namespace application\logic\novel;

use application\library\AliyunOss;
use application\library\HelperExtend;
use application\library\ManageException;
use application\models\novel\Article;
use application\models\novel\Book;
use application\models\novel\Chapter;
use application\models\novel\Collect;
use application\models\novel\CollectFrom;
use woodlsy\httpClient\HttpCurl;
use woodlsy\phalcon\library\Log;

class CollectLogic
{
    private $collectModel;

    public function __construct()
    {
        $this->collectModel = (new Collect());
    }

    /**
     * 获取采集目标站列表
     *
     * @author yls
     * @param $page
     * @param $row
     * @return array|bool
     */
    public function getCollectList($page, $row)
    {
        $offset = ($page - 1) * $row;
        $list   = $this->collectModel->getList(['is_deleted' => 0], 'id desc', $offset, $row);
        return $list;
    }

    /**
     * 获取采集目标站列表条数
     *
     * @author yls
     * @return array|int
     */
    public function getCollectListCount()
    {
        $count = $this->collectModel->getCount(['is_deleted' => 0]);
        return $count;
    }

    /**
     * 删除采集节点
     *
     * @author yls
     * @param int $id
     * @return bool
     * @throws ManageException
     */
    public function delCollect(int $id)
    {
        $info = $this->collectModel->getById($id);
        if (!$info) {
            throw new ManageException('找不到数据');
        }
        $res = $this->collectModel->deleteData(['id' => $id]);
        if (!$res) {
            throw new ManageException('删除失败');
        }
        return true;
    }

    /**
     * 根据ID查找数据
     *
     * @author yls
     * @param int $id
     * @return array|mixed
     */
    public function getById(int $id)
    {
        return $this->collectModel->getById($id);
    }

    /**
     * 保存节点数据
     *
     * @author yls
     * @param array $data
     * @return bool|int
     */
    public function save(array $data)
    {
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if (empty($id)) {
            return $this->collectModel->insertData($data);
        } else {
            return $this->collectModel->updateData($data, ['id' => $id]);
        }
    }

    /**
     * 抓取小说基本信息
     *
     * @author yls
     * @param int   $targetId
     * @param array $collect
     * @return array
     * @throws ManageException
     * @throws \woodlsy\httpClient\HttpClientException
     */
    public function startCollect(int $targetId, array $collect)
    {
        if (empty($targetId)) {
            throw new ManageException('来源文章序号不能为空');
        }

        $url  = $this->getUrl($collect, $targetId, 'collect_urlarticle');
        $html = (new HttpCurl())->setUrl($url)->setHeader('Referer: ' . $collect['collect_host'])->get();
        $html = iconv($collect['collect_iconv'], 'UTF-8', $html);
        if (empty($html)) {
            throw new ManageException('采集错误URL：' . $url);
        }

        $indexlink    = $this->getUrl($collect, $targetId, 'collect_indexlink');
        $book_name    = HelperExtend::dealRegular($collect['collect_articletitle']);
        $book_author  = HelperExtend::dealRegular($collect['collect_author']);
        $book_sort    = HelperExtend::dealRegular($collect['collect_sort']);
        $book_keyword = HelperExtend::dealRegular($collect['collect_keyword']);
        $book_intro   = HelperExtend::dealRegular($collect['collect_intro']);
        $book_img     = HelperExtend::dealRegular($collect['collect_articleimage']);
        $indexlink    = HelperExtend::dealRegular($indexlink);
        $fullarticle  = HelperExtend::dealRegular($collect['collect_fullarticle']);

        $book = array();
        //获取小说名称
        preg_match('/' . $book_name[0] . $book_name[3] . $book_name[1] . '/i', $html, $result);
        $book['book_name'] = $result['1'];
        $result            = '';
        //获取作者
        preg_match('/' . $book_author[0] . $book_author[3] . $book_author[1] . '/i', $html, $result);
        $book['book_author'] = $result[1];
        $result              = '';
        //获取分类名称
        preg_match('/' . $book_sort[0] . $book_sort[3] . $book_sort[1] . '/i', $html, $result);
        $book['book_sort'] = $result[1];
        $result            = '';
        //获取关键字
        if (!empty($book_keyword)) {
            preg_match('/' . $book_keyword[0] . $book_keyword[3] . $book_keyword[1] . '/i', $html, $result);
            $book['book_keyword'] = $result[1];
        } else {
            $book['book_keyword'] = '';
        }
        $result               = '';
        //获取简介
        preg_match('/' . $book_intro[0] . $book_intro[3] . $book_intro[1] . '/i', $html, $result);
        $book['book_intro'] = $result[1];
        $result             = '';
        //获取小说封面
        preg_match('/' . $book_img[0] . $book_img[3] . $book_img[1] . '/i', $html, $result);
        ($result[1] == $collect['collect_filterimage']) ? $book['book_img'] = '' : $book['book_img'] = $result[1];
        if (!empty($book['book_img'])) {
            if (!preg_match('/^(http|https):\/\//i', $book['book_img'])) {
                $book['book_img'] = $collect['collect_host'] . $book['book_img'];
            }
        }
        $result = '';
        //获取<{indexlink}>
        preg_match('/' . $indexlink[0] . $indexlink[3] . $indexlink[1] . '/i', $html, $result);
        $book['indexlink'] = $result[1];
        $result            = '';
        //获取小说连载状态
        $book['book_state'] = preg_match('/' . $fullarticle . '/i', $html);

        return $book;
    }

    /**
     * 采集文章目录
     *
     * @author yls
     * @param $collectId
     * @param $targetId
     * @param $indexlink
     * @param $bookId
     * @return array
     * @throws ManageException
     * @throws \woodlsy\httpClient\HttpClientException
     */
    public function article($collectId, $targetId, $indexlink, $bookId)
    {
        $collect  = $this->getById($collectId);
        $urlindex = $this->getUrl($collect, $targetId, 'collect_urlindex');
        $urlindex = preg_replace('/<{indexlink}>/i', $indexlink, $urlindex);

        $html      = (new HttpCurl())->setUrl($urlindex)->setHeader('Referer: ' . $collect['collect_host'])->get();
        $html      = iconv($collect['collect_iconv'], 'UTF-8', $html);
        $chapter   = HelperExtend::dealRegular($collect['collect_chapter']);
        $chapterid = HelperExtend::dealRegular($collect['collect_chapterid']);

        preg_match_all('/' . $chapter[0] . $chapter[3] . $chapter[1] . '/i', $html, $result);
        preg_match_all('/' . $chapterid[0] . $chapterid[3] . $chapterid[1] . '/i', $html, $resultId);

        //获取链接地址
        $result[2] = preg_replace('/(.*)(href=\")([^\"]+)(\"[\s\S]*)/i', '$3', $result[0]);

        //补全链接地址    $link[0]为超链接地址 $link[1]为章节序号 $link[2]为章节名称
        $link[0] = HelperExtend::expandlinks($result[2], $urlindex, $collect['collect_host']);
        $link[1] = $resultId[1];
        $link[2] = $result[1];

        $data = [];
        foreach ($link[0] as $key => $value) {
            $data[$key]['from_book_id']    = $bookId;
            $data[$key]['from_collect_id'] = $collectId;
            $data[$key]['from_article_id'] = $link[1][$key];
            $data[$key]['from_url']        = $value;
            $data[$key]['from_sort']       = ($key + 1);
            $data[$key]['from_title']      = $link[2][$key];
            $data[$key]['from_state']      = 0;
        }
        if (!is_array($data['0'])) {
            $msg = '请确认链接采集规则正确，或目标网站访问正常 <a href="' . $urlindex . ' target="_blank">' . $urlindex . '</a>';
            throw new ManageException($msg);
        }

        return $data;
    }

    /**
     * 采集文章
     *
     * @author yls
     * @param $bookId
     * @param $collectId
     * @param $chapterId
     * @param $categoryId
     * @param $fromSort
     * @return array
     * @throws ManageException
     * @throws \OSS\Core\OssException
     * @throws \woodlsy\httpClient\HttpClientException
     */
    public function collectArticle($bookId, $collectId, $chapterId, $categoryId, $fromSort)
    {
        $collect = $this->getById($collectId);

        $article = array();

        $from = (new CollectFrom())->getOne(['from_book_id' => $bookId, 'from_sort' => ['>', $fromSort], 'from_state' => 0], null, 'from_sort asc');

        $html         = (new HttpCurl())->setUrl($from['from_url'])->setHeader('Referer: ' . $collect['collect_host'])->get();
        $html         = iconv($collect['collect_iconv'], 'UTF-8', $html);
        $content_preg = HelperExtend::dealRegular($collect['collect_content']);
        preg_match_all('/' . $content_preg[0] . $content_preg[3] . $content_preg[1] . '/i', $html, $match);
        if ($match[1] == '') {
            $msg = '<span class="red">' . $from['from_title'] . '（文章内容获取失败：<a href="' . $from['from_url'] . '" target="_blank">' . $from['from_url'] . '</a>）  </span>';
            throw new ManageException($msg);
        }
        $content = $this->pregContent($collect['collect_contentfilter'], $collect['collect_contentreplace'], $match[1][0]);

        $content_code = mb_detect_encoding($content);
        if (mb_detect_encoding($content) != 'UTF-8') {
            $content = iconv($content_code, 'UTF-8', $content);
        }
        $content = htmlspecialchars_decode($content);

        //文章内容字数
        $article['wordnumber'] = mb_strlen(strip_tags($content), $content_code);

        $msg = '错误';
        if ($article['wordnumber'] > 200) {
            $article['title']        = $from['from_title'];
            $article['book_id']      = $from['from_book_id'];
            $article['chapter_id']   = $chapterId;
            $article['article_sort'] = $from['from_sort'];
            $article_id              = (new Article())->insertData($article);
            if ($article_id) {

                // 先保存到本地，确认没问题，再从后台上传到aliyun
                HelperExtend::writeBookText($categoryId, $bookId, (int)$article_id, HelperExtend::doWriteContent($content));
//                try {
//                    $bookUrl = (new AliyunOss())->saveString((int)$article['book_id'], (int)$article_id, HelperExtend::doWriteContent($content));
//                } catch (\Exception $e) {
//                    (new Article())->delData(['id' => $article_id]);
//                    throw new ManageException('上传阿里云失败：'.$e->getMessage());
//                }

                $addarticle_error = '';
                $row = (new Chapter())->updateData(['chapter_articlenum' => ['+', 1]], ['id' => $article['chapter_id']]);
                if (empty($row)) {
                    $addarticle_error = '（章节文章数更新失败）';
                }
                $row = (new Book())->updateData(['book_articlenum' => ['+', 1], 'book_wordsnumber' => ['+', $article['wordnumber']]], ['id' => $article['book_id']]);
                if (empty($row)) {
                    $addarticle_error .= '（小说总文章数更新失败）（小说总字数更新失败）';
                }

                $msg         = $from['from_title'] . $addarticle_error . ' ';
                (new CollectFrom())->updateData(['from_state' => 1], ['id' => $from['id']]);
//                (new Article())->updateData(['url' => $bookUrl], ['id' => $article_id]);
            }
        } else {
            $msg = '<span class="red">' . $from['from_title'] . '（采集失败内容过少：<a href="' . $from['from_url'] . '" target="_blank">' . $from['from_url'] . '</a>）['.$from['from_sort'].'][<span class="orange" onclick="invalid('.$from['id'].', this)">确认</span>]  </span>';
            $content       .= "\r\n========================================================================\r\n";
            Log::write($from['from_book_id'], $content, 'errorArticle');

        }

        $nextFrom = (new CollectFrom())->getCount(['from_book_id' => $bookId, 'from_sort' => ['>', $from['from_sort'], 'from_state' => 0]]);

        return ['msg' => $msg, 'new_from' => $nextFrom, 'from_sort' =>$from['from_sort']];
    }

    /**
     * 处理采集节点中的URL
     *
     * @param array  $collect 采集节点信息数组
     * @param int    $targetId 来源文章序号
     * @param string $return_url 需要返回的url
     * @return mixed             返回的url
     */
    private function getUrl($collect, $targetId, $return_url)
    {
        $subarticleid = $collect['collect_subarticleid'];
        $subchapterid = $collect['collect_subchapterid'];

        if (!empty($subchapterid)) {
            $subchapterid         = preg_replace('/<{articleid}>/i', $targetId, $subchapterid);
            $b_subchapterid       = $this->donumber($subchapterid);
            $collect[$return_url] = preg_replace('/<{subchapterid}>/i', $b_subchapterid, $collect[$return_url]);
        }
        if (!empty($subarticleid)) {
            $subarticleid         = preg_replace('/<{articleid}>/i', $targetId, $subarticleid);
            $b_subarticleid       = $this->donumber($subarticleid);
            $collect[$return_url] = preg_replace('/<{subarticleid}>/i', $b_subarticleid, $collect[$return_url]);
        }

        $url = preg_replace('/<{articleid}>/i', $targetId, $collect[$return_url]);
        return $url;
    }

    /**
     * 处理节点中文章子序号和 章节子序号的运算   暂时只支持一步运算
     *
     * @param string $number 含加减乘数的字符串
     * @return number          运算后的结果
     */
    private function donumber($number)
    {
        $subchapterid_jia = explode('+', $number);
        if ($subchapterid_jia['1'] != '') {
            return ($subchapterid_jia[0] + $subchapterid_jia[1]);
        }
        $subchapterid_jian = explode('-', $number);
        if ($subchapterid_jian['1'] != '') {
            return ($subchapterid_jian[0] - $subchapterid_jian[1]);
        }
        $subchapterid_chen = explode('*', $number);
        if ($subchapterid_chen['1'] != '') {
            return ($subchapterid_chen[0] * $subchapterid_chen[1]);
        }
        $subchapterid_chu = explode('%%', $number);
        if ($subchapterid_chu['1'] != '') {
            return floor($subchapterid_chu[0] / $subchapterid_chu[1]);
        }
        $subchapterid_chu = explode('%', $number);
        if ($subchapterid_chu['1'] != '') {
            return ($subchapterid_chu[0] % $subchapterid_chu[1]);
        }

    }

    /**
     * 采集章节
     *
     * @author yls
     * @param $bookId
     * @return array|bool
     */
    public function getCollectFrom($bookId)
    {
        return (new CollectFrom())->getAll(['from_book_id' => $bookId]);
    }


    /**
     * 采集章节条数
     *
     * @author yls
     * @param $bookId
     * @return array|int
     */
    public function getCollectFromCount($bookId)
    {
        return (new CollectFrom())->getCount(['from_book_id' => $bookId]);
    }

    /**
     * 新增采集章节
     *
     * @author yls
     * @param array $data
     */
    public function addCollectFrom(array $data)
    {
        foreach ($data as $val) {
            (new CollectFrom())->insertData($val);
        }
    }

    /**
     * 处理文章内容的替换
     *
     * @param string $filter 过滤的内容
     * @param string $replace 过滤后替换的内容
     * @param string $content 文章内容
     * @return string $content 替换后的文章内容
     */
    private function pregContent($filter, $replace, $content)
    {
        $filter  = explode('|', $filter);
        $replace = explode('|', $replace);
        if (empty($filter)) {
            return $content;
        }
        foreach ($filter as $key => $value) {
            $content = preg_replace("/$value/i", ($replace[$key] ?? ''), $content);
        }
        $content = preg_replace("/<a([^>]*)>(.*)<\/a>/", "", $content);
        $content = preg_replace("'<script(.*?)<\/script>'is", "", $content);
        return $content;
    }

    /**
     * 上传阿里云
     *
     * @author woodlsy
     * @param int   $bookId
     * @param array $article
     * @return string|null
     * @throws ManageException
     */
    public function uploadOss(int $bookId, array $article)
    {
        $book = (new BookLogic())->getById($bookId);
        if (!$book) {
            throw new ManageException('小说不存在');
        }
        $content = HelperExtend::getBookText($book['book_category'], $bookId, $article['id']);
        try {
            $bookUrl = (new AliyunOss())->saveString((int)$bookId, (int)$article['id'], HelperExtend::doWriteContent($content));
            (new Article())->updateData(['is_oss' => 1, 'url' => $bookUrl], ['id' => $article['id']]);
            HelperExtend::delBookText($book['book_category'], $bookId, $article['id']);
            return $bookUrl;
        } catch (\Exception $e) {
            throw new ManageException('上传阿里云失败：'.$e->getMessage());
        }
    }

    /**
     * 确认已采集
     *
     * @author woodlsy
     * @param int $id
     * @return bool|int
     */
    public function confirmFrom(int $id)
    {
        return (new CollectFrom())->updateData(['from_state' => 1], ['id' => $id]);
    }
}