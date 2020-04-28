<?php
namespace application\logic\blog;

use application\library\HelperExtend;
use application\library\ManageException;
use application\models\blog\Article;
use application\models\blog\ArticleTag;
use application\models\blog\Category;

class ArticleLogic
{
    /**========================== article start ===============================**/
    /**
     * 文章列表
     *
     * @author woodlsy
     * @param int    $page
     * @param int    $row
     * @param string $orderBy
     * @return array|bool
     */
    public function getList(int $page = 1, int $row = 20, $orderBy = 'create_at desc')
    {
        $offset = ($page - 1) * $row;
        $where = [];
        return (new Article())->getList($where, $orderBy, $offset, $row);
    }

    /**
     * 文章列表count
     *
     * @author woodlsy
     * @return array|int
     */
    public function getListCount()
    {
        $where = [];
        return (new Article())->getCount($where);
    }

    /**
     * 保存文章
     *
     * @author woodlsy
     * @param array $data
     * @return bool
     * @throws ManageException
     */
    public function saveArticle(array $data)
    {
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if (empty($id)) {
            $id = (new Article())->insertData($data);
            if (empty($id)) {
                return false;
            }
            if (!empty($data['tags'])) {
                foreach (explode(',', $data['tags']) as $val) {
                    (new ArticleTag())->insertData(['article_id' => $id, 'tag' => $val]);
                }
            }
        } else {
            $article = (new Article())->getById($id);
            if (!$article) {
                throw new ManageException('文章不存在');
            }
            $row = (new Article())->updateData($data, ['id' => $id]);
            if (empty($row)) {
                return false;
            }
            if ($article['tags'] !== $data['tags']) {
                (new ArticleTag())->delData(['article_id' => $id]);
                if (!empty($data['tags'])) {
                    foreach (explode(',', $data['tags']) as $val) {
                        (new ArticleTag())->insertData(['article_id' => $id, 'tag' => $val]);
                    }
                }
            }
        }
        return true;
    }

    /**
     * 获取文章内容
     *
     * @author woodlsy
     * @param int $id
     * @return array|mixed
     */
    public function getById(int $id)
    {
        return (new Article())->getById($id);
    }


    /**========================== article start ===============================**/

    /**========================== category start ===============================**/

    /**
     * 获取分类
     *
     * @author woodlsy
     * @param int|null $parentId
     * @param int|null $deleted
     * @param string   $orderBy
     * @return array|bool
     */
    public function getCategory(int $parentId = null, int $deleted = null, string $orderBy = 'pid asc')
    {
        $where = [];
        if(null !== $parentId) {
            $where['pid'] = $parentId;
        }
        if(null !== $deleted) {
            $where['is_deleted'] = $deleted;
        }
        $fields = ['id', 'name', 'pid', 'is_deleted', 'create_at', 'update_at'];
        return (new Category())->getAll($where, $fields, $orderBy);
    }

    /**
     * 获取分类数组
     *
     * @author woodlsy
     * @param int|null $parentId
     * @param int|null $deleted
     * @return array
     */
    public function getCategoryPairs(int $parentId = null, int $deleted = null)
    {
        return HelperExtend::arrayToKeyValue($this->getCategory($parentId, $deleted), 'id', 'name');
    }

    public function getCategoryById(int $id)
    {
        return (new Category())->getById($id);
    }

    /**
     * 保存分类
     *
     * @author woodlsy
     * @param array $data
     * @return bool|int
     */
    public function saveCategory(array $data)
    {
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if (empty($id)) {
            return (new Category())->insertData($data);
        } else {
            return (new Category())->updateData($data, ['id' => $id]);
        }
    }

    /**========================== category end ===============================**/
}