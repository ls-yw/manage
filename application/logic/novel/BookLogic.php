<?php
namespace application\logic\novel;

use application\library\ManageException;
use application\models\novel\Book;
use application\models\novel\Category;
use application\models\novel\Chapter;

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
}