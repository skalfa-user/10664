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
 * @since 1.8.0
 */
class PROTECTEDPHOTOS_CLASS_PasswordFormElement extends TextField
{
    public function __construct( $name, $albumId )
    {
        parent::__construct($name);

        $password = PROTECTEDPHOTOS_BOL_Service::getInstance()->findPasswordForAlbumByAlbumId($albumId);

        if ( $password !== null && $password->privacy === PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD )
        {
            $this->setValue($password->password);
        }

        $this->addAttribute('placeholder', OW::getLanguage()->text('protectedphotos', 'your_password'));
    }

    public function renderInput( $params = null )
    {
        return '<div id="ppp-password" class="ow_smallmargin" style="display: none">
            <div class="ow_pass_protected_privacy_pass_field">
                ' . parent::renderInput() . '<br>
                ' . UTIL_HtmlTag::generateTag('span', array('id' => $this->getId() . '_error', 'style' => 'display:none;', 'class' => 'error'), true) . '
            </div>
        </div>';

    }
}
