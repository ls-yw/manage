<?php

namespace application\modules\novel\controllers;

use application\base\BaseController;
use application\library\ManageException;
use application\logic\novel\BookLogic;
use application\logic\novel\CollectLogic;
use Exception;
use woodlsy\httpClient\HttpCurl;
use woodlsy\phalcon\library\Helper;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class CollectController extends BaseController
{
    /**
     * 采集目标站
     *
     * @author yls
     */
    public function indexAction()
    {
        $this->view->data      = (new CollectLogic())->getCollectList($this->page, $this->size);
        $this->view->totalPage = ceil((new CollectLogic())->getCollectListCount() / $this->size);
        $this->view->page      = $this->page;
        $this->view->title     = '采集节点';
        $this->view->pageLink  = '?page={page}';
    }

    /**
     * 删除采集节点
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function delAction()
    {
        try {

            $id = (int) $this->get('id', 'int', 0);
            (new CollectLogic())->delCollect($id);
            Redis::getInstance()->setex('alert_success', 3600, '删除成功');
            return $this->response->redirect('/novel/collect/index.html');
        } catch (ManageException $e) {
            Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
            return $this->response->redirect('/index/login/index.html');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            Redis::getInstance()->setex('alert_error', 3600, '系统错误');
            return $this->response->redirect('/index/login/index.html');
        }
    }

    /**
     * 保存节点配置
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function setAction()
    {
        $id      = (int) $this->get('id', 'int', 0);
        $collect = (new CollectLogic())->getById($id);
        if ($this->request->isPost()) {
            $data = [
                'id'                     => $id,
                'collect_name'           => $this->post('collect_name'),
                'collect_host'           => $this->post('collect_host'),
                'collect_subchapterid'   => $this->post('collect_subchapterid'),
                'collect_subarticleid'   => $this->post('collect_subarticleid'),
                'collect_iconv'          => $this->post('collect_iconv'),
                'collect_urlarticle'     => $this->post('collect_urlarticle'),
                'collect_articletitle'   => $this->post('collect_articletitle'),
                'collect_author'         => $this->post('collect_author'),
                'collect_sort'           => $this->post('collect_sort'),
                'collect_sortid'         => $this->post('collect_sortid'),
                'collect_keyword'        => $this->post('collect_keyword'),
                'collect_intro'          => $this->post('collect_intro'),
                'collect_articleimage'   => $this->post('collect_articleimage'),
                'collect_filterimage'    => $this->post('collect_filterimage'),
                'collect_indexlink'      => $this->post('collect_indexlink'),
                'collect_fullarticle'    => $this->post('collect_fullarticle'),
                'collect_urlindex'       => $this->post('collect_urlindex'),
                'collect_volume'         => $this->post('collect_volume'),
                'collect_chapter'        => $this->post('collect_chapter'),
                'collect_chapterid'      => $this->post('collect_chapterid'),
                'collect_urlchapter'     => $this->post('collect_urlchapter'),
                'collect_content'        => $this->post('collect_content'),
                'collect_contentfilter'  => $this->post('collect_contentfilter'),
                'collect_contentreplace' => $this->post('collect_contentreplace'),
                'collect_status'         => $this->post('collect_status'),
            ];
            try {

                if (empty($data['collect_name'])) {
                    throw new ManageException('节点名称不能为空');
                }
                if (empty($data['collect_host'])) {
                    throw new ManageException('目标网站不能为空');
                }
                if (empty($data['collect_urlarticle'])) {
                    throw new ManageException('文章信息页面地址不能为空');
                }
                $row = (new CollectLogic())->save($data);
                if (!$row) {
                    throw new ManageException('保存失败');
                }
                Redis::getInstance()->setex('alert_success', 3600, '保存成功');
                return $this->response->redirect('/novel/collect/index.html');
            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                die('<script>window.history.go(-1);</script>');
            }

        } else {

            $this->view->info     = $collect;
            $this->view->title    = '设置采集节点';
            $this->view->menuflag = 'novel-collect-index';
        }
    }

    /**
     * 采集准备
     *
     * @author yls
     */
    public function startAction()
    {
        try {

            $act = $this->get('act', 'string', 'one');
            $id  = $this->get('id', 'int', '0');

            $title = '采集设置';

            $collect = (new CollectLogic())->getById($id);
            if (!$collect) {
                throw new ManageException('找不到采集节点数据');
            }

            if ($act == 'co_intro') {
                $title    = '文章信息页采集';
                $targetId = (int) $this->get('target_id', 'int');
                $book     = (new CollectLogic())->startCollect($targetId, $collect);

                $this->view->book     = $book;
                $this->view->targetId = $targetId;
                $this->view->category = (new BookLogic())->getCategoryPairs();
            }

            $this->view->info     = $collect;
            $this->view->act      = $act;
            $this->view->title    = $title;
            $this->view->menuflag = 'novel-collect-index';

        } catch (ManageException $e) {
            Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
            die('<script>window.history.go(-1);</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            Redis::getInstance()->setex('alert_error', 3600, '系统错误');
            die('<script>window.history.go(-1);</script>');
        }

    }

    /**
     * 采集小说基本信息
     *
     * @author yls
     * @throws ManageException
     * @throws \woodlsy\httpClient\HttpClientException
     */
    public function bookAction()
    {
        if ($this->request->isPost()) {
            try {
                $url       = $this->config->uploadPath . '/upload/urlImg?project=' . $this->router->getModuleName() . '&url=' . trim($this->post('book_img', 'string'));
                $res       = (new HttpCurl())->setUrl($url)->get();
                $data      = [
                    'book_name'            => trim($this->post('book_name', 'string')),
                    'book_category'        => trim($this->post('book_sort', 'string')),
                    'book_author'          => trim($this->post('book_author', 'string')),
                    'book_state'           => $this->post('book_state', 'int'),
                    'book_keyword'         => trim($this->post('book_keyword', 'string')),
                    'book_intro'           => trim($this->post('book_intro', 'string')),
                    'book_is_collect'      => $this->post('monitoring', 'int'),
                    'book_collect_id'      => trim($this->post('id', 'string')),
                    'book_from_article_id' => $this->post('target_id', 'string'),
                    'book_img'             => $this->config->uploadPath . '/' . Helper::jsonDecode($res)['url'],
                ];
                $indexlink = $this->post('indexlink', 'int');

                $book = (new BookLogic())->getByNameAndAuthor($data['book_name'], $data['book_author']);
                if ($book) {

                    if ((int) $book['book_collect_id'] !== (int) $data['book_collect_id']) {
                        throw new ManageException('该小说已存在但不是该采集节点采集，如需重新采集，请先删除该小说');
                    }
                    $this->view->act = 'update';
                    $this->view->id  = $book['id'];
                } else {
                    $id = (new BookLogic())->save($data);
                    if (!$id) {
                        throw new ManageException('保存小说失败');
                    }
                    $this->view->act = 'add';
                    $this->view->id  = $id;
                }

                $this->view->targetId  = $this->post('target_id', 'int');
                $this->view->collectId = $data['book_collect_id'];
                $this->view->indexlink = $indexlink;
                $this->view->title     = '小说采集';
                $this->view->menuflag  = 'novel-collect-index';

            } catch (ManageException $e) {
                Redis::getInstance()->setex('alert_error', 3600, $e->getMessage());
                die('<script>window.history.go(-1);</script>');
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                Redis::getInstance()->setex('alert_error', 3600, '系统错误');
                die('<script>window.history.go(-1);</script>');
            }
        } else {
            $id        = (int) $this->get('id', 'int');
            $collectId = (int) $this->get('collect_id', 'int');
            $targetId  = (int) $this->get('target_id', 'int');

            $collect = (new CollectLogic())->getById($collectId);
            if (!$collect) {
                Redis::getInstance()->setex('alert_error', 3600, '找不到采集节点数据');
                die('<script>window.history.go(-1);</script>');
            }
            $book = (new CollectLogic())->startCollect($targetId, $collect);

            $this->view->act       = 'update';
            $this->view->targetId  = $targetId;
            $this->view->collectId = $collectId;
            $this->view->indexlink = $book['indexlink'];
            $this->view->id        = $id;
            $this->view->title     = '小说采集';
            $this->view->menuflag  = 'novel-collect-bookList';
        }
    }

    /**
     * 采集小说信息
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function articleAction()
    {
        if ($this->request->isAjax()) {
            try {
                $act       = $this->get('act', 'string');
                $bookId    = (int) $this->get('book_id', 'int');
                $targetId  = (int) $this->get('target_id', 'int');
                $indexlink = (int) $this->get('indexlink', 'string');
                $collectId = (int) $this->get('collect_id', 'int');

                if (empty($bookId) || empty($targetId) || empty($collectId)) {
                    $msg = '小说ID不能为空,目标文章ID不能为空,采集节点ID不能为空';
                    return $this->ajaxReturn(1, $msg);
                }

                $data = (new CollectLogic())->article($collectId, $targetId, $indexlink, $bookId);

                if ('add' === $act) {
                    // 判断是否已采集
                    $collectForm = (new CollectLogic())->getCollectFromCount($bookId);
                    if ($collectForm) {
                        return $this->ajaxReturn(1, '该小说已采集过，请返回选择更新');
                    }

                    (new CollectLogic())->addCollectFrom($data);
                    $result        = [];
                    $result['url'] = '/novel/collect/chapter.html?act=add&book_id=' . $bookId . '&collect_id=' . $collectId;
                    return $this->ajaxReturn(0, '共有' . count($data) . '篇文章需采集：<br/>', null, $result);
                } elseif ('update' === $act) {
                    $from = (new CollectLogic())->getCollectFrom($bookId);

                    $new_from = array();
                    $add_from = array();
                    foreach ($data as $key => $value) {
                        if (isset($from[$key]) && $value['from_article_id'] == $from[$key]['from_article_id']) {
                            if ($from[$key]['from_state'] == 0) $new_from[] = $from[$key];
                        } else {
                            $new_from[]     = $value;  //需要重新采集的内容
                            $add_from[$key] = $value;  //需要新增如数据库的内容
                        }
                    }

                    if (isset($add_from['0']) && is_array($add_from['0'])) {
                        (new CollectLogic())->addCollectFrom($add_from);
                    }

                    if (is_array($data['0']) && !isset($new_from['0'])) {
                        throw new ManageException('无新内容可采集');
                    }

                    return $this->ajaxReturn(0, '共有' . count($new_from) . '篇文章需采集：<br/>', null, ['url' => '/novel/collect/chapter.html?act=update&book_id=' . $bookId . '&collect_id=' . $collectId]);
                }

            } catch (ManageException $e) {
                return $this->ajaxReturn(1, $e->getMessage());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->ajaxReturn(1, '系统错误');
            }
        }
    }

    /**
     * 保存章节
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function chapterAction()
    {
        if ($this->request->isAjax()) {
            try {
                $act       = $this->get('act', 'string');
                $bookId    = (int) $this->get('book_id', 'int');
                $collectId = (int) $this->get('collect_id', 'int');

                $book = (new BookLogic())->getById($bookId);
                $msg  = '';

                $chapterArray = (new BookLogic())->getChapter($bookId, '默认章节');
                if (empty($chapterArray)) {
                    //默认新增章节 默认章节，采集的文章都放入此章节内
                    $chapter                       = [];
                    $chapter['chapter_name']       = '默认章节';
                    $chapter['book_id']            = $bookId;
                    $chapter['book_name']          = (new BookLogic())->getBookNameById($bookId);
                    $chapter['chapter_articlenum'] = 0;
                    $chapter['chapter_order']      = 1;
                    $chapterId                     = (new BookLogic())->saveChapter($chapter);
                    if (empty($chapterId)) {
                        throw new ManageException('章节新增失败');
                    }
                    $msg = '新增默认章节  ';
                } else {
                    $chapterId = $chapterArray[0]['id'];
                }

                //更新最后采集时间
                (new BookLogic())->updateCollectTime($bookId);

                return $this->ajaxReturn(0, $msg, null, ['url' => '/novel/collect/doArticle.html?act=' . $act . '&book_id=' . $bookId . '&collect_id=' . $collectId . '&chapter_id=' . $chapterId . '&category_id=' . $book['book_category']]);
            } catch (ManageException $e) {
                return $this->ajaxReturn(1, $e->getMessage());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->ajaxReturn(1, '系统错误');
            }
        }
    }

    /**
     * 采集文章
     *
     * @author yls
     * @return \Phalcon\Http\ResponseInterface
     */
    public function doArticleAction()
    {
        if ($this->request->isAjax()) {
            try {
                $act        = $this->get('act', 'string');
                $bookId     = (int) $this->get('book_id', 'int');
                $collectId  = (int) $this->get('collect_id', 'int');
                $chapterId  = (int) $this->get('chapter_id', 'int');
                $categoryId = (int) $this->get('category_id', 'int');
                $fromSort   = (int) $this->get('from_sort', 'int', 0);

                $res = (new CollectLogic())->collectArticle($bookId, $collectId, $chapterId, $categoryId, $fromSort);

                if (empty($res['new_from'])) {
                    return $this->ajaxReturn(0, $res['msg'] . ' 采集完成');
                }

                return $this->ajaxReturn(0, $res['msg'], null, ['url' => '/novel/collect/doArticle.html?act=' . $act . '&book_id=' . $bookId . '&collect_id=' . $collectId . '&chapter_id=' . $chapterId . '&category_id=' . $categoryId . '&from_sort=' . $res['from_sort']]);
            } catch (ManageException $e) {
                return $this->ajaxReturn(1, $e->getMessage());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->ajaxReturn(1, '系统错误');
            }
        }
    }

    /**
     * 可采集小说列表
     *
     * @author yls
     */
    public function bookListAction()
    {
        $keywords              = $this->get('keywords', 'string');
        $this->view->data      = (new BookLogic())->getList($keywords, 1, $this->page, $this->size);
        $this->view->totalPage = ceil((new BookLogic())->getListCount($keywords, 1) / $this->size);
        $this->view->page      = $this->page;
        $this->view->pageLink  = '?page={page}&keywords=' . $keywords;
        $this->view->title     = '采集小说';
    }
}