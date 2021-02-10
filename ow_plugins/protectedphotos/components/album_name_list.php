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
 * AJAX Upload photo component
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.8.1
 */
class PROTECTEDPHOTOS_CMP_AlbumNameList extends OW_Component
{
    /**
     * @param int $userId
     */
    public function __construct( $userId, $exclude = array() )
    {
        parent::__construct();

        if ( empty($userId) )
        {
            $this->setVisible(false);

            return;
        }

        $names = OW::getEventManager()->call('photo.get_album_names', array(
            'userId' => $userId,
            'exclude' => $exclude
        ));

        $protectedAlbums = PROTECTEDPHOTOS_BOL_Service::getInstance()->findPasswordForAlbums(array_keys($names));
        $protectedAlbums = array_map(function( $album )
        {
            return $album->albumId;
        }, $protectedAlbums);

        $this->assign('albumNameList', $names);
        $this->assign('protectedAlbums', $protectedAlbums);
    }
}