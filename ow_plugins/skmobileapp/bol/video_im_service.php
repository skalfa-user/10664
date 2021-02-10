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

class SKMOBILEAPP_BOL_VideoImService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Get notifications
     *
     * @param integer $userId
     * @return array
     */
    public function getNotifications($userId)
    {
        $ids = [];
        $processedNotifications = [];

        $notifications = VIDEOIM_BOL_VideoImService::getInstance()->getNotifications($userId);

        foreach ( $notifications as $index => $notification )
        {
            $decoded = json_decode($notification['notification'], true);

            if ( $notification['accepted'] )
            {
                continue;
            }

            if ( !isset($ids[$notification['userId']]) )
            {
                $ids[$notification['userId']] = [];
            }

            $ids[$notification['userId']][] = $notification['id'];

            $processedNotifications[$notification['id']] = [
                'id' => (int) $notification['id'],
                'type' => $decoded['type'],
                'notification' => $decoded,
                'sessionId' => $notification['sessionId'],
                'avatar' => null,
                'user' => [
                    'id' => (int) $notification['userId'],
                    'userName' => null
                ]
            ];
        }
      
        // Sort notification array to ensure 'candidate' notifications are in last places
        uasort( $processedNotifications, function( $left, $right ) {
            return $right['type'] == 'candidate' || $right['type'] == 'bye' ? -1 : 1;
        });

        // load avatars
        $avatarList = BOL_AvatarService::getInstance()->findByUserIdList(array_keys($ids));

        foreach ( $avatarList as $avatar ) {
            foreach ( $ids[$avatar->userId] as $notificationId ) {
                $processedNotifications[$notificationId]['avatar'] = $this->getAvatarData($avatar, false);
            }
        }

        // load user names
        $userNames = BOL_UserService::getInstance()->getUserNamesForList(array_keys($ids));

        foreach ( $userNames as $userId => $userName )
        {
            foreach ( $ids[$userId] as $notificationId ) {
                $processedNotifications[$notificationId]['user']['userName'] = $userName;
            }
        }

        // load display names
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList(array_keys($ids));

        foreach ( $displayNames as $userId => $displayName )
        {
            if ( $displayName )
            {
                foreach ( $ids[$userId] as $notificationId )
                {
                    $processedNotifications[$notificationId]['user']['userName'] = $displayName;
                }
            }
        }

        $data = [];
        foreach ( $processedNotifications as $notificationData )
        {
            $data[] = $notificationData;
        }
        
        $event = new OW_Event('skmobileapp.formatted_videoim_notifications_data', [], $data);
        OW_EventManager::getInstance()->trigger($event);

        return $event->getData();
    }
}
