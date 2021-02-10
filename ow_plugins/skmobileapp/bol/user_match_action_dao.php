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

class SKMOBILEAPP_BOL_UserMatchActionDao extends OW_BaseDao
{
    use OW_Singleton;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Action life time
     */
    const ACTION_LIFE_TIME = 259200; // 3 days

    /**
     * Action like
     */
    const ACTION_LIKE = 'like';

    /**
     * Action dislike
     */
    const ACTION_DISLIKE = 'dislike';

    /**
     * Match read
     */
    const MATCH_READ = 1;

    /**
     * Match unread
     */
    const MATCH_UNREAD = 0;

    /**
     * Match mutual
     */
    const MATCH_MUTUAL = 1;

    /**
     * Match not mutual
     */
    const MATCH_NOT_MUTUAL = 0;

    /**
     * Match new
     */
    const MATCH_NEW = 1;

    /**
     * Match not new
     */
    const MATCH_NOT_NEW = 0;

    /**
     * Gets DTO class name
     *
     * @return string
     */
    public function getDtoClassName()
    {
        return 'SKMOBILEAPP_BOL_UserMatchAction';
    }

    /**
     * Gets table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skmobileapp_user_match_action';
    }

    /**
     * Delete user match
     *
     * @param integer $id
     * @return void
     */
    public function deleteUserMatch($id)
    {
        $match = $this->findById($id);

        if ( $match )
        {
            // find recipient match
            $recipientMatch = $this->findUserMatch($match->recipientId, $match->userId);

            if ($recipientMatch && $recipientMatch->mutual == self::MATCH_MUTUAL)
            {
                // mark recipient's match as not mutual
                $recipientMatch->mutual = self::MATCH_NOT_MUTUAL;
                $this->save($recipientMatch);
            }

            $event = new OW_Event('skmobileapp.delete_user_match', [
                'userId' => $match->userId,
                'recipientId' => $match->recipientId,
                'type' => $match->type
            ]);

            OW_EventManager::getInstance()->trigger($event);

            $this->deleteById($id);
        }
    }

    /**
     * Find matched users
     *
     * @param integer $userId
     * @param integer $limit
     * @return array
     */
    public function findMatchedUsers($userId, $limit)
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('matched_users', 'recipientId', [
            'method' => 'SKMOBILEAPP_BOL_UserMatchActionDao::findMatchedUsers'
        ]);

        $query = '
            SELECT
                `matched_users`.`id`,
                `matched_users`.`recipientId` AS `userId`,
                `matched_users`.`createStamp`,
                `matched_users`.`read`,
                `matched_users`.`new`,
                `matched_users`.`mutual`
            FROM `' . $this->getTableName() . '` AS `matched_users`
                ' . $queryParts['join'] . '
            WHERE
                (
                    `matched_users`.`userId` = :userId
                        AND
                    `matched_users`.`type` = :type
                        AND
                    `matched_users`.`mutual` = :mutual
                )
                    AND
                ' . $queryParts['where'] . '
            ORDER BY
                `matched_users`.`new` DESC,
                `matched_users`.`createStamp` DESC
            LIMIT
                :limit
        ';

