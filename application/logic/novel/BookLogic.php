<?php
namespace application\logic\novel;

use application\library\ManageException;
use application\models\novel\Book;
use application\models\novel\Category;
use application\models\novel\Chapter;
use woodlsy\phalcon\library\Helper;

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
     * @param $keywords
     * @param $page
     * @param $row
     * @return array|bool
     */
    public function getList($keywords, $page, $row)
    {
        $offset = ($page - 1) * $row;
        $where = ['book_collect_id' => ['!=', 0], 'book_is_collect' => 1];
        if (!empty($keywords)) {
            $where['book_name'] = ['like', '%'.$keywords.'%'];
        }
        $list   = (new Book())->getList($where, 'id desc', $offset, $row);
        return $list;
    }

    /**
     * 可采集小说列表总数
     *
     * @author yls
     * @param $keywords
     * @return array|int
     */
    public function getListCount($keywords)
    {
        $where = ['book_collect_id' => ['!=', 0], 'book_is_collect' => 1];
        if (!empty($keywords)) {
            $where['book_name'] = ['like', '%'.$keywords.'%'];
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
        return (new Book())->updateData(['book_last_collect_time' => Helper::now()], ['id' => $bookId]);
    }
}