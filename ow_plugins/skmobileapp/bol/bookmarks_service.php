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

class SKMOBILEAPP_BOL_BookmarksService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Get marked list by user id
     *
     * @param integer $userId
     * @param array $markIdList
     * @return array
     */
    public function getMarkedListByUserId( $userId, $markIdList )
    {
        $bookmarksDao = BOOKMARKS_BOL_MarkDao::getInstance();

        $sql = 'SELECT `id`, `' . BOOKMARKS_BOL_MarkDao::USER_ID . '`, `' . BOOKMARKS_BOL_MarkDao::MARK_USER_ID . '`
            FROM `' . $bookmarksDao->getTableName() . '`
            WHERE `' . BOOKMARKS_BOL_MarkDao::USER_ID . '` = :userId AND
                `' . BOOKMARKS_BOL_MarkDao::MARK_USER_ID . '` IN (' . implode(',', array_map('intval', $markIdList)) . ');';

        $result = OW::getDbo()->queryForList($sql, [
            'userId' => $userId
        ]);

        $out = [];
        foreach ( $result as $bookmark )
        {
            $out[] = [
                'id' => (int) $bookmark['id'],
                'user' => (int) $bookmark[BOOKMARKS_BOL_MarkDao::MARK_USER_ID]
            ];
        }

        return $out;
    }

    /**
     * Find latest bookmarks user id list
     *
     * @param integer $userId
     * @param integer $limit
     * @return array
     */
    public function findLatestBookmarksUserIdList( $userId, $limit )
    {
        $bookmarksDao = BOOKMARKS_BOL_MarkDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('b', BOOKMARKS_BOL_MarkDao::MARK_USER_ID, [
            'method' => 'SKMOBILEAPP_BOL_BookmarksService::findLatestBookmarksUserIdList'
        ]);

        $sql = 'SELECT `b`.`id`, `b`.`' . BOOKMARKS_BOL_MarkDao::MARK_USER_ID . '`
            FROM `' . $bookmarksDao->getTableName() . '` AS `b`
            ' . $queryParts['join'] . '
            WHERE ' . $queryParts['where'] . ' AND `b`.`' . BOOKMARKS_BOL_MarkDao::USER_ID . '` = :userId
            ORDER BY `b`.`id` DESC
            LIMIT :limit';

        $result = OW::getDbo()->queryForList($sql, [
            'userId' => $userId,
            'limit' => $limit
        ]);

        $out = [];
        foreach ( $result as $bookmark )
        {
            $out[] = [
                'id' => (int) $bookmark['id'],
                'markUserId' => (int) $bookmark[BOOKMARKS_BOL_MarkDao::MARK_USER_ID]
            ];
        }

        return $out;
    }

    /**
     * Format bookmark data
     *
     * @param integer $loggedUserId
     * @param array $bookmarks
     * @return array
     */
    public function formatBookmarkData($loggedUserId, array $bookmarks)
    {
        $processedUsers = [];
        $ids = [];

        // process users
        foreach( $bookmarks as $bookmark ) 
        {
            $ids[] = $bookmark['markUserId'];

            $processedUsers[$bookmark['markUserId']] = [
                'id' => (int) $bookmark['id'],     
                'avatar' => null,
                'matchAction' => null,
                'user' => [
                    'id' => (int) $bookmark['markUserId'],
                    'userName' => null
                ]
            ];
        }

        // load avatars
        $avatarList = BOL_AvatarService::getInstance()->findByUserIdList($ids);

        foreach ( $avatarList as $avatar ) 
        {
            $processedUsers[$avatar->userId]['avatar'] = $this->getAvatarData($avatar, false);
        }

        // load user names
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($ids);

        foreach ( $userNames as $userId => $userName ) 
        {
            $processedUsers[$userId]['user']['userName'] = $userName;
        }

        // load display names
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($ids);

        foreach ( $displayNames as $userId => $displayName ) 
        {
            if ( $displayName ) 
            {
                $processedUsers[$userId]['user']['userName'] = $displayName;
            }
        }

        // load matches
        $mathList = SKMOBILEAPP_BOL_Service::getInstance()->findUserMatchActionsByUserIdList($loggedUserId, $ids);

        foreach($mathList as $matchAction) {
            $processedUsers[$matchAction->recipientId]['matchAction'] = [
                'id' => (int) $matchAction->id,
                'type' => $matchAction->type,
                'userId' => (int) $matchAction->recipientId,
                'isMutual' => boolval($matchAction->mutual),
                'createStamp' => (int) $matchAction->createStamp,
                'isRead' => boolval($matchAction->read),
                'isNew' => boolval($matchAction->new)
            ];
        }

        $data = [];
        foreach ( $processedUsers as $userData ) 
        {
            $data[] = $userData;
        }

        $event = new OW_Event('skmobileapp.formatted_bookmarks_data', [], $data);
        OW_EventManager::getInstance()->trigger($event);

        return $event->getData();
    }
}
