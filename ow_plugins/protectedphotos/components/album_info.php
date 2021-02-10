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
 * @package ow_plugins.protected_photos.components
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_CMP_AlbumInfo extends OW_Component
{
    /**
     * @param PHOTO_BOL_PhotoAlbum $album
     */
    public function __construct( $album )
    {
        parent::__construct();

        $this->assign('album', $album);
        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $coverUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';
        $this->assign('coverUrl', $coverUrl);
        $this->assign('coverUrlOrig', $coverUrl);

        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_enter_password', array(
                'id' => '#pphotos-enter-password',
                'albumId' => $album->id,
                'context' => implode('|', array(
                    'album_view', OW::getRouter()->urlForRoute('photo_user_album', array(
                        'user' => BOL_UserService::getInstance()->getUserName($album->userId),
                        'album' => $album->id
                    ))
                ))
            ))
        );
    }
}
