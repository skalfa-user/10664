<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Class SKTEXTCR_CMP_MessagesList
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CMP_MessagesList extends OW_Component
{
    protected $params = [];

    public function __construct( SKTEXTCR_BOL_DataSearch $params = null )
    {
        parent::__construct();

        $userService = BOL_UserService::getInstance();

        $onPage = 20;

        $page = isset($_GET['page']) && (int) $_GET['page'] ? (int) $_GET['page'] : 1;
        $first = ( $page - 1 ) * $onPage;

        $messageCount = 0;
        $messagesList = [];
        $dataParams = [];

        if ( !empty($params) )
        {
            switch ($params->type)
            {
                case SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BY_USER_NAME:

                    if ( !empty($params->userId) )
                    {
                        $dataParams['userId'] = $params->userId;
                        $dataParams['type'] = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BY_USER_NAME;
                    }

                    break;

                case SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_FROM_TO:

                    if ( !empty($params->senderId) && !empty($params->recipientId) )
                    {
                        $dataParams['senderId'] = $params->senderId;
                        $dataParams['recipientId'] = $params->recipientId;
                        $dataParams['type'] = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_FROM_TO;
                    }

                    break;

                case SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BAD_WORD:

                    if ( !empty($params->badWord) )
                    {
                        $dataParams['badWord'] = $params->badWord;
                        $dataParams['type'] = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BAD_WORD;
                    }

                    break;
            }

            if ( !empty($dataParams) )
            {
                $messageCount = SKTEXTCR_BOL_Service::getInstance()->countMessageList($dataParams);
                $messagesList = SKTEXTCR_BOL_Service::getInstance()->findMessageList($first, $onPage, $dataParams);
            }
        }
        else
        {
            $messageCount = SKTEXTCR_BOL_Service::getInstance()->countMessageList([]);
            $messagesList = SKTEXTCR_BOL_Service::getInstance()->findMessageList($first, $onPage, []);
        }

        if ( empty($messagesList) && $page > 1 )
        {
            OW::getApplication()->redirect(OW::getRequest()->buildUrlQueryString(null, ['page' => $page - 1]));
        }

        $userIdList = [];

        if ( !empty($messagesList) )
        {
            foreach ( $messagesList as $message )
            {
                if ( !in_array($message['senderId'], $userIdList) )
                {
                    array_push($userIdList, $message['senderId']);
                }

                if ( !in_array($message['recipientId'], $userIdList) )
                {
                    array_push($userIdList, $message['recipientId']);
                }
            }

            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
            $userNameList = $userService->getUserNamesForList($userIdList);
            $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, ['sex', 'birthdate', 'email']);


            $sexList = [];

            foreach ( $userIdList as $id )
            {
                if ( empty($questionList[$id]['sex']) )
                {
                    continue;
                }

                $sex = $questionList[$id]['sex'];
                $sexValue = '';

                if ( !empty($sex) )
                {
                    for ( $i = 0 ; $i < BOL_QuestionService::MAX_QUESTION_VALUES_COUNT; $i++ )
                    {
                        $val = pow( 2, $i );
                        if ( (int)$sex & $val  )
                        {
                            $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                        }
                    }

                    if ( !empty($sexValue) )
                    {
                        $sexValue = substr($sexValue, 0, -2);
                    }
                }

                $sexList[$id] = $sexValue;
            }

            // Paging
            $pages = (int) ceil($messageCount / $onPage);
            $paging = new BASE_CMP_Paging($page, $pages, $onPage);

            $this->addComponent('paging', $paging);

            $this->assign('messagesList', $messagesList);
            $this->assign('total', $messageCount);
            $this->assign('sexList', $sexList);
            $this->assign('avatars', $avatars);
            $this->assign('userNameList', $userNameList);
            $this->assign('questionList', $questionList);
        }
        else
        {
            $this->assign('messagesList', null);
        }

    }
}