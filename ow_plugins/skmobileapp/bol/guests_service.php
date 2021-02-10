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

class SKMOBILEAPP_BOL_GuestsService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * New guests
     */
    const NEW_GUESTS = 0;

    /**
     * Viewed guests
     */
    const VIEWED_GUESTS = 1;

    /**
     * Find guest by id
     *
     * @param integer $id
     * @param integer $userId
     * @return array
     */
    function findGuestById($id, $userId = null)
    {
        $guestDao = OCSGUESTS_BOL_GuestDao::getInstance();
        $guestDto = $guestDao->findById($id);

        if ( $userId && $guestDto && $guestDto->userId != $userId )
        {
            return;
        }

        return $guestDto;
    }

    /**
     * Delete guest by id
     *
     * @param $id
     * @return void
     */
    function deleteGuestById($id)
    {
        $guestDao = OCSGUESTS_BOL_GuestDao::getInstance();
        $guestDao->deleteById($id);
    }

    /**
     * Mark all guests as read
     * 
     * @param integer $userId
     * @return void
     */
    function markAllGuestsAsRead($userId)
    {
        $guestDao = OCSGUESTS_BOL_GuestDao::getInstance();

        $query = "
            UPDATE
                `" . $guestDao->getTableName() . "`
            SET
                `viewed` = ?
            WHERE
                `userId` = ?
        ";

        OW::getDbo()->query($query, [
            self::VIEWED_GUESTS,          
            $userId
        ]);
    }
 
    /**
     * Find guests
     *
     * @param integer $userId
     * @param integer $limit
     * @return array
     */
    function findGuests($userId, $limit = 300)
    {
        $guestDao = OCSGUESTS_BOL_GuestDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('oc', 'guestId', [
            'method' => 'SKMOBILEAPP_BOL_GuestsService::findLatestBookmarksUserIdList'
        ]);

        $sql = 'SELECT `oc`.* FROM `' . $guestDao->getTableName() . '` as `oc` ' .  $queryParts['join'] . '
                WHERE ' .  $queryParts['where'] . ' AND `oc`.`userId` = :userId
                ORDER BY `viewed` ASC, `visitTimestamp` DESC
                LIMIT :limit';

        $guests = OW::getDbo()->queryForObjectList($sql, $guestDao->getDtoClassName(), [
            'userId' => $userId,
            'limit' => $limit
        ]);

        return $guests
            ? $this->formatGuestData($userId, $guests)
            : [];
    }

    /**
     * Format guest data
     *
     * @param integer $userId
     * @param array $guests
     * @return array
     */
    public function formatGuestData($userId, array $guests)
    {
        $processedGuests = [];
        $ids = [];

        // process guests
        foreach( $guests as $guest ) 
        {
            $ids[] = $guest->guestId;
            $processedGuests[$guest->guestId] = [
                'id' => (int) $guest->id,     
                'viewed' => (bool) $guest->viewed,
                'visitTimestamp' => (int) $guest->visitTimestamp,
                'visitDate' => UTIL_DateTime::formatDate($guest->visitTimestamp),
                'avatar' => null,
                'matchAction' => null,   
                'user' => [
                    'id' => (int) $guest->guestId,
                    'userName' => null
                ]
            ];
        }

        // load matches
        $mathList = SKMOBILEAPP_BOL_UserMatchActionDao::getInstance()->findUserMatchActionsByUserIdList($userId, $ids);

        foreach( $mathList as $matchAction ) 
        {
            $processedGuests[$matchAction->recipientId]['matchAction'] = [
                'id' => (int) $matchAction->id,
                'type' => $matchAction->type,
                'userId' => (int) $matchAction->recipientId,
                'isMutual' => boolval($matchAction->mutual),
                'createStamp' => (int) $matchAction->createStamp,
                'isRead' => boolval($matchAction->read),
                'isNew' => boolval($matchAction->new)
            ];
        }

        // load avatars
        $avatarList = BOL_AvatarService::getInstance()->findByUserIdList($ids);

        foreach( $avatarList as $avatar ) 
        {
            $processedGuests[$avatar->userId]['avatar'] = $this->getAvatarData($avatar, false);
        }

        // load user names
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($ids);

        foreach( $userNames as $userId => $userName ) 
        {
            $processedGuests[$userId]['user']['userName'] = $userName;
        }

        // load display names
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($ids);

        foreach( $displayNames as $userId => $displayName ) 
        {
            if ( $displayName ) 
            {
                $processedGuests[$userId]['user']['userName'] = $displayName;
            }
        }

        $data = [];
        foreach( $processedGuests as $guestData ) 
        {
            $data[] = $guestData;
        }

        $event = new OW_Event('skmobileapp.formatted_guests_data', [], $data);
        OW_EventManager::getInstance()->trigger($event);

        return $event->getData();
    }
}
