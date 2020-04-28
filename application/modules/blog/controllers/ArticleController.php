<?php

namespace application\modules\blog\controllers;

use application\base\BaseController;
use application\library\ManageException;
use application\logic\blog\ArticleLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class ArticleController extends BaseController
{
    private $crumbs = [['name' => '博客系统']];

    /**
     * 文章列表
     *
     * @author yls
     */
    public function indexAction()
    {
        $article  = (new ArticleLogic())->getList($this->page, $this->size);
        $category = (new ArticleLogic())->getCategoryPairs(0);

        $this->view->totalPage = ceil((new ArticleLogic())->getListCount() / $this->size);
        $this->view->page      = $this->page;
        $this->view->pageLink  = '?page={page}';
        $this->view->title    = '文章列表';
        $this->view->data     = $article;
        $this->view->category = $category;
        $this->view->crumbs   = $this->crumbs;
    }

    /**
     * 编辑文章
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function setArticleAction()
    {
        if ($this->request->isPost()) {
            try {
                $id         = $this->post('id');
                $title      = $this->post('title');
                $categoryId = $this->post('category_id');
//                $desc       = $this->post('desc');
                $content = $this->post('content');
                $tags    = $this->post('tags');
                $isPush  = $this->post('is_push');
                $sort    = $this->post('sort');
                $imgUrl    = $this->post('img_url');

                if (empty($title)) {
                    throw new ManageException('标题不能为空');
                }

                $data = [
                    'id'          => $id,
                    'title'       => $title,
                    'category_id' => $categoryId,
                    //                    'desc'       => $desc,
                    'content'     => $content,
                    'tags'        => $tags,
                    'is_push'     => $isPush,
                    'sort'        => $sort,
                    'img_url'     => $imgUrl,
                ];

                $row = (new ArticleLogic())->saveArticle($data);
                if (!$row) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/blog/article/index.html');
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                return $this->response->redirect($this->request->getHTTPReferer());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                return $this->response->redirect($this->request->getHTTPReferer());
            }
        } else {
            $id = (int) $this->get('id');

            $article = empty($id) ? [] : (new ArticleLogic())->getById($id);
            if (!empty($article)) {
                $this->crumbs[] = ['name' => $article['title']];
            }

            $parentCategory       = (new ArticleLogic())->getCategoryPairs(NULL, 0);
            $this->view->menuflag = 'article';
            $this->view->title    = '编辑文章';
            $this->view->category = $parentCategory;
            $this->view->data     = $article;
            $this->view->crumbs   = $this->crumbs;
            $this->view->menuflag = 'blog-article-index';
        }
    }


}
