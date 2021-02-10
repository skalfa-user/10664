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
class PROTECTEDPHOTOS_CMP_PhotoComment extends OW_Component
{
    private $commentService;
    private $albumId;
    private $photoId;

    public function __construct()
    {
        parent::__construct();

        $this->commentService = BOL_CommentService::getInstance();
    }

    public function setAlbumId( $albumId )
    {
        $this->albumId = (int) $albumId;

        return $this;
    }

    public function setPhotoId( $photoId )
    {
        $this->photoId = (int) $photoId;

        return $this;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('count', $this->commentService->findCommentCount('photo_comments', $this->photoId));

        $id = uniqid('pphoto');
        $this->assign('id', $id);
        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_enter_password', array(
                'id' => '#' . $id,
                'albumId' => $this->albumId,
                'context' => implode('|', array(
                    'photo_view', OW::getRouter()->urlForRoute('view_photo', array(
                        'id' => $this->photoId
                    ))
                ))
            ))
        );
    }
}