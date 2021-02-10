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
 * User search service class.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.usearch.bol
 * @since 1.5.3
 */
final class USEARCH_BOL_Service
{
    const LIST_ORDER_LATEST_ACTIVITY = 'latest_activity';
    const LIST_ORDER_NEW = 'new';
    const LIST_ORDER_MATCH_COMPATIBILITY = 'match_compatibility';
    const LIST_ORDER_DISTANCE = 'distanse';
    const LIST_ORDER_WITHOUT_SORT = 'without_sort';
    
    /**
     * Class instance
     *
     * @var USEARCH_BOL_Service
     */
    private static $classInstance;

    private $searchDao;
    /**
     * Class constructor
     */
    private function __construct() {
        
        $this->searchDao = USEARCH_BOL_SearchDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return USEARCH_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getOrderTypes()
    {
        $data = array();
        
        $config = OW::getConfig()->getValues('usearch');
        
        if ( !empty($config['order_latest_activity']) )
        {
            $data[USEARCH_BOL_Service::LIST_ORDER_LATEST_ACTIVITY] = USEARCH_BOL_Service::LIST_ORDER_LATEST_ACTIVITY;
        }
        
        if ( !empty($config['order_recently_joined']) )
        {
            $data[USEARCH_BOL_Service::LIST_ORDER_NEW] = USEARCH_BOL_Service::LIST_ORDER_NEW;
        }
        
        if ( !empty($config['order_match_compatibitity']) )
        {
            $data[USEARCH_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY] = USEARCH_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY;
        }
        
        if ( !empty($config['order_distance']) )
        {
            $data[USEARCH_BOL_Service::LIST_ORDER_DISTANCE] = USEARCH_BOL_Service::LIST_ORDER_DISTANCE;
        }
        
        $event = new OW_Event('usearch.get_list_order_types', array(), $data);
        
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }
    
    public function getPositionList()
    {
        return array(  'position1', 'position2', 'position3',  'position4'  );
    }

    public function saveQuickSerchQuestionPosition( array $value )
    {
        OW::getConfig()->saveConfig( 'usearch', 'quick_search_fields', json_encode($value) );
    }

    public function getQuickSerchQuestionNames()
    {
        $positions = $this->getQuickSerchQuestionPosition();

        $result = array();

        foreach ( $positions as $value )
        {
            if ( !empty($value) )
            {
                $result[] = $value;
            }
        }

        return $result;
    }

    public function getQuickSerchQuestionPosition()
    {
        if ( !OW::getConfig()->configExists('usearch', 'quick_search_fields') )
        {
            OW::getConfig()->addConfig('usearch', 'quick_search_fields', '');
        }

        $questions = OW::getConfig()->getValue('usearch', 'quick_search_fields');
        
        $allowedFieldsList = $this->getAllowedQuickSerchQuestionNames();

        $result = array();

        if ( !empty($questions) )
        {
            $list = json_decode($questions, true);

            if ( !is_array($list) )
            {
                $result = array();
            }

            $tmpList = $allowedFieldsList;
            $positionList = $this->getPositionList();
            foreach (  $positionList as $position )
            {
                $question = array_shift($list);

                if ( in_array($question, $tmpList) )
                {
                    $result[$position] = $question;
                    unset($tmpList[$question]);
                }
                else
                {
                    $result[$position] = null;
                }
            }
        }

        if ( empty($result) )
        {
            $result = array('position1' => 'sex', 'position2' => 'match_sex', 'position3' => 'birthdate', 'position4' => null);
            
            if ( OW::getPluginManager()->isPluginActive('googlelocation') )
            {
                $result['position4'] = 'googlemap_location';
            }
        }

        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        
        if ( count($accountTypes) > 1 )
        {
            $result['position1'] = 'sex';
            $result['position2'] = 'match_sex';
        }

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($result);
        $searchQuestionList = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType('all');
        
        $searchList = array();
        
        foreach ( $searchQuestionList as $question )
        {
            $searchList[$question['name']] = $question;
        }
        
        foreach ( $result as $key => $item )
        {
            if ( empty($questions[$item]) )
            {
                $result[$key] = null;
            }
            
            if ( empty($searchList[$item]) && !in_array($item, array('sex', 'match_sex')) )
            {
                $result[$key] = null;
            }
            
            if ( count($accountTypes) <= 1 && in_array($item, array('sex', 'match_sex')) )
            {
                $result[$key] = null;
            }
        }

        return $result;
    }
    
    public function getAllowedQuickSerchQuestionNames()
    {
        $accountType2Question = BOL_QuestionService::getInstance()->getAccountTypesToQuestionsList();

        $searchQuestionList = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType('all');
        
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        $accountTypeList = array();
        
        foreach( $accountTypes as $item )
        {
            $accountTypeList[$item->name] = $item->name;
        }
        
        $searchQuestionNameList = array();
        $questionList = array();
        
        foreach ( $searchQuestionList as $key => $question )
        {
            $searchQuestionNameList[$question['name']] = $question['name'];
        }
        
        foreach( $accountType2Question as $dto )
        {
            if (  in_array($dto->questionName, $searchQuestionNameList) )
            {
                $questionList[$dto->accountType][$dto->questionName] = $dto->questionName;
            }
        }
        
        foreach ( $questionList as $accountType => $questions )
        {
            if ( in_array($accountType, $accountTypeList) )
            {
                if ( empty($result) )
                {
                    $result = $questions;
                }
                else
                {
                    $result = array_intersect($result, $questions);
                }
            }
        }

        $resultList = array();
        
        foreach ( $result as $key => $value )
        {
            $resultList[$value] = $value;
        }

        return $resultList;
    }

    public function getAccounTypeByGender( $gender )
    {
        $accountType2Gender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        if ( !empty($accountType2Gender) )
        {
            foreach ( $accountType2Gender as $item )
            {
                if ( $item->genderValue == $gender )
                {
                    return $item->accountType;
                }
            }
        }

        return null;
    }

    public function getGenderByAccounType( $accountType )
    {
        $accountType2Gender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        if ( !empty($accountType2Gender) )
        {
            foreach ( $accountType2Gender as $item )
            {
                if ( $item->accountType == $accountType )
                {
                    return $item->genderValue;
                }
            }
        }

        return null;
    }

    public function updateSearchData( $data )
    {
        if ( empty($data) )
        {
            return array();
        }

        if ( isset($data['accountType']) )
        {
            unset($data['accountType']);
        }

        $accountType = null;
        
        if ( !empty($data['match_sex']) )
        {
            $accountType = $this->getAccounTypeByGender($data['match_sex']);
        }

        if ( !empty($accountType) )
        {
            $data['accountType'] = $accountType;
        }

        return $data;
    }

    public function updateQuickSearchData( $data )
    {
        if ( empty($data) )
        {
            return array();
        }

        $questionNames = array_keys($data);

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNames);

        foreach ( $questions as $question )
        {
            if ( $question->presentation == BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX )
            {
                if ( !empty($question->name) )
                {
                    if( !is_array($data[$question->name]) && !empty($data[$question->name]) )
                    {
                        $data[$question->name] = array($data[$question->name]);
                    }
                }
            }
        }

        return $data;
    }
    
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        return $this->searchDao->getUserIdList($listId, $first, $count, $excludeList);
    }    
    
    public function getSearchResultList( $listId, $listType, $from, $count, $excludeList = array() )
    {
        if ( empty($excludeList) )
        {
            $excludeList = array();
        }
        
        if ( OW::getUser()->isAuthenticated() )
        {
            $excludeList[] = OW::getUser()->getId();
        }
        
        $userIdList = $this->getUserIdList($listId, 0, BOL_SearchService::USER_LIST_SIZE, $excludeList);
        //$userIdList = BOL_SearchService::getInstance()->getUserIdList($listId, 0, BOL_SearchService::USER_LIST_SIZE, $excludeList);
        
        if ( empty($userIdList) )
        {
            return array();
        }

        switch($listType)
        {
            case USEARCH_BOL_Service::LIST_ORDER_NEW:
                
                return $this->searchDao->findSearchResultListOrderedByRecentlyJoined( $userIdList, $from, $count );
                
                break;
                
            case USEARCH_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY:

                if ( OW::getPluginManager()->isPluginActive('matchmaking') && OW::getUser()->isAuthenticated() )
                {
                    $users = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
                    
                    $list = array();
                    
                    foreach ( $users as $user )
                    {
                        $list[$user->id] = $user;        
                    }
                    
                    $result = MATCHMAKING_BOL_Service::getInstance()->findCompatibilityByUserIdList( OW::getUser()->getId(), $userIdList, $from, $count);
                    $usersList = array();
                    
                    foreach ( $result as $item )
                    {
                        $usersList[$item['userId']] = $list[$item['userId']];
                    }
                    
                    return $usersList;
                }
                
                break;
                
            case USEARCH_BOL_Service::LIST_ORDER_DISTANCE:

                if ( OW::getPluginManager()->isPluginActive('googlelocation') && OW::getUser()->isAuthenticated() )
                {
                    
                    
                    $result = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('googlemap_location'));
                    
                    if ( !empty($result[OW::getUser()->getId()]['googlemap_location']['json']) )
                    {
                        $location = $result[OW::getUser()->getId()]['googlemap_location'];
                        
                        return GOOGLELOCATION_BOL_LocationService::getInstance()->getListOrderedByDistance( $userIdList, $from, $count, $location['latitude'], $location['longitude'] );
                    }
                }
                
                break;  
                
            default:                
                $params = array(
                    'idList' => $userIdList,
                    'orderType' => $listType,
                    'from' => $from,
                    'count' => $count,
                    'userId' => OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : 0
                );
                
                $event = new OW_Event('usearch.get_ordered_list', $params, array());
                OW::getEventManager()->trigger($event);
                
                $data = $event->getData();
                
                if ( !empty($data) && is_array($data) )
                {
                    return $data;
                }
        }

        return $this->searchDao->findSearchResultListByLatestActivity( $userIdList, $from, $count );
    }
    
    public function getAccountTypeToQuestionList()
    {
        $accounType2QuestionList = BOL_QuestionService::getInstance()->getAccountTypesToQuestionsList();
        
        $list = array();
        /* @var $dto BOL_QuestionToAccountType */
        foreach ( $accounType2QuestionList as $dto )
        {
            $list[$dto->accountType][$dto->questionName] = $dto->questionName;
        }
        
        return $list;
    }
    
    public function getSearchResultMenu($order) {
        $lang = OW::getLanguage();
        $router = OW::getRouter();
        $config = OW::getConfig()->getValues('usearch');
        
        $items = array();
        
        if ( $config['order_latest_activity'] )
        {
            $item = array(
                'label' => $lang->text('usearch', 'latest'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => USEARCH_BOL_Service::LIST_ORDER_LATEST_ACTIVITY)),
                'isActive' => $order == USEARCH_BOL_Service::LIST_ORDER_LATEST_ACTIVITY);
            
            array_push($items, $item);
        }
        
        if ( $config['order_recently_joined'] )
        {
            $item = array(
                'label' => $lang->text('usearch', 'recently_joined'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => USEARCH_BOL_Service::LIST_ORDER_NEW)),
                'isActive' => $order == USEARCH_BOL_Service::LIST_ORDER_NEW);
            
            array_push($items, $item);
        }
        
        if ( OW::getPluginManager()->isPluginActive('matchmaking') && $config['order_match_compatibitity'] && OW::getUser()->isAuthenticated() )
        {
            $item = array(
                'label' => $lang->text('usearch', 'match_compatibitity'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => USEARCH_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY)),
                'isActive' => $order == USEARCH_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY);
            
            array_push($items, $item);
        }

        if ( OW::getPluginManager()->isPluginActive('googlelocation') && $config['order_distance'] && OW::getUser()->isAuthenticated() )
        {
            $item = array(
                'label' => $lang->text('usearch', 'distance'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => USEARCH_BOL_Service::LIST_ORDER_DISTANCE)),
                'isActive' => $order == USEARCH_BOL_Service::LIST_ORDER_DISTANCE);
            
            array_push($items, $item);
            
        }
        
        $event = new OW_Event('usearch.get_list_order_menu', array('order' => $order), $items);
        
        OW::getEventManager()->trigger($event);

        $items = $event->getData();
        
        return $items;
    }
    
    public function findUserIdListByQuestionValues( $questionValues, $first, $count, $isAdmin = false, $aditionalParams = array() )
    {
        $first = (int) $first;
        $count = (int) $count;

        $data = array(
            'data' => $questionValues,
            'first' => $first,
            'count' => $count,
            'isAdmin' => $isAdmin,
            'aditionalParams' => $aditionalParams
        );

        $event = new OW_Event("base.question.before_user_search", $data, $data);

        OW_EventManager::getInstance()->trigger($event);

        $data = $event->getData();

        return $this->searchDao->findUserIdListByQuestionValues($data['data'], $data['first'], $data['count'], $data['isAdmin'], $data['aditionalParams']);
    }
}