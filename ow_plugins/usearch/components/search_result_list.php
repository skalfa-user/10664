<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Search result component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.usearch.components
 * @since 1.5.3
 */
class USEARCH_CMP_SearchResultList extends OW_Component
{
    protected $items;
    protected $orderType;
    protected $page;
    protected $actions;
    protected $location;

    public function __construct( $items, $page, $orderType = null, $actions = false )
    {
        parent::__construct();

        $this->items = $items;
        $this->actions = $actions;
        $this->page = $page;
        $this->orderType = $orderType;
        
        $data = OW::getSession()->get('usearch_search_data');
        
        if ( $this->orderType == USEARCH_BOL_Service::LIST_ORDER_DISTANCE )
        {
            //$location = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('googlemap_location'));
            
            if ( !empty($data['googlemap_location']['json']) )
            {
                $this->location = $data['googlemap_location'];
            }
        }

        $url = OW::getPluginManager()->getPlugin('usearch')->getStaticCssUrl() . 'search.css';
        OW::getDocument()->addStyleSheet($url);
        
        $this->assign('searchUrl', OW::getRouter()->urlForRoute('users-search'));
    }

    public function getFields( $userIdList )
    {
        $fields = array();
        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');
        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }
        
        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');
        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $qLocation = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');
        if ( $qLocation && $qLocation->onView )
        {
            $qs[] = 'googlemap_location';
        }

//        if ( $this->listType == 'details' )
//        {
//            $qs[] = 'aboutme';
//        }
        
        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);
        
        $matchCompatibility = array();

        if ( $this->orderType == USEARCH_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY )
        {
            if ( OW::getPluginManager()->isPluginActive('matchmaking') && OW::getUser()->isAuthenticated() )
            {
                $maxCompatibility = MATCHMAKING_BOL_QuestionMatchDao::getInstance()->getMaxPercentValue();
                $matchCompatibilityList = MATCHMAKING_BOL_Service::getInstance()->findCompatibilityByUserIdList( OW::getUser()->getId(), $userIdList, 0, 1000 );
                
                foreach ( $matchCompatibilityList as $compatibility )
                {
                    $matchCompatibility[$compatibility['userId']] = (int)$compatibility['compatibility'];
                }
            }
        }
        
        foreach ( $questionList as $uid => $q )
        {
            $fields[$uid] = array();
            $age = '';

            if ( !empty($q['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            if ( !empty($q['sex']) )
            {
                $sex = $q['sex'];
                $sexValue = '';

                for ( $i = 0 ; $i < BOL_QuestionService::MAX_QUESTION_VALUES_COUNT; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val  )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid]['base'][] = array(
                    'label' => '', 'value' => $sexValue . ' ' . $age
                );
            }

            if ( !empty($q['aboutme']) )
            {
                $fields[$uid]['aboutme'] = array(
                    'label' => '', 'value' => $q['aboutme']
                );
            }

            if ( !empty($q['googlemap_location']['address']) )
            {
                $fields[$uid]['location'] = array(
                    'label' => '', 'value' => $q['googlemap_location']['address']
                );
            }
            
            if ( isset($matchCompatibility[$uid]) )
            {
                $fields[$uid]['match_compatibility'] = array(
                        'label' => '', 'value' => $matchCompatibility[$uid].'%'
                    );
            }
            
            if ( $this->orderType == USEARCH_BOL_Service::LIST_ORDER_DISTANCE )
            {
                if ( OW::getPluginManager()->isPluginActive('googlelocation') && !empty($q['googlemap_location']) && !empty($this->location) )
                {
                    $event = new OW_Event('googlelocation.calc_distance', array(
                        'lat' => $this->location['latitude'], 
                        'lon' => $this->location['longitude'], 
                        'lat1' => $q['googlemap_location']['latitude'], 
                        'lon1' => $q['googlemap_location']['longitude']));
                    
                    OW::getEventManager()->trigger($event);
                    
                    $data = $event->getData();
                    
                    if ( $data['units'] == 'miles' )
                    {
                        $html = '&nbsp;<span>'.OW::getLanguage()->text('usearch', 'miles').'</span>';
                    }
                    else 
                    {
                        $html = '&nbsp;<span>'.OW::getLanguage()->text('usearch', 'kms').'</span>';
                    }
                    
                    
                    $fields[$uid]['distance'] = array(
                        'label' => '', 'value' => $data['distance'].$html
                    );
                }
            }
        }
        
        return $fields;
    }

    private function process( $list )
    {
        $service = BOL_UserService::getInstance();

        $idList = array();
        $userList = array();

        foreach ( $list as $dto )
        {
            $userList[] = array('dto' => $dto);
            $idList[] = $dto->getId();
        }

        $displayNameList = array();
        $questionList = array();
        $markList = array();
        
        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, false, true, true, false);
            $vatarsSrc = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList, 2);

            foreach ( $avatars as $userId => $avatarData )
            {
                $avatars[$userId]['src'] = $vatarsSrc[$userId];
                $displayNameList[$userId] = isset($avatarData['title']) ? $avatarData['title'] : '';
            }

            $usernameList = $service->getUserNamesForList($idList);
            $onlineInfo = $service->findOnlineStatusForUserList($idList);

            $showPresenceList = array();
            $ownerIdList = array();

            foreach ( $onlineInfo as $userId => $isOnline )
            {
                $ownerIdList[$userId] = $userId;
            }

            $eventParams = array(
                'action' => 'base_view_my_presence_on_site',
                'ownerIdList' => $ownerIdList,
                'viewerId' => OW::getUser()->getId()
            );

            $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

            foreach ( $onlineInfo as $userId => $isOnline )
            {
                // Check privacy permissions
                if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
                {
                    $showPresenceList[$userId] = false;
                    continue;
                }

                $showPresenceList[$userId] = true;
            }

            if ( $this->actions )
            {
                $actions = USEARCH_CLASS_EventHandler::getInstance()->collectUserListActions($idList);
                $this->assign('actions', $actions);
            }

            $this->assign('showPresenceList', $showPresenceList);
            $this->assign('fields', $this->getFields($idList));
            $this->assign('questionList', $questionList);
            $this->assign('usernameList', $usernameList);
            $this->assign('avatars', $avatars);
            $this->assign('displayNameList', $displayNameList);
            $this->assign('onlineInfo', $onlineInfo);
            $this->assign('page', $this->page);
            
            $activityShowLimit = OW::getConfig()->getValue('usearch', 'hide_user_activity_after');
            
            $this->assign('activityShowLimit', time() - ((int)$activityShowLimit)*24*60*60);


            if ( OW::getPluginManager()->isPluginActive('bookmarks') && OW::getUser()->isAuthenticated() )
            {
                $markList = BOOKMARKS_BOL_MarkDao::getInstance()->getMarkedListByUserId(OW::getUser()->getId(), $idList);
                $this->assign('bookmarkActive', TRUE);
                
                $contextActionList = array();
        
                foreach ( $idList as $id )
                {
                    $label = !empty($markList[$id]) ? OW::getLanguage()->text('bookmarks', 'unmark_toolbar_label') : OW::getLanguage()->text('bookmarks', 'mark_toolbar_label');
                    
                    $contextAction = new BASE_CMP_ContextAction();

                    $contextParentAction = new BASE_ContextAction();
                    $contextParentAction->setKey('userlist_menu');
                    $contextParentAction->setClass('ow_usearch_userlist_menu ow_newsfeed_context ');
                    $contextAction->addAction($contextParentAction);
                    
                    $action = new BASE_ContextAction();
                    $action->setKey('bookmark');
                    $action->setLabel($label);
                    $action->addAttribute('data-user-id', $id);
                    $action->setClass('ow_ulist_big_avatar_bookmark usearch_bookmark download');
                    $action->setUrl('javascript://');
                    $action->setParentKey($contextParentAction->getKey());
                    $action->setOrder(1);

                    $contextAction->addAction($action);
                    
                    $contextActionList[$id] = $contextAction->render();
                    
                }
        
                $this->assign('itemMenu', $contextActionList);
            }
        }

        $this->assign('list', $userList);
        $this->assign('bookmarkList', $markList);



    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->process($this->items);
    }
}