<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * User List
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_system_plugins.skadate.components
 * @since 1.0
 */
class SKADATE_CMP_UserList extends OW_Component
{
    private $userList = array();
    private $fieldList = array();
    private $displayActivity = TRUE;

    public function __construct( $userList, $fieldList, $usersCount )
    {
        parent::__construct();

        $this->userList = $userList;
        $this->fieldList = array_unique($fieldList);

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $usersOnPage = OW::getConfig()->getValue('base', 'users_on_page');
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($usersCount / $usersOnPage), 5));
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $userList = array();
        $userDtoList = array();

        $userService = BOL_UserService::getInstance();
        $questionService = BOL_QuestionService::getInstance();

        $userIdList = array_keys($this->userList);
        $userDataList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $this->fieldList);

        foreach ( $userService->findUserListByIdList($userIdList) as $userDto )
        {
            $userDtoList[$userDto->id] = $userDto;
        }

        foreach ( $this->userList as $userId => $fieldList )
        {
            $fields = array_diff(array_keys($fieldList), $this->fieldList);
            $fieldsData = $questionService->getQuestionData(array($userId), $fields);
            $userList[$userId]['fields'] = array_merge(!empty($userDataList[$userId]) ? $userDataList[$userId] : array(), !empty($fieldsData[$userId]) ? $fieldsData[$userId] : array(), $fieldList);
            $userList[$userId]['dto'] = $userDtoList[$userId];
        }

        $this->assign('userList', $userList);
        $this->assign('avatars', BOL_AvatarService::getInstance()->getAvatarsUrlList($userIdList, 2));
        $this->assign('onlineList', !empty($userIdList) ? $userService->findOnlineStatusForUserList($userIdList) : array());
        $this->assign('usernameList', $userService->getUserNamesForList($userIdList));
        $this->assign('displaynameList', $userService->getDisplayNamesForList($userIdList));
        $this->assign('displayActivity', $this->displayActivity);
    }

    public function setDisplayActivity( $value )
    {
        $this->displayActivity = (booL) $value;

        return $this;
    }
}
