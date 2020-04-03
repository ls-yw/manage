<?php


namespace application\modules\novel\controllers;


use application\base\BaseController;
use application\logic\novel\MemberLogic;

class MemberController extends BaseController
{

    /**
     * 会员列表
     *
     * @author woodlsy
     */
    public function indexAction()
    {
        $this->view->data      = (new MemberLogic())->getList($this->page, $this->size);
        $this->view->totalPage = ceil((new MemberLogic())->getListCount() / $this->size);
        $this->view->page      = $this->page;
        $this->view->title     = '会员列表';
    }

    /**
     * 收录申请
     *
     * @author woodlsy
     */
    public function applyBookAction()
    {
        $this->view->data      = (new MemberLogic())->getApplyBookList($this->page, $this->size);
        $this->view->totalPage = ceil((new MemberLogic())->getApplyBookListCount() / $this->size);
        $this->view->page      = $this->page;
        $this->view->title     = '申请收录列表';
    }

}