        return $this->dbo->queryForList($query, [
            'userId' => $userId,
            'type' => self::ACTION_LIKE,
            'mutual' => self::MATCH_MUTUAL,
            'limit' => $limit
        ]);
    }

    /**
     * Create user match action
     *
     * @param integer $userId
     * @param integer $recipientId
     * @param string $type
     * @return SKMOBILEAPP_BOL_UserMatchAction
     */
    public function createUserMatchAction($userId, $recipientId, $type)
    {
        // delete old match
        $oldMatch = $this->findUserMatch($userId, $recipientId);

        if ( $oldMatch )
        {
            $event = new OW_Event('skmobileapp.delete_user_match', [
                'userId' => $oldMatch->userId,
                'recipientId' => $oldMatch->recipientId,
                'type' => $oldMatch->type
            ]);

            OW_EventManager::getInstance()->trigger($event);

            $this->deleteById($oldMatch->id);
        }

        // find recipient match
        $recipientMatch = $this->findUserMatch($recipientId, $userId);
        $isMatchMutual  = self::MATCH_NOT_MUTUAL;

        switch ( $type )
        {
            case self::ACTION_LIKE :
                if ( $recipientMatch && $recipientMatch->type == self::ACTION_LIKE )
                {
                    $isMatchMutual = self::MATCH_MUTUAL;

                    // mark recipient's match as mutual
                    $recipientMatch->mutual = self::MATCH_MUTUAL;
                    $this->save($recipientMatch);

                    // send push and email notifications
                    try {
                        $isPushAllowed = (bool) BOL_PreferenceService::getInstance()->
                                getPreferenceValue('skmobileapp_new_matches_push', $recipientId);

                        if ( $isPushAllowed )
                        {
                            $pushMessage = new SKMOBILEAPP_BOL_PushMessage;
                            $pushMessage->setMessageType('matchedUser')
                                ->setSoundName('match.wav')
                                ->setMessageParams([
                                    'id' => (int) $recipientMatch->id
                                ]);

                            $pushMessage->sendNotification($recipientId, 'pn_new_match_title', 'pn_new_match');
                        }

                        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars([$userId]);
                        $avatar = $avatars[$userId];

                        $url = OW::getRouter()->urlForRoute('base_user_profile', [
                            'username' => BOL_UserService::getInstance()->getUserName($userId)
                        ]);

                        // send an email notification
                        $event = new OW_Event('notifications.add', [
                            'pluginKey' => 'skmobileapp',
                            'entityType' => 'skmobileapp_new_match_message',
                            'entityId' => $recipientMatch->id,
                            'action' => 'skmobileapp-new_match_message',
                            'userId' => $recipientId,
                            'time' => time()
                        ], [
                            'avatar' => $avatar,
                            'url' =>  $url,
                            'string' => [
                                'key' => 'skmobileapp+new_match_notification_string',
                                'vars' => []
                            ]
                        ]);

                        OW::getEventManager()->trigger($event);
                    }
                    catch(Exception $e) {}
                }
                break;

            case self::ACTION_DISLIKE :
            default :
                if ( $recipientMatch && $recipientMatch->mutual == self::MATCH_MUTUAL )
                {
                    // mark recipient's match as not mutual
                    $recipientMatch->mutual = self::MATCH_NOT_MUTUAL;
                    $this->save($recipientMatch);
                }
                break;
        }

        $matchDto = new SKMOBILEAPP_BOL_UserMatchAction;

        $matchDto->userId = $userId;
        $matchDto->recipientId = $recipientId;
        $matchDto->type = $type;
        $matchDto->createStamp = time();
        $matchDto->expirationStamp = time() + self::ACTION_LIFE_TIME;
        $matchDto->mutual = $isMatchMutual;
        $matchDto->read = self::MATCH_UNREAD;
        $matchDto->new = $type == self::ACTION_LIKE ? self::MATCH_NEW : self::MATCH_NOT_NEW;

        $this->save($matchDto);

        $event = new OW_Event('skmobileapp.create_user_match', [
            'userId' => $userId,
            'recipientId' => $recipientId,
            'type' => $type
        ]);

        OW_EventManager::getInstance()->trigger($event);

        return $matchDto;
    }

    /**
     * Find user match actions
     *
     * @param integer $userId
     * @param array $userIdList
     * @return array
     */
    public function findUserMatchActionsByUserIdList($userId, array $userIdList)
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldInArray('recipientId', $userIdList);

        return $this->findListByExample($example);
    }

    /**
     * Find user match
     *
     * @param integer $userId
     * @param integer $recipientId
     * @return SKMOBILEAPP_BOL_UserMatchAction
     */
    public function findUserMatch($userId, $recipientId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('recipientId', $recipientId);

        return $this->findObjectByExample($example);
    }

    /**
     * Delete all matches by user id
     *
     * @param integer $userId
     * @return void
     */
    public function deleteAllMatchesByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);

        $example = new OW_Example();
        $example->andFieldEqual('recipientId', $userId);

        $this->deleteByExample($example);
    }
}
