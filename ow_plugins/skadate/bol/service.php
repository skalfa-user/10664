<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
final class SKADATE_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var SKADATE_BOL_Service
     */
    private static $classInstance;

    /**
     * @var SKADATE_BOL_SpeedmatchRelationDao
     */
    private $relationDao;

    const BIG_AVATAR_WIDTH = 960;
    const BIG_AVATAR_HEIGHT = 640;

    const MEDIUM_AVATAR_WIDTH = 800;
    const MEDIUM_AVATAR_HEIGHT = 480;

    const SMALL_AVATAR_WIDTH = 480;
    const SMALL_AVATAR_HEIGHT = 320;

    const BIG_AVATAR_PREFIX = 'avatar_';

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SKADATE_BOL_Service
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
     * Constructor.
     */
    private function __construct()
    {
        $this->relationDao = SKADATE_BOL_SpeedmatchRelationDao::getInstance();
    }

    public function copyBigAvatar( $userId )
    {
        $avatarService = BOL_AvatarService::getInstance();

        $avatar = $avatarService->findByUserId($userId);

        if ( !$avatar )
        {
            return false;
        }

        // remove old
        $this->removeBigAvatar($userId);

        $origAvatarPath = $avatarService->getAvatarPath($userId, 3);

        $tmpPath = $this->getAvatarPluginFilesPath($userId, $avatar->hash);

        if ( !is_writable(dirname($tmpPath)) )
        {
            return false;
        }

        $storage = OW::getStorage();
        $storage->copyFileToLocalFS($origAvatarPath, $tmpPath);

        $image = new UTIL_Image($tmpPath);

        $width = $image->getWidth();
        $imageSize = $this->checkImageSize( $width );
        if ( empty($imageSize) )
        {
            @unlink($tmpPath);

            return false;
        }

        $width = $imageSize['width'];
        $height = $imageSize['height'];

        if ( $width == 0 || $height == 0 )
        {
            @unlink($tmpPath);

            return false;
        }
        // store new
        $newAvatar = new SKADATE_BOL_Avatar();
        $newAvatar->userId = $userId;
        $newAvatar->avatarId = $avatar->id;
        $newAvatar->hash = $avatar->hash;

        SKADATE_BOL_AvatarDao::getInstance()->save($newAvatar);

        $bigAvatarPath = $this->getAvatarPath($userId, $avatar->hash);

        $image->resizeImage($width, $height, true)
            ->saveImage($tmpPath);

        $storage->copyFile($tmpPath, $bigAvatarPath);

        @unlink($tmpPath);

        return true;
    }

    public function checkImageSize( $width )
    {
        $width = (int) $width;

        if ( $width > 0 )
        {
            if ( $width >= self::BIG_AVATAR_WIDTH )
            {
                $width = self::BIG_AVATAR_WIDTH;
                $height = self::BIG_AVATAR_HEIGHT;
            }
            else if( self::BIG_AVATAR_WIDTH > $width && $width >= self::MEDIUM_AVATAR_WIDTH)
            {
                $width = self::MEDIUM_AVATAR_WIDTH;
                $height = self::MEDIUM_AVATAR_HEIGHT;
            }
            else if( self::MEDIUM_AVATAR_WIDTH > $width && $width >= self::SMALL_AVATAR_WIDTH)
            {
                $width = self::SMALL_AVATAR_WIDTH;
                $height = self::SMALL_AVATAR_HEIGHT;
            }
            else
            {
                return array();
            }

            return array(
                'width'     => $width,
                'height'    => $height
            );
        }

        return array();
    }

    public function removeBigAvatar( $userId )
    {
        $avatar = $this->findAvatarByUserId($userId);

        if ( !$avatar )
        {
            return true;
        }

        SKADATE_BOL_AvatarDao::getInstance()->deleteById($avatar->id);

        $avatarPath = $this->getAvatarPath($userId, $avatar->hash);

        $storage = OW::getStorage();

        if ( $storage->fileExists($avatarPath) )
        {
            $storage->removeFile($avatarPath);
        }

        return true;
    }

    public function findAvatarByUserId( $userId )
    {
        return SKADATE_BOL_AvatarDao::getInstance()->findByUserId($userId);
    }

    public function findAvatarListByUserIdList( array $userIdList )
    {
        $result = array();

        foreach ( SKADATE_BOL_AvatarDao::getInstance()->findByUserIdList($userIdList) as $avatar )
        {
            $result[$avatar->userId] = $avatar;
        }

        return $result;
    }

    public function getAvatarsDir()
    {
        return OW::getPluginManager()->getPlugin('skadate')->getUserFilesDir() . 'avatars' . DS;
    }

    public function getAvatarsPluginFilesDir()
    {
        return OW::getPluginManager()->getPlugin('skadate')->getPluginFilesDir() . 'avatars' . DS;
    }

    public function getAvatarPath( $userId, $hash )
    {
        $dir = self::getAvatarsDir();

        return $dir . self::BIG_AVATAR_PREFIX . $userId . '_' . $hash . '.jpg';
    }

    public function getAvatarPluginFilesPath( $userId, $hash )
    {
        $dir = $this->getAvatarsPluginFilesDir();

        return $dir . self::BIG_AVATAR_PREFIX . $userId . '_' . $hash . '.jpg';
    }

    public function getAvatarUrl( $userId, $hash )
    {
        $dir = OW::getPluginManager()->getPlugin('skadate')->getUserFilesDir() . 'avatars' . DS;

        return OW::getStorage()->getFileUrl($dir . self::BIG_AVATAR_PREFIX . $userId . '_' . $hash . '.jpg');
    }

    public function addSpeedmatchRelation( $userId, $oppUserId, $status = 0 )
    {
        if ( !$userId || !$oppUserId )
        {
            return false;
        }

        $oppUser = BOL_UserService::getInstance()->findUserById($oppUserId);
        if ( !$oppUser )
        {
            return false;
        }

        $relation = $this->findSpeedmatchRelation($userId, $oppUserId);

        if ( $relation )
        {
            return true;
        }

        $relation = new SKADATE_BOL_SpeedmatchRelation();

        $relation->userId = $userId;
        $relation->oppUserId = $oppUserId;
        $relation->status = $status;
        $relation->addTimestamp = time();

        $this->relationDao->save($relation);

        return true;
    }

    public function findSpeedmatchRelation( $userId, $oppUserId )
    {
        return $this->relationDao->findRelation($userId, $oppUserId);
    }

    public function isSpeedmatchRelationMutual( $userId1, $userId2 )
    {
        if ( !$userId1 || !$userId2 )
        {
            return false;
        }

        $relation1 = $this->findSpeedmatchRelation($userId1, $userId2);
        $relation2 = $this->findSpeedmatchRelation($userId2, $userId1);

        if ( $relation1 && $relation2 && $relation1->status && $relation2->status )
        {
            return true;
        }

        return false;
    }

    public function startSpeedmatchConversation( $userId, $opponentId )
    {
        if ( !$userId || !$opponentId )
        {
            return false;
        }

        $content = array(
            'entityType' => 'speedmatch',
            'eventName' => 'display_mutual_message',
            'params' => array(
                'userId' => $userId,
                'opponentId' => $opponentId,
            )
        );

        $activeModes = OW::getEventManager()->call('mailbox.get_active_mode_list');

        if ( count($activeModes) === 2 || in_array('chat', $activeModes) )
        {
            $eventParams = array(
                'mode' => count($activeModes) === 2 ? 'chat' : $activeModes[0],
                'userId' => $userId,
                'opponentId' => $opponentId,
                'isSystem' => 1,
                'text' => json_encode($content)
            );
            $event = new OW_Event('mailbox.post_message', $eventParams);
            OW::getEventManager()->trigger($event);
            $data = $event->getData();

            if ( isset($data['error']) && $data['error'] === false && isset($data['message']) )
            {
                return $data['message']['convId'];
            }
        }
        else
        {
            $params = array(
                'userId' => $userId,
                'opponentId' => $opponentId,
                'text' => json_encode($content),
                'subject' => OW::getLanguage()->text('skadate', 'speedmatch_subject')
            );

            try
            {
                $conversation = OW::getEventManager()->call('mailbox.create_conversation', $params);
                $conversationService = MAILBOX_BOL_ConversationService::getInstance();
                $messageDto = $conversationService->getLastMessage($conversation->id);
                $messageDto->isSystem = 1;
                $conversationService->saveMessage($messageDto);
            } catch ( Exception $e ) { }

            if ( !empty($conversation) )
            {
                return $conversation->id;
            }
        }

        return false;
    }

    public function removeSpeedmatchRelationsByUserId( $userId )
    {
        $this->relationDao->deleteByUserId($userId);
    }

    public function findSpeedmatchOpponents( $userId, $first, $count, $criteria, $exclude = array() )
    {
        if ( !$userId )
        {
            return null;
        }

        if ( isset($criteria['birthdate']) )
        {
            $range = explode('-', $criteria['birthdate']);
            if ( !empty($range[0]) && !empty($range[1]) )
            {
                $criteria['birthdate'] = array('from' => $range[0], 'to' => $range[1]);
            }
            else
            {
                unset($criteria['birthdate']);
            }
        }

        $addQuery['join'] = ' LEFT JOIN `' . $this->relationDao->getTableName() . '` AS `relation`
            ON (`user`.`id` = `relation`.`oppUserId` AND `relation`.`userId` = ' . OW::getDbo()->escapeString($userId) . ') ';

        $addQuery['where'] = ' AND `relation`.`id` IS NULL AND `user`.`id` != ' . OW::getDbo()->escapeString($userId);

        if ( !empty($exclude) )
        {
            $addQuery['where'] .= ' AND `user`.`id` NOT IN (' . implode(',', array_map('intval', array_filter($exclude))) . ') ';
        }

        if ( !empty($criteria['googlemap_location']['latitude']) && !empty($criteria['googlemap_location']['longitude']) )
        {
            $location = $criteria['googlemap_location'];
            unset($criteria['googlemap_location']);

            $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates(
                $location['latitude'], $location['longitude'], 'sw', (float) $location['distance']
            );
            $location['southWestLat'] = $coord['lat'];
            $location['southWestLng'] = $coord['lng'];

            $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates(
                $location['latitude'], $location['longitude'], 'ne', (float) $location['distance']
            );
            $location['northEastLat'] = $coord['lat'];
            $location['northEastLng'] = $coord['lng'];

            $sql = GOOGLELOCATION_BOL_LocationService::getInstance()->getSearchInnerJoinSql(
                'user', $location['southWestLat'], $location['southWestLng'], $location['northEastLat'], $location['northEastLng'], null, 'INNER'
            );

            $addQuery1 = $addQuery;
            $addQuery1['join'] .= $sql;

            $query1 = BOL_UserDao::getInstance()->findUserIdListByQuestionValuesQuery($criteria, false, array_merge($addQuery1, array(
                'distinct' => false
            )));

            $locationDao = SKADATE_BOL_CurrentLocationDao::getInstance();

            $sql = ' INNER JOIN `' . $locationDao->getTableName() . '` current_location ON (user.id = current_location.userId
                AND (
                    current_location.latitude >= ' . $location['southWestLat'] . '
                    AND current_location.latitude <= ' . $location['northEastLat'] . '
                )
                AND (
                    current_location.longitude >= ' . $location['southWestLng'] . '
                    AND current_location.longitude <= ' . $location['northEastLng'] . '
                )) ';

            $addQuery2 = $addQuery;
            $addQuery2['join'] .= $sql;

            $query2 = BOL_UserDao::getInstance()->findUserIdListByQuestionValuesQuery($criteria, false, array_merge($addQuery2, array(
                'distinct' => false
            )));

            $query = '('.$query1 . ') UNION (' . $query2 . ") ORDER BY 2 DESC ";

            $idList = OW::getDbo()->queryForColumnList($query . " LIMIT :first, :count ", array_merge(array('first' => $first, 'count' => $count)));
        }
        else
        {
            $idList = BOL_UserDao::getInstance()->findUserIdListByQuestionValues($criteria, $first, $count, false, array_merge($addQuery, array(
                'distinct' => false
            )));
        }

        return $idList;
    }

    public function removeCurrentLocationByUserId( $userId )
    {
        SKADATE_BOL_CurrentLocationDao::getInstance()->deleteByUserId($userId);

        return true;
    }
    
    public function getPromoImageUrl()
    {
        return  OW::getPluginManager()->getPlugin('skadate')->getUserFilesUrl().'mobile_promo_image.jpg';
    }
    
    public function getPromoImagePath()
    {
        return  OW::getPluginManager()->getPlugin('skadate')->getUserFilesDir().'mobile_promo_image.jpg';
    }
    
    public function setPromoImageUploaded($value = true)
    {        
        OW::getConfig()->saveConfig('skadate', 'promo_image_uploaded', (boolean)$value);
    }
    
    public function isPromoImageUploaded()
    {
        return  OW::getConfig()->getValue('skadate', 'promo_image_uploaded') == '1';
    }
}
