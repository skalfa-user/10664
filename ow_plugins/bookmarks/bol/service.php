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
 * Bookmarks Service
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.bol
 * @since 1.0
 */
class BOOKMARKS_BOL_Service
{
    CONST LIST_LATEST = 'latest';
    CONST LIST_ONLINE = 'online';
    CONST LIST_NOTIFY = 'notify';
    
    CONST COUNT_USER_IN_NOTIFY = 7;
    CONST COUNT_CRON_USER = 100;

    const EVENT_ON_BEFORE_FIND_BOOKMARKS_USER_ID_LIST = 'bookmarks.find_bookmarks_user_id_list';
    
    private static $classInstance;

    /**
     * 
     * @return BOOKMARKS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    /* @var $markDto BOOKMARKS_BOL_MarkDao */
    private $markDto;
    private $notifyLogDao;

    private function __construct()
    {
        $this->markDto = BOOKMARKS_BOL_MarkDao::getInstance();
        $this->notifyLogDao = BOOKMARKS_BOL_NotifyLogDao::getInstance();
    }
    
    public function isMarked( $userId, $markUserId )
    {
        return $this->markDto->isMarked($userId, $markUserId);
    }
    
    public function mark( $userId, $markUserId )
    {
        return $this->markDto->mark($userId, $markUserId);
    }
    
    public function unmark( $userId, $markUserId )
    {
        return $this->markDto->unmark($userId, $markUserId);
    }
    
    public function findBookmarksCount( $userId, $list = self::LIST_LATEST )
    {
        $options = [
            'userId' => $userId,
            'list' => $list
        ];
        $eventParams = $this->getQueryFilter(self::EVENT_ON_BEFORE_FIND_BOOKMARKS_USER_ID_LIST, $options);

        return $this->markDto->findBookmarksCount($userId, $list, $eventParams);
    }
    
    public function findBookmarksUserIdList( $userId, $first, $count, $list = self::LIST_LATEST )
    {
        $options = [
            'userId' => $userId,
            'list' => $list,
            'first' => 0,
            'count' => self::COUNT_USER_IN_NOTIFY
        ];
        $eventParams = $this->getQueryFilter(self::EVENT_ON_BEFORE_FIND_BOOKMARKS_USER_ID_LIST, $options);

        return $this->markDto->findBookmarksUserIdList($userId, $first, $count, $list, $eventParams);
    }
    
    public function cleareExpiredNotifyLog( $timestamp )
    {
        return $this->notifyLogDao->cleareExpiredNotifyLog($timestamp);
    }

    public function findUserIdListForNotify( $timeStamp, $first, $count )
    {
        return $this->notifyLogDao->findUserIdListForNotify($timeStamp, $first, $count);
    }
    
    public function getMarkedListByUserId( $userId, $userIdList )
    {
        return $this->markDto->getMarkedListByUserId($userId, $userIdList);
    }
    
    public function sendNotifyForUser( $userId )
    {
        if ( empty($userId) )
        {
            return FALSE;
        }

        $options = [
            'userId' => $userId,
            'list' => self::LIST_NOTIFY,
            'first' => 0,
            'count' => self::COUNT_USER_IN_NOTIFY
        ];

        $eventParams = $this->getQueryFilter(self::EVENT_ON_BEFORE_FIND_BOOKMARKS_USER_ID_LIST, $options);

        $bookmarkUserIdList = $this->markDto->findBookmarksUserIdList($userId, 0, self::COUNT_USER_IN_NOTIFY, self::LIST_NOTIFY, $eventParams);
        
        if ( empty($bookmarkUserIdList) )
        {
            return TRUE;
        }
        
        $cmp = new BOOKMARKS_CMP_Notify($userId, $bookmarkUserIdList);
        $cmp->sendNotification();
        
        return TRUE;
    }
    
    public function notifyLogSave( $userId )
    {
        $this->notifyLogDao->notifyLogSave($userId);
    }


    public function getQueryFilter( $eventName, array $options = array() )
    {
        $event = new BASE_CLASS_QueryBuilderEvent($eventName, $options);

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('b', 'markUserId', array(
            'method' => 'BOOKMARKS_BOL_Service::findBookmarksUserList'
        ));

        OW::getEventManager()->trigger($event);

        return array(
            'join' => $event->getJoin() . ' ' . $queryParts['join'],
            'where' => $event->getWhere() . ' AND ' . $queryParts['where'],
            'order' => $event->getOrder() . ' ' . $queryParts['order']
        );
    }
}
