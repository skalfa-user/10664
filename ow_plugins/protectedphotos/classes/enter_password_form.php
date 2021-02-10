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
 * @package ow_plugins.protected_photos.classes
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_CLASS_EnterPasswordForm extends Form
{
    const FORM_NAME = 'pphotos-enter-password-form';
    const ELEMENT_ALBUM_ID = 'pphotos-album-id';
    const ELEMENT_CONTEXT = 'pphotos-context';
    const ELEMENT_PASSWORD = 'pphotos-enter-password';
    const SUBMIT = 'pphotos-submit';

    public function __construct()
    {
        parent::__construct(self::FORM_NAME);

        $this->setAjax();
        $this->setAction(OW::getRouter()->urlForRoute('protectedphotos.enter_password'));
        $this->setAjaxResetOnSuccess(false);

        $album = new HiddenField(self::ELEMENT_ALBUM_ID);
        $album->setRequired();
        $this->addElement($album);

        $context = new HiddenField(self::ELEMENT_CONTEXT);
        $context->setRequired();
        $this->addElement($context);

        $enterPassword = new TextField(self::ELEMENT_PASSWORD);
        $enterPassword->setRequired();
        $enterPassword->addAttribute('placeholder', OW::getLanguage()->text('protectedphotos', 'password'));
        $this->addElement($enterPassword);

        $submit = new Submit(self::SUBMIT);
        $submit->setValue(OW::getLanguage()->text('protectedphotos', 'submit'));
        $this->addElement($submit);
    }
}
