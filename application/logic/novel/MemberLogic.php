<?php

namespace application\logic\novel;

use application\models\novel\BookApply;
use application\models\novel\User;

class MemberLogic
{
    /**
     * 获取会员列表
     *
     * @author woodlsy
     * @param int $page
     * @param int $row
     * @return array|bool
     */
    public function getList($page = 1, $row = 20)
    {
        $offset = ($page - 1) * $row;
        $where  = [];
        return (new User())->getList($where, 'id desc', $offset, $row);
    }

    /**
     * 获取会员列表count
     *
     * @author woodlsy
     * @return array|int
     */
    public function getListCount()
    {
        return (new User())->getCount([]);
    }

    /**
     * 收录申请列表
     *
     * @author woodlsy
     * @param int $page
     * @param int $row
     * @return array|bool
     */
    public function getApplyBookList($page = 1, $row = 20)
    {
        $offset = ($page - 1) * $row;
        $where  = [];
        return (new BookApply())->getList($where, 'id desc', $offset, $row);
    }

    /**
     * 收录申请列表count
     *
     * @author woodlsy
     * @return array|int
     */
    public function getApplyBookListCount()
    {
        return (new BookApply())->getCount([]);
    }
}