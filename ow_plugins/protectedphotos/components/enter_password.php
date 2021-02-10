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
class PROTECTEDPHOTOS_CMP_EnterPassword extends OW_Component
{
    public function __construct( $albumId, $context )
    {
        parent::__construct();

        $service = PROTECTEDPHOTOS_BOL_Service::getInstance();

        if ( empty($albumId) || !$service->isAlbumProtected($albumId) )
        {
            $this->setVisible(false);

            return;
        }

        $form = new PROTECTEDPHOTOS_CLASS_EnterPasswordForm();

        $event = OW::getEventManager()->trigger(
            new OW_Event('photo.album_find', array(
                'albumId' => $albumId
            ))
        );
        $album = $event->getData();
        $form->getElement(PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_PASSWORD)->setLabel(
            OW::getLanguage()->text('protectedphotos', 'enter_password_label', array(
                'albumName' => strip_tags($album['name'])
            ))
        );
        $form->getElement(PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_ALBUM_ID)->setValue($albumId);
        $form->getElement(PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_CONTEXT)->setValue($context);
        $this->addForm($form);

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'script.js');
    }
}
   