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

class PCGALLERY_CLASS_PhotoBridge
{
    /**
     * Class instance
     *
     * @var PCGALLERY_CLASS_PhotoBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return PCGALLERY_CLASS_PhotoBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $defaultPhotoAlbumName = 'Photos of me';

    public function __construct()
    {

    }

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive("photo");
    }

    public function getAlbumName()
    {
        $albumName = OW::getLanguage()->text('pcgallery', 'photo_album_name');

        return empty($albumName) ? $this->defaultPhotoAlbumName : $albumName;
    }

    public function getAlbum( $userId )
    {
        $album = OW::getEventManager()->call("photo.album_find", array(
            'albumTitle' => $this->getAlbumName(),
            'userId' => $userId
        ));

        return empty($album) ? null : $album;
    }

    public function getPhotos( $userId, $limit = null )
    {
        $params = array(
            'userId' => $userId,
            "entityType" => "user",
            "entityId" => $userId,
            "privacy" => null
        );

        if ( $limit !== null )
        {
            $params['offset'] = $limit[0];
            $params['limit'] = $limit[1];
        }

        $photos = OW::getEventManager()->call("photo.entity_photos_find", $params);

        if ( empty($photos) )
        {
            return array();
        }

        $out = array();
        foreach ( $photos as $photoId => $photo )
        {
            $out[] = array(
                'id' => $photoId,
                'src' => $photo['photoUrl']
            );
        }

        return $out;
    }
    
    public function getAlbumPhotos( $userId, $albumId, $limit = null )
    {
        $params = array(
            'albumId' => $albumId,
            'userId' => $userId,
            "privacy" => null
        );

        if ( $limit !== null )
        {
            $params['offset'] = $limit[0];
            $params['limit'] = $limit[1];
        }

        $photos = OW::getEventManager()->call('photo.album_photos_find', $params);

        if ( empty($photos) )
        {
            return array();
        }

        $out = array();
        foreach ( $photos as $photo )
        {
            $out[] = array(
                'id' => $photo["id"],
                'src' => $photo['photoUrl']
            );
        }

        return $out;
    }

    public function collectAlbumSuggest( BASE_CLASS_EventCollector $e )
    {
        $e->add(OW::getLanguage()->text('pcgallery', 'photo_album_name'));
    }

    public function initFloatbox()
    {
        OW::getEventManager()->call('photo.init_floatbox');
    }

    public function init()
    {
        if ( !$this->isActive() ) return;
        
        //OW::getEventManager()->bind("photo.suggest_default_album", array($this, 'collectAlbumSuggest'));
    }
}