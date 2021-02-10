<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * VideoIm Service Class.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugins.videoim.bol
 * @since 1.8.1
 */
final class VIDEOIM_BOL_VideoImService
{
    /**
     * Seconds that we need to count from last
     * received notification before sent them to user
     */
    const GET_NOTIFICATIONS_DELAY_TIME = 5; // seconds

    const ERROR_CODE_NOT_LOGGED_IN = 1;
    const ERROR_CODE_SAME_PERSON = 2;
    const ERROR_CODE_NOT_ONLINE = 3;
    const ERROR_CODE_USER_BLOCKED = 4;
    const ERROR_CODE_USER_DECLINED = 5;
    const ERROR_CODE_USER_PRIVACY = 6;
    const ERROR_CODE_NO_PERMISSION = 7;
    const ERROR_CODE_NO_CREDITS_ENOUGH = 8;

    const TRACK_CREDITS_BOTH = 'both';
    const TRACK_CREDITS_INITIATOR = 'initiator';
    const TRACK_CREDITS_INTERLOCUTOR = 'interlocutor';

    /**
     * Class instance
     *
     * @var VIDEOIM_BOL_VideoImService
     */
    private static $classInstance;

    /**
     * Notification DAO
     *
     * @var VIDEOIM_BOL_NotificationDao
     */
    private $notificationDao;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->notificationDao = VIDEOIM_BOL_NotificationDao::getInstance();
    }

    /**
     * Is super moderator
     *
     * @param $userId
     * @return boolean
     */
    public function isSuperModerator($userId)
    {
        return BOL_AuthorizationService::getInstance()->isSuperModerator($userId);
    }

    /**
     * Returns class instance
     *
     * @return VIDEOIM_BOL_VideoImService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Track timed action
     *
     * @return void
     */
    public function trackTimedAction()
    {
        BOL_AuthorizationService::getInstance()->trackActionForUser(OW::getUser()->getId(), 'videoim', 'video_im_timed_call', array(
            'actionsGroupKey' => OW::getSession()->get('videoim.auth_actions_group_key')
        ));
    }

    /**
     * Track basic actions
     *
     * @param integer $recipientId
     * @return void
     */
    public function trackBasicActions($recipientId)
    {
        BOL_AuthorizationService::getInstance()->trackActionForUser($recipientId, 'videoim', 'video_im_call');
        BOL_AuthorizationService::getInstance()->trackActionForUser(OW::getUser()->getId(), 'videoim', 'video_im_receive');
    }

    /**
     * Get plugin build
     *
     * @return integer
     */
    public function getPluginBuild()
    {
        return OW::getPluginManager()->getPlugin('videoim')->getDto()->getBuild();
    }

    /**
     * Save auth actions group key
     *
     * @return void
     */
    public function saveAuthActionsGroupKey()
    {
        OW::getSession()->set('videoim.auth_actions_group_key', md5(OW::getUser()->getId() . time()));
    }

    /**
     * Get user avatar
     *
     * @param integer $userId
     * @param integer $size
     * @return string
     */
    public function getUserAvatar($userId, $size = 2)
    {
        $userAvatar = BOL_AvatarService::getInstance()->getAvatarUrl($userId, $size);

        return $userAvatar
            ? $userAvatar
            : BOL_AvatarService::getInstance()->getDefaultAvatarUrl($size);
    }

    /**
     * Delete expired notifications
     *
     * @return void
     */
    public function deleteExpiredNotifications()
    {
        $this->notificationDao->deleteExpiredNotifications();
    }

    /**
     * Get notifications
     *
     * @param integer $userId
     * @return array
     */
    public function getNotifications($userId)
    {
        $notifications = $this->notificationDao->findNotifications($userId);
        $notificationCount = count($notifications);

        if ( $notificationCount )
        {
            $lastSentNotification = $notifications[$notificationCount - 1];

            if ( time() - $lastSentNotification['createStamp'] <= self::GET_NOTIFICATIONS_DELAY_TIME )
            {
                return [];
            }
        }

        return $notifications;
    }

    /**
     * Add notification
     *
     * @param VIDEOIM_BOL_Notification $notificationDto
     * @return void
     */
    public function addNotification( VIDEOIM_BOL_Notification $notificationDto )
    {
        $this->notificationDao->save($notificationDto);
    }

    /**
     * Delete user notifications
     *
     * @param integer $userId
     * @param integer $recipientId
     * @return void
     */
    public function deleteUserNotifications($userId, $recipientId)
    {
        $this->notificationDao->deleteUserNotifications($userId, $recipientId);
    }

    /**
     * Mark accepted notifications
     *
     * @param $userId
     * @param $recipientId
     * @param $sessionId
     * @return void
     */
    public function markAcceptedNotifications($userId, $recipientId, $sessionId)
    {
        $this->notificationDao->markAcceptedNotifications($userId, $recipientId, $sessionId);
    }

    /**
     * Is credits timing call enough
     *
     * @param integer
     * @param boolean
     * @return boolean
     */
    public function isCreditsTimingCallEnough($userId, $isInitiator)
    {
        $configs = OW::getConfig()->getValues('videoim');
        $currentTrackCreditsType = $configs['track_credits_type'];

        if ( !OW::getUser()->isAdmin() )
        {
            if ( $currentTrackCreditsType === VIDEOIM_BOL_VideoImService::TRACK_CREDITS_BOTH ||
                ($currentTrackCreditsType === VIDEOIM_BOL_VideoImService::TRACK_CREDITS_INITIATOR && $isInitiator) ||
                ($currentTrackCreditsType === VIDEOIM_BOL_VideoImService::TRACK_CREDITS_INTERLOCUTOR && !$isInitiator) )
            {
                $result = OW::getEventManager()->call('usercredits.check_balance', array(
                    'userId' => $userId,
                    'pluginKey' => 'videoim',
                    'action' => 'video_im_timed_call'
                ));

                return null === $result ? true : $result;
            }
        }

        return true;
    }

    /**
     * Get credits timing call error
     *
     * @return string
     */
    public function getCreditsTimingCallError()
    {
        $message =  OW::getEventManager()->call('usercredits.error_message', array(
            'userId' => OW::getUser()->getId(),
            'pluginKey' => 'videoim',
            'action' => 'video_im_timed_call'
        ));

        // TODO: Remove this dirty fix for credits later
        if ( OW::getApplication()->getContext() == OW::CONTEXT_MOBILE )
        {
            $message = strip_tags($message);
        }

        return $message;
    }

    /**
     * Is allowed receive video request
     *
     * @param boolean $allowPromotion
     * @return array
     *      boolean is request allowed
     *      string  error message
     */
    public function isAllowedReceiveVideoImRequest($allowPromotion = false)
    {
        // check admin status
        if ( OW::getUser()->isAdmin() )
        {
            return array(
                true,
                null
            );
        }

        // check the recipient's balance
        $isBalanceEnough = $this->isCreditsTimingCallEnough(OW::getUser()->getId(), false); // added by me

        if ( !$isBalanceEnough )
        {
            return array(
                $allowPromotion,
                $this->getCreditsTimingCallError()
            );
        }

        // check permissions
        $isAuthorized = OW::getUser()->isAuthorized('videoim', 'video_im_receive');

        if ( $isAuthorized )
        {
            return array(
                true,
                null
            );
        }

        // check the promotion status
        $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus('videoim', 'video_im_receive');
        $isPromoted = !empty($promotedStatus['status'])
                && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

        if ( $isPromoted )
        {
            return array(
                $allowPromotion,
                $promotedStatus['msg']
            );
        }

        // by default the receiving video request is not allowed
        return array(
            false,
            $promotedStatus['msg']
        );
    }

    /**
     * Is allowed send video request
     *
     * @param integer $recipientId
     * @param boolean $allowPromotion
     * @return array
     *      boolean is request allowed
     *      string  error message
     *      integer error code = null
     */
    public function isAllowedSendVideoImRequest($recipientId, $allowPromotion = false)
    {
        $userId = OW::getUser()->getId();

        if ( !$userId )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_not_logged'),
                self::ERROR_CODE_NOT_LOGGED_IN
            );
        }

        // check recipient id
        if ( $recipientId == $userId )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_same_person'),
                self::ERROR_CODE_SAME_PERSON
            );
        }

        // check recipient's online status
        $isOnline = BOL_UserService::getInstance()->findOnlineUserById($recipientId);

        if ( !$isOnline )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_not_online'),
                self::ERROR_CODE_NOT_ONLINE
            );
        }

        // check the receive permission
        $authorizedReceiveCalls = OW::getUser()->isAuthorized('videoim', 'video_im_receive', array(
            'userId' => $recipientId
        ));

        if ( !$authorizedReceiveCalls )
        {
            // check the promotion status
            $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus('videoim', 'video_im_receive', array(
                'userId' => $recipientId
            ));

            $isPromoted = !empty($promotedStatus['status'])
                    && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

            if ( !$isPromoted )
            {
                return array(
                    false,
                    OW::getLanguage()->text('videoim', 'send_request_error_no_permission'),
                    self::ERROR_CODE_NO_PERMISSION
                );
            }
        }

        // check admin status
        if ( OW::getUser()->isAdmin() )
        {
            return array(
                true,
                null
            );
        }

        // check recipient's blocked status
        $isBlocked  = BOL_UserService::getInstance()->isBlocked($userId, $recipientId);

        if ( $isBlocked )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_user_blocked'),
                self::ERROR_CODE_USER_BLOCKED
            );
        }

        // check the receive permission
        $authorizedUsePreference = OW::getUser()->isAuthorized('videoim', 'video_im_preferences', array(
            'userId' => $recipientId
        ));

        $isDeclined = $authorizedUsePreference &&
                true === BOL_PreferenceService::getInstance()->getPreferenceValue('videoim_decline_calls', $recipientId);

        if ( $isDeclined )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_declined'),
                self::ERROR_CODE_USER_DECLINED
            );
        }

        // check recipient's privacy
        $eventParams = array(
            'action' => 'videoim_send_call_request',
            'ownerId' => $recipientId,
            'viewerId' => $userId
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_privacy'),
                self::ERROR_CODE_USER_PRIVACY
            );
        }

        // check the sender's balance
        $isBalanceEnough = $this->isCreditsTimingCallEnough(OW::getUser()->getId(), true);

        if ( !$isBalanceEnough )
        {
            return array(
                $allowPromotion,
                $this->getCreditsTimingCallError()
            );
        }

        // check permissions
        $isAuthorized = OW::getUser()->isAuthorized('videoim', 'video_im_call');

        if ( $isAuthorized )
        {
            return array(
                true,
                null
            );
        }

        // check the promotion status
        $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus('videoim', 'video_im_call');
        $isPromoted = !empty($promotedStatus['status'])
                && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

        if ( $isPromoted )
        {
            return array(
                $allowPromotion,
                $promotedStatus['msg']
            );
        }

        // by default the sending video request is not allowed
        return array(
            false,
            $promotedStatus['msg']
        );
    }

    /**
     * Is allowed send video request for application
     *
     * @param integer $recipientId
     * @param boolean $allowPromotion
     * @return array
     *      boolean is request allowed
     *      string  error message
     *      integer error code = null
     */
    public function isAllowedSendVideoImRequestForApplication($recipientId, $allowPromotion = false)
    {
        $userId = OW::getUser()->getId();

        if ( !$userId )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_not_logged'),
                self::ERROR_CODE_NOT_LOGGED_IN
            );
        }

        // check recipient id
        if ( $recipientId == $userId )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_same_person'),
                self::ERROR_CODE_SAME_PERSON
            );
        }

        // check recipient's online status
        $isOnline = BOL_UserService::getInstance()->findOnlineUserById($recipientId);

        if ( !$isOnline )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_not_online'),
                self::ERROR_CODE_NOT_ONLINE
            );
        }

        // check the receive permission
        $authorizedReceiveCalls = OW::getUser()->isAuthorized('videoim', 'video_im_receive', array(
            'userId' => $recipientId
        ));

        if ( !$authorizedReceiveCalls )
        {
            // check the promotion status
            $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus('videoim', 'video_im_receive', array(
                'userId' => $recipientId
            ));

            $isPromoted = !empty($promotedStatus['status'])
                && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

            if ( !$isPromoted || !$allowPromotion ) // added by me
            {
                return array(
                    false,
                    OW::getLanguage()->text('videoim', 'send_request_error_no_permission'),
                    self::ERROR_CODE_NO_PERMISSION
                );
            }
        }

        // check admin status
        if ( OW::getUser()->isAdmin() )
        {
            return array(
                true,
                null
            );
        }

        // check recipient's blocked status
        $isBlocked  = BOL_UserService::getInstance()->isBlocked($userId, $recipientId);

        if ( $isBlocked )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_user_blocked'),
                self::ERROR_CODE_USER_BLOCKED
            );
        }

        // check the receive permission
        $authorizedUsePreference = OW::getUser()->isAuthorized('videoim', 'video_im_preferences', array(
            'userId' => $recipientId
        ));

        $isDeclined = $authorizedUsePreference &&
            true === BOL_PreferenceService::getInstance()->getPreferenceValue('videoim_decline_calls', $recipientId);

        if ( $isDeclined )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_declined'),
                self::ERROR_CODE_USER_DECLINED
            );
        }

        // check recipient's privacy
        $eventParams = array(
            'action' => 'videoim_send_call_request',
            'ownerId' => $recipientId,
            'viewerId' => $userId
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return array(
                false,
                OW::getLanguage()->text('videoim', 'send_request_error_privacy'),
                self::ERROR_CODE_USER_PRIVACY
            );
        }

        $isBalanceEnough = $this->isCreditsTimingCallEnough($recipientId, false);

