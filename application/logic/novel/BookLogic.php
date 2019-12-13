<?php
namespace application\logic\novel;

use application\library\AliyunOss;
use application\library\HelperExtend;
use application\library\ManageException;
use application\models\novel\Article;
use application\models\novel\Book;
use application\models\novel\Category;
use application\models\novel\Chapter;
use application\models\novel\CollectFrom;
use Exception;

class BookLogic
{
    /**
     * 分类数组
     *
     * @author yls
     * @return array
     */
    public function getCategoryPairs()
    {
        $categorys = (new Category())->getAll([]);
        $arr = [];
        if (!empty($categorys)) {
            foreach ($categorys as $val) {
                $arr[$val['id']] = $val['name'];
            }
        }
        return $arr;
    }

    /**
     * 保存小说基本信息
     *
     * @author yls
     * @param array $data
     * @return bool|int
     * @throws ManageException
     */
    public function save(array $data)
    {
        if(!preg_match('/^(http|https):\/\//i', $data['book_img'])) {
            throw new ManageException('需补全图片地址');
        }
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if(empty($id)) {
            return (new Book())->insertData($data);
        } else {
            return (new Book())->updateData($data, ['id' => $id]);
        }
    }

    /**
     * 保存栏目信息
     *
     * @author yls
     * @param array $data
     * @return bool|int
     */
    public function saveCategory(array $data)
    {
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if(empty($id)) {
            return (new Category())->insertData($data);
        } else {
            return (new Category())->updateData($data, ['id' => $id]);
        }
    }

    /**
     * 查找小说基本信息
     *
     * @author yls
     * @param $bookId
     * @return array|mixed
     */
    public function getById($bookId)
    {
        return (new Book())->getById($bookId);
    }

    /**
     * 根据书名和作者查找
     *
     * @author yls
     * @param string $name
     * @param string $author
     * @return array
     */
    public function getByNameAndAuthor(string $name, string $author)
    {
        return (new Book())->getOne(['book_name' => $name, 'book_author' => $author]);
    }

    /**
     * 查找章节
     *
     * @author yls
     * @param      $bookId
     * @param null $chapterName
     * @return array|bool
     */
    public function getChapter($bookId, $chapterName = null)
    {
        $where  = ['book_id' => $bookId];
        if ($chapterName) {
            $where['chapter_name'] = $chapterName;
        }
        return (new Chapter())->getAll($where);
    }

    /**
     * 根据ID获取小说名称
     *
     * @author yls
     * @param $bookId
     * @return string
     */
    public function getBookNameById($bookId)
    {
        return (new Book())->getById($bookId, ['book_name'])['book_name'] ?? '';
    }

    /**
     * 保存章节
     *
     * @author yls
     * @param array $data
     * @return bool|int
     */
    public function saveChapter(array $data)
    {
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if(empty($id)) {
            return (new Chapter())->insertData($data);
        } else {
            return (new Chapter())->updateData($data, ['id' => $id]);
        }
    }

    /**
     * 可采集小说列表
     *
     * @author yls
     * @param      $keywords
     * @param null $isCollect
     * @param null $page
     * @param null $row
     * @return array|bool
     */
    public function getList($keywords, $isCollect = null, $page = null, $row = null)
    {
        $offset = ($page - 1) * $row;
        $where = ['book_collect_id' => ['!=', 0]];
        if (!empty($keywords)) {
            $where['book_name'] = ['like', '%'.$keywords.'%'];
        }
        if ($isCollect) {
            $where['book_is_collect'] = $isCollect;
        }
        $list   = (new Book())->getList($where, 'id desc', $offset, $row);
        return $list;
    }

    /**
     * 可采集小说列表总数
     *
     * @author yls
     * @param      $keywords
     * @param null $isCollect
     * @return array|int
     */
    public function getListCount($keywords, $isCollect = null)
    {
        $where = ['book_collect_id' => ['!=', 0]];
        if (!empty($keywords)) {
            $where['book_name'] = ['like', '%'.$keywords.'%'];
        }
        if ($isCollect) {
            $where['book_is_collect'] = $isCollect;
        }
        $list   = (new Book())->getCount($where);
        return $list;
    }

    /**
     * 分类列表
     *
     * @author yls
     * @param $page
     * @param $row
     * @return array|bool
     */
    public function getCategoryList($page, $row)
    {
        $offset = ($page - 1) * $row;
        $list   = (new Category())->getList([], 'id desc', $offset, $row);
        return $list;
    }

    /**
     * 分类列表条数
     *
     * @author yls
     * @return array|int
     */
    public function getCategoryListCount()
    {
        $list   = (new Category())->getCount([]);
        return $list;
    }

    /**
     * 分类详情
     *
     * @author yls
     * @param int $id
     * @return array|mixed
     */
    public function getCategoryById(int $id)
    {
        $info   = (new Category())->getById($id);
        return $info;
    }

