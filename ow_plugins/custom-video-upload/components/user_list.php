<?php

class CVIDEOUPLOAD_CMP_UserList extends OW_Component
{
    private $userList = [];
    private $isSearch;

    public function __construct( $userList = [], $isSearch = false)
    {
        parent::__construct();

        $this->userList = $userList;
        $this->isSearch = boolval($isSearch);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if (  $this->userList )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($this->userList);
            $userNameList = BOL_UserService::getInstance()->getUserNamesForList($this->userList);

            $this->assign('users', $this->userList);
            $this->assign('avatars', $avatars);
            $this->assign('userNameList', $userNameList);
        }
        else
        {
            $this->assign('users', null);
        }

        $this->assign('isSearch',  $this->isSearch);
    }
}
