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
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks.bol
 * @since 1.0
 */
class WINKS_BOL_Service
{
    CONST EMAIL_SEND = 'send';
    CONST EMAIL_BACK = 'back';

    CONST LIMIT_TIMESTAMP = 604800; // week
    CONST EVENT_ON_BEFORE_FIND_WINK_LIST_BY_STATUS = 'winks.on_before_find_wink_list_by_status';
    
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $winksDao;

    private function __construct()
    {
        $this->winksDao = WINKS_BOL_WinksDao::getInstance();
    }

    public function isLimited( $userId, $partnerId )
    {
        return $this->winksDao->isLimited($userId, $partnerId);
    }
    
    public function sendWink( $userId, $partnerId )
    {
        if ( empty($userId) || empty($partnerId) )
        {
            return FALSE;
        }

        if ( ($wink = $this->findByUserIdAndPartnerId($userId, $partnerId)) === NULL )
        {
            $wink = new WINKS_BOL_Winks();
        }
        
        $activeModes = json_decode(OW::getConfig()->getValue('mailbox', 'active_modes'));

        $wink->setUserId($userId);
        $wink->setPartnerId($partnerId);
        $wink->setTimeStamp(time());
        $wink->setStatus(WINKS_BOL_WinksDao::STATUS_WAIT);
        $wink->setViewed(0);
        $wink->setConversationId(0);
        $wink->setMessageType((in_array('chat', $activeModes) ? 'chat' : 'mail'));
        $wink->setWinkback(0);
        $this->winksDao->save($wink);

        return TRUE;
    }

    public function findByUserIdAndPartnerId( $userId, $partnerId )
    {
        return $this->winksDao->findByUserIdAndPartnerId($userId, $partnerId);
    }
    
    public function countWinksForUser( $partnerId, $status = NULL, $viewed = NULL, array $activeModes = array())
    {
        $options = array(
            'partnerId' => $partnerId,
            'status' => $status,
            'viewed' => $viewed,
            'activeModes' => $activeModes
        );

        $eventParams = $this->getQueryFilter('winks.on_before_count_winks_for_user', $options);

        return $this->winksDao->countWinksForUser($partnerId, $status, $viewed, $activeModes, $eventParams);
    }
    
    public function countWinksForPartner( $userId, $status = NULL, $viewed = NULL )
    {
        return $this->winksDao->countWinksForPartner($userId, $status, $viewed);
    }
    
    public function countWinkBackedByUserId( $userId, array $activeModes = array() )
    {
        $options = array(
            'userId' => $userId,
            'activeModes' => $activeModes
        );

        $eventParams = $this->getQueryFilter('winks.on_before_count_wink_backed_by_userId', $options);

        return $this->winksDao->countWinkBackedByUserId($userId, $activeModes, $eventParams);
    }

    public function findWinkList( $partnerId, $first, $limit, array $activeModes = array() )
    {
        $options = array(
            'partnerId' => $partnerId,
            'first' => $first,
            'limit' => $limit,
            'activeModes' => $activeModes
        );

        $eventParams = $this->getQueryFilter('winks.on_before_find_wink_list', $options);

        return $this->winksDao->findWinkList($partnerId, $first, $limit, $activeModes, $eventParams);
    }

    public function findWinkListByStatus( $partnerId, $first, $limit, $status )
    {
        $options = array(
            'partnerId' => $partnerId,
            'first' => $first,
            'limit' => $limit,
            'status' => $status
        );

        $eventParams = $this->getQueryFilter(self::EVENT_ON_BEFORE_FIND_WINK_LIST_BY_STATUS, $options);
        $winks = $this->winksDao->findWinkListByStatus($partnerId, $first, $limit, $status, $eventParams);

        if ( $winks )
        {
            $authors = array();
            $winkList = array();
            
            // process winks
            foreach($winks as $wink)
            {
                if ( !in_array($wink->userId, $authors) )
                {
                    array_push($authors, $wink->userId);
                }
                
                $winkList[] = array(
                    'id' => $wink->id,
                    'userId' => $wink->userId,
                    'date' => UTIL_DateTime::formatDate($wink->timestamp),
                    'timestamp' => $wink->timestamp,
                    'viewed' => $wink->getViewed()
                );
            }

            $onlineUsers = BOL_UserService::getInstance()->findOnlineStatusForUserList($authors);
            $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($authors);
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($authors, true, false, false, false);

            // complete users info
            foreach($winkList as &$wink)
            {
                $displayName = $displayNames[$wink['userId']];

                $wink['userOnline'] = !empty($onlineUsers[$wink['userId']]);
                $wink['text'] = strip_tags(OW::getLanguage()->text('winks', 'console_wink_wait_item', array('userUrl' => null, 'displayName' => $displayName)));
                $wink['avatar'] = $avatars[$wink['userId']]['src'];
                $wink['displayName'] = $displayName;
            }

            return $winkList;
        }

        return array();
    }