    /**
     * 更新采集时间
     *
     * @author yls
     * @param $bookId
     * @return bool|int
     */
    public function updateCollectTime($bookId)
    {
        return (new Book())->updateData(['book_last_collect_time' => HelperExtend::now()], ['id' => $bookId]);
    }

    /**
     * 获取采集文章数量
     *
     * @author woodlsy
     * @param int      $bookId
     * @param int|null $status
     * @return array|int
     */
    public function getCollectFromCount(int $bookId, int $status = null)
    {
        $where = ['from_book_id' => $bookId];
        if (null !== $status) {
            $where['from_state'] = $status;
        }
        return (new CollectFrom())->getCount($where);
    }

    /**
     * 获取oss文章数量
     *
     * @author woodlsy
     * @param int      $bookId
     * @param int|null $isOss
     * @return array|int
     */
    public function getArticleByOssCount(int $bookId, int $isOss = null)
    {
        $where = ['book_id' => $bookId];
        if (null !== $isOss) {
            $where['is_oss'] = $isOss;
        }
        return (new Article())->getCount($where);
    }

    /**
     * 获取oss文章列表
     *
     * @author woodlsy
     * @param int      $bookId
     * @param int|null $isOss
     * @param string   $order
     * @return array|bool
     */
    public function getArticleByOssAll(int $bookId, int $isOss = null, string $order = 'article_sort asc')
    {
        $where = ['book_id' => $bookId];
        if (null !== $isOss) {
            $where['is_oss'] = $isOss;
        }
        return (new Article())->getAll($where, ['id', 'title', 'book_id'], $order);
    }

    /**
     * 获取文章列表
     *
     * @author woodlsy
     * @param int    $bookId
     * @param string $order
     * @param int    $page
     * @param int    $row
     * @return array|bool
     */
    public function getArticleList(int $bookId, string $order, int $page, int $row)
    {
        $offset = ($page - 1) * $row;
        $where = ['book_id' => $bookId];
        return (new Article())->getList($where, $order, $offset, $row);
    }

    /**
     * 获取文章列表数量
     *
     * @author woodlsy
     * @param int $bookId
     * @return array|int
     */
    public function getArticleListCount(int $bookId)
    {
        $where = ['book_id' => $bookId];
        return (new Article())->getCount($where);
    }

    /**
     * 获取文章详情
     *
     * @author woodlsy
     * @param int  $id
     * @param bool $getContent
     * @return array|mixed
     * @throws ManageException
     */
    public function getArticleById(int $id, bool $getContent = false)
    {
        $article = (new Article())->getById($id);
        if (!empty($article) && $getContent) {
            $article['content'] = (new AliyunOss())->getString($article['book_id'], $id);
        }
        return $article;
    }

    /**
     * 保存文章
     *
     * @author woodlsy
     * @param array $data
     * @param int   $articleId
     * @return bool|int
     * @throws ManageException
     * @throws \OSS\Core\OssException
     */
    public function saveArticle(array $data, int $articleId)
    {
        $content = $data['content'];
        unset($data['content']);
        if (empty($articleId)) {
            // 判断是否要更新article_sort
            $this->updateArticleSort((int)$data['book_id'], (int)$data['article_sort']);

            $articleId =  (new Article())->insertData($data);
            if (empty($articleId)) {
                throw new ManageException('插入数据失败');
            }
        } else {
            $oldArticle = (new Article())->getById($articleId);
            if ((int)$oldArticle['article_sort'] !== (int)$data['article_sort']) {
                $this->updateArticleSort((int)$data['book_id'], (int)$data['article_sort']);
            }
            try{
                (new AliyunOss())->delFile((int)$data['book_id'], $articleId);
            } catch (Exception $e) {
                throw new ManageException('删除oss上文章内容失败'.$e->getMessage());
            }
            $data['bk_article'] = 0;
            $data['url'] = '';
            $row = (new Article())->updateData($data, ['id' => $articleId]);
            if (empty($row)) {
                throw new ManageException('更新数据失败');
            }
        }

        $url = (new AliyunOss())->saveString($data['book_id'], $articleId, $content);
        return (new Article())->updateData(['url' => $url, 'is_oss' => 1], ['id' => $articleId]);
    }

    /**
     * 更新文章排序
     *
     * @author woodlsy
     * @param int $bookId
     * @param int $articleSort
     */
    protected function updateArticleSort(int $bookId, int $articleSort)
    {
        $existSort = (new Article())->getCount(['book_id' => $bookId, 'article_sort' => $articleSort]);
        if (!empty($existSort)) {
            (new Article())->updateData(['article_sort' => ['+', 1]], ['book_id' => $bookId, 'article_sort' => ['>=', $articleSort]]);
        }
    }
}