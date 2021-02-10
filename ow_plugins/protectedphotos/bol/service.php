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
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.protected_photos.bol
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_BOL_Service
{
    const PRIVACY_PUBLIC = 'public';
    const PRIVACY_FRIENDS_ONLY = 'friends_only';
    const PRIVACY_INDIVIDUAL_FRIENDS = 'individual_friends';
    const PRIVACY_PASSWORD = 'password';

    private static $instance;

    public static function getInstance()
    {
        if ( null === self::$instance )
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $passwordDao;
    private $accessDao;

    private function __construct()
    {
        $this->passwordDao = PROTECTEDPHOTOS_BOL_PasswordDao::getInstance();
        $this->accessDao = PROTECTEDPHOTOS_BOL_AccessDao::getInstance();
    }

    /**
     * @param $albumId
     * @return PROTECTEDPHOTOS_BOL_Password
     */
    public function findPasswordForAlbumByAlbumId( $albumId )
    {
        return $this->passwordDao->findByAlbumId($albumId);
    }

    public function isAlbumProtected( $albumId )
    {
        static $cache = array();

        if ( !isset($cache[$albumId]) )
        {
            $cache[$albumId] = $this->passwordDao->isAlbumProtected($albumId);
        }

        return $cache[$albumId];
    }

    /**
     * @param array $ids
     * @return PROTECTEDPHOTOS_BOL_Password[]
     */
    public function findPasswordForAlbums( array $ids )
    {
        return $this->passwordDao->findByAlbumIds($ids);
    }

    public function getPasswordOrCreate( $albumId )
    {
        if ( empty($albumId) )
        {
            throw new InvalidArgumentException('Required album ID');
        }

        $password = $this->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null )
        {
            $password = new PROTECTEDPHOTOS_BOL_Password();
            $password->albumId = (int) $albumId;
        }

        return $password;
    }

    public function protectAlbumButExcludeAllFriends( $albumId )
    {
        if ( empty($albumId) )
        {
            return false;
        }

        $password = $this->getPasswordOrCreate($albumId);
        $password->privacy = self::PRIVACY_FRIENDS_ONLY;
        $password->meta = null;
        $this->passwordDao->save($password);

        return $password->id;
    }

    public function grantAccessForAllFriends( $userId, $albumId )
    {
        if ( empty($userId) || empty($albumId) ) return;

        $count = OW::getEventManager()->call('plugin.friends.count_friends', array(
            'userId' => $userId
        ));

        if ( (int)$count > 0 )
        {
            $friendIds = OW::getEventManager()->call('plugin.friends.get_friend_list', array(
                'userId' => $userId,
                'count' => $count
            ));

            $this->grantAccesses($albumId, $friendIds);
        }
    }

    public function changeFeedPrivacyByAlbum( $albumId )
    {
        $password = $this->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null ) return;

        $this->clearFeedPrivacyForAlbum($password->albumId, $password->privacy);

        if ( $password->privacy === PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS )
        {
            $this->changeAlbumPrivacyForIndividualFriends($password->albumId, $this->getFriendIds($password->albumId));
        }
    }

    public function clearFeedPrivacyForAlbum( $albumId, $privacy )
    {
        switch ( $privacy )
        {
            case PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC:
            case PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD:
                $this->changeFeedPrivacy($albumId, 'everybody');
                break;
            case PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY:
                $this->changeFeedPrivacy($albumId, 'friends_only');
                break;
        }

        $this->removeAlbumFollowers($albumId);
    }

    public function changeAlbumPrivacyForIndividualFriends( $albumId, array $friends )
    {
        $permission = $this->generatePermissionForFeed($albumId);
        $this->changeFeedPrivacy($albumId, $permission);

        $album = $this->getAlbum($albumId);
        $this->follow($album['userId'], $permission, $friends);
    }

    public function getAlbum( $albumId )
    {
        static $cache = array();

        if ( !isset($cache[$albumId]) )
        {
            $cache[$albumId] = OW::getEventManager()->call('photo.album_find', array('albumId' => $albumId));
        }

        return $cache[$albumId];
    }

    private function getFeedItemsForAlbum( $albumId )
    {
        $photos = $this->getAlbumPhotos($albumId);

        if ( count($photos) === 0 ) return array();

        return $this->groupPhotosByUploadKey($photos);
    }

    private function getAlbumPhotos( $albumId )
    {
        if ( empty($albumId) ) array();

        PROTECTEDPHOTOS_CLASS_QueryHandler::getInstance()->unbind();
        $countPhotos = OW::getEventManager()->call('photo.album_photos_count', array('albumId' => $albumId));

        if ( (int) $countPhotos <= 0 )
        {
            PROTECTEDPHOTOS_CLASS_QueryHandler::getInstance()->init();

            return array();
        }

        $photos = OW::getEventManager()->call('photo.album_photos_find', array(
            'albumId' => $albumId,
            'limit' => $countPhotos
        ));

        PROTECTEDPHOTOS_CLASS_QueryHandler::getInstance()->init();

        return array_map(function( $photo )
        {
            return $photo['dto'];
        }, $photos);
    }

    private function groupPhotosByUploadKey( array $photos )
    {
        if ( count($photos) === 0 ) return array();

        $grouped = array_reduce($photos, function( $carry, $photo )
        {
            if ( !isset($carry[$photo['uploadKey']]) )
            {
                $carry[$photo['uploadKey']] = array();
            }

            $carry[$photo['uploadKey']][] = $photo;

            return $carry;
        }, array());

        $result = array(
            'multiple_photo_upload' => array(),
            'photo_comments' => array()
        );

        foreach ( $grouped as $key => $group )
        {
            if ( count($group) > 1 )
            {
                $result['multiple_photo_upload'][] = $key;
            }
            else
            {
                $photo = array_pop($group);
                $result['photo_comments'][] = $photo['id'];
            }
        }

        return $result;
    }

    public function changeFeedPrivacy( $albumId, $privacy )
    {
        $album = $this->getAlbum($albumId);
        $feedItems = $this->getFeedItemsForAlbum($albumId);

        foreach ( $feedItems as $entityType => $entityIds )
        {
            foreach ( $entityIds as $entityId )
            {
                $event = new OW_Event('feed.activity', array(
                    'activityType' => 'create',
                    'activityId' => $album['userId'],
                    'pluginKey' => 'photo',
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'userId' => $album['userId'],
                    'privacy' => $privacy
                ));

                OW::getEventManager()->trigger($event);
            }
        }
    }

    public function protectAlbumButExcludeIndividualFriends( $albumId, array $friendIds )
    {
        if ( empty($albumId) )
        {
            return false;
        }

        $password = $this->getPasswordOrCreate($albumId);
        $password->privacy = self::PRIVACY_INDIVIDUAL_FRIENDS;
        $password->meta = json_encode(array(
            'friend_ids' => array_filter(array_map('intval', $friendIds))
        ));
        $this->passwordDao->save($password);

        return $password->id;
    }

    public function grantAccessForIndividualFriends( $albumId, array $friendIds )
    {
        $this->deleteAlbumAccess($albumId);
        $this->grantAccesses($albumId, $friendIds);
    }

    public function generatePermissionForFeed( $albumId )
    {
        return sprintf('pppa-%d', $albumId);
    }

    public function removeAlbumFollowers( $albumId, array $exclude = array() )
    {
        $friends = $this->getFriendIds($albumId);

        if ( count($friends) === 0 ) return;

        $album = $this->getAlbum($albumId);
        $permission = $this->generatePermissionForFeed($albumId);
        $this->unFollow($album['userId'], $permission, array_diff($friends, $exclude));
    }

    public function unFollow( $userId, $permission, array $friendIds )
    {
        foreach ( $friendIds as $friendId )
        {
            OW::getEventManager()->trigger(new OW_Event('feed.remove_follow', array(
                'userId' => $friendId,
                'feedType' => 'user',
                'feedId' => $userId,
                'permission' => $permission
            )));
        }
    }

    public function follow( $userId, $permission, array $friends )
    {
        foreach ( $friends as $friendId )
        {
            OW::getEventManager()->trigger(new OW_Event('feed.add_follow', array(
                'userId' => $friendId,
                'feedType' => 'user',
                'feedId' => $userId,
                'permission' => $permission
            )));
        }
    }

    public function protectAlbumByPassword( $albumId, $password )
    {
        if ( empty($albumId) || empty($password) )
        {
            return false;
        }

        $pwd = $this->getPasswordOrCreate($albumId);

        if ( strcmp(trim($pwd->password), trim($password)) !== 0 )
        {
            $this->deleteAlbumAccess($albumId);
        }

        $pwd->privacy = self::PRIVACY_PASSWORD;
        $pwd->meta = null;
        $pwd->password = trim($password);
        $this->passwordDao->save($pwd);

        return $pwd->id;
    }

    public function grantAccess( $albumId, $userId )
    {
        if ( empty($albumId) || empty($userId) )
        {
            return false;
        }

        $password = $this->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null )
        {
            return false;
        }

        $access = $this->accessDao->findByPasswordIdAndUserId($password->id, $userId);

        if ( $access === null )
        {
            $access = new PROTECTEDPHOTOS_BOL_Access();
            $access->passwordId = $password->id;
            $access->userId = (int) $userId;

            $this->accessDao->save($access);
        }

        return $access->id;
    }

    public function grantAccesses( $albumId, array $userIds )
    {
        if ( empty($albumId) || count($userIds) === 0 ) return;

        $password = $this->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null )
        {
            return false;
        }

        foreach ( $userIds as $userId )
        {
            $access = $this->accessDao->findByPasswordIdAndUserId($password->id, $userId);

            if ( $access === null )
            {
                $access = new PROTECTEDPHOTOS_BOL_Access();
                $access->passwordId = $password->id;
                $access->userId = (int) $userId;

                $this->accessDao->save($access);
            }
        }
    }

    /**
     * @param array $passwordIds
     * @param $userId
     * @return PROTECTEDPHOTOS_BOL_Access[]
     */
    public function findAlbumAccessForUser( array $passwordIds, $userId )
    {
        return $this->accessDao->findByPasswordIdsAndUserId($passwordIds, $userId);
    }

    public function getAccessForUser( $userId, array $albumIds )
    {
        if ( count($albumIds) === 0 )
        {
            return array();
        }

        $passwords = $passwordIds = $passwordAlbumIds = $result = array();

        foreach ( $this->findPasswordForAlbums($albumIds) as $password )
        {
            $passwords[(int) $password->id] = $password;
            $passwordIds[] = (int) $password->id;
            $passwordAlbumIds[] = (int) $password->albumId;
        }

        foreach ( $this->findAlbumAccessForUser($passwordIds, $userId) as $access )
        {
            $result[] = (int) $passwords[$access->passwordId]->albumId;
        }

        return array_merge($result, array_diff($albumIds, $passwordAlbumIds));
    }

    public function deleteAlbumPassword( $albumId )
    {
        $this->deleteAlbumAccess($albumId);
        $this->passwordDao->deleteByAlbumId($albumId);
    }

    public function deleteAlbumAccess( $albumId )
    {
        $password = $this->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null )
        {
            return;
        }

        $this->accessDao->deleteAccessByPasswordId($password->id);
    }

    public function deleteAlbumAccessFromUser( $albumId, $userId )
    {
        $password = $this->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null )
        {
            return;
        }

        $this->accessDao->deleteAccessForUser($password->id, $userId);
    }

    public function findUserPasswordByPrivacy( $userId, array $privacies )
    {
        return $this->passwordDao->findUserPasswordByPrivacy($userId, $privacies);
    }

    public function getFriendIds( $albumId )
    {
        $password = PROTECTEDPHOTOS_BOL_Service::getInstance()->findPasswordForAlbumByAlbumId($albumId);

        if ( $password !== null && $password->privacy === PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS )
        {
            $meta = json_decode($password->meta, true);
            return array_filter(array_map('intval', $meta['friend_ids']));
        }

        return array();
    }
}