    public function markViewedByIds( $winksIds )
    {
        return $this->winksDao->markViewedByIds($winksIds);
    }
    
    public function findWinkByUserIdAndPartnerId( $userId, $partnerId )
    {
        return $this->winksDao->findWinkByUserIdAndPartnerId($userId, $partnerId);
    }
    
    public function findWinkById( $id )
    {
        return $this->winksDao->findById($id);
    }
    
    public function findExpiredDate( $timeStamp )
    {
        return $this->winksDao->findExpiredDate($timeStamp);
    }
    
    public function deleteWinkById( $id )
    {
        return $this->winksDao->deleteById($id);
    }
    
    public function deleteWinkByUserId( $userId )
    {
        return $this->winksDao->deleteWinkByUserId($userId);
    }
    
    public function setWinkback( $winkId, $flag = TRUE )
    {
        if ( empty($winkId) || ($wink = $this->winksDao->findById($winkId)) === NULL )
        {
            return FALSE;
        }
        
        $wink->setWinkback($flag);
        $this->winksDao->save($wink);
        
        return TRUE;
    }

    public function isWinkBacked( $winkId )
    {
        if ( empty($winkId) || ($wink = $this->findWinkById($winkId)) === null )
        {
            return false;
        }

        return $wink->getWinkback() == 1;
    }
    
    public function isCompleted( $userId, $partnerId )
    {
        return $this->winksDao->isCompleted($userId, $partnerId);
    }
    
    public function setStatusByUserId( $userId, $status )
    {
        return $this->winksDao->setStatusByUserId($userId, $status);
    }

    public function getActiveModes()
    {
        if ( OW::getPluginManager()->getPlugin('winks')->getDto()->getBuild() >= 14 )
        {
            return json_decode(OW::getConfig()->getValue('mailbox', 'active_modes'));
        }

         return array();
    }

    public function sendWinkEmailNotification( $userId, $partnerId, $winkType )
    {
        if ( empty($userId) || empty($partnerId) ||
            ($user = BOL_UserService::getInstance()->findUserById($userId)) === null ||
            ($partner = BOL_UserService::getInstance()->findUserById($partnerId)) === null )
        {
            return false;
        }

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId, $partnerId), true, true, true, false);

        switch ( $winkType )
        {
            case self::EMAIL_SEND:
                $subjectKey = 'wink_send_email_subject';
                $subjectArr = array('displayname' => $avatar[$userId]['title']);

                $textContentKey = 'wink_send_email_text_content';
                $htmlContentKey = 'wink_send_email_html_content';
                $contentArr = array(
                    'src' => $avatar[$userId]['src'],
                    'displayname' => $avatar[$userId]['title'],
                    'url' => $avatar[$userId]['url'],
                    'home_url' => OW_URL_HOME
                );
                break;
            case self::EMAIL_BACK:
            default:
                $subjectKey = 'wink_back_email_subject';
                $subjectArr = array('displayname' => $avatar[$userId]['title']);

                $textContentKey = 'wink_back_email_text_content';
                $htmlContentKey = 'wink_back_email_html_content';
                $contentArr = array(
                    'src' => $avatar[$userId]['src'],
                    'displayname' => $avatar[$userId]['title'],
                    'url' => $avatar[$userId]['url'],
                    'conversation_url' => OW::getRouter()->urlForRoute('mailbox_messages_default')
                );
                break;
        }

        $languageService = BOL_LanguageService::getInstance();
        $defaultLanguageId = $languageService->findDefault()->getId();
        $mail = OW::getMailer()->createMail();

        $mail->addRecipientEmail($partner->email);

        $mail->setSubject($languageService->getText($defaultLanguageId, 'winks', $subjectKey, $subjectArr));
        $mail->setTextContent($languageService->getText($defaultLanguageId, 'winks', $textContentKey, $contentArr));
        $mail->setHtmlContent($languageService->getText($defaultLanguageId, 'winks', $htmlContentKey, $contentArr));

        try
        {
            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            OW::getLogger('winks.send_notification')->addEntry(json_encode($e));
        }
    }

    /**
     * Get query filter
     *
     * @param $eventName
     * @param array $options
     *
     * @return array
     */
    public function getQueryFilter( $eventName, array $options = array() )
    {
        $event = new BASE_CLASS_QueryBuilderEvent($eventName, $options);

        OW::getEventManager()->trigger($event);

        return array(
            'join' => $event->getJoin(),
            'where' => $event->getWhere(),
            'order' => $event->getOrder()
        );
    }
}