//        // check the recipient's balance
//        $result = OW::getEventManager()->call('usercredits.check_balance', array(
//            'userId' => $recipientId,
//            'pluginKey' => 'videoim',
//            'action' => 'video_im_timed_call'
//        ));
//        $isBalanceEnough =  null === $result ? true : $result;

        if ( !$isBalanceEnough )
        {
            return array(
                $allowPromotion,
                OW::getLanguage()->text('videoim', 'send_request_error_no_permission'),
                self::ERROR_CODE_NO_CREDITS_ENOUGH // added by me
            );
        }

        // check permissions
        $isAuthorized = OW::getUser()->isAuthorized('videoim', 'video_im_call');

        if ( $isAuthorized )
        {
            return array(
                true,
                null
            );
        }

        // check the promotion status
        $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus('videoim', 'video_im_call');
        $isPromoted = !empty($promotedStatus['status'])
            && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

        if ( $isPromoted )
        {
            return array(
                $allowPromotion,
                $promotedStatus['msg']
            );
        }

        // by default the sending video request is not allowed
        return array(
            false,
            $promotedStatus['msg']
        );
    }

    /**
     * Get timed call price
     *
     * @return integer
     */
    public function getTimedCallPrice()
    {
        $data = array(
            'pluginKey' => 'videoim',
            'action' => 'video_im_timed_call',
            'userId' => OW::getUser()->getId(),
        );

        $event = new OW_Event('usercredits.action_info', $data);
        OW::getEventManager()->trigger($event);

        $result = $event->getData();

        return isset($result['price'], $result['disabled']) && $result['price'] <> 0 && !$result['disabled']
            ? (int) $result['price']
            : 0;
    }

    /**
     * is demo mode activated
     */
    public function isDemoModeActivated()
    {
        if ( stristr(OW_URL_HOME, 'demo.skadate.com') !== false &&
             stristr(OW_URL_HOME, 'demo.skadate.com/bar') === false )
        {
            return true;
        }

        return false;
    }
}