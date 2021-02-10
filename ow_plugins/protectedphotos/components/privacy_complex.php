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
class PROTECTEDPHOTOS_CMP_PrivacyComplex extends OW_Component
{
    const ELEMENT_COMPLEX = 'ppp-complex';
    const ELEMENT_PRIVACY = 'ppp-privacy';
    const ELEMENT_SEARCH = 'ppp-search';
    const ELEMENT_FRIEND_LIST = 'ppp-friend-list';
    const ELEMENT_SELECTED_LIST = 'ppp-selected-list';
    const ELEMENT_PASSWORD = 'ppp-password';

    private $formName;

    private $privacyInput;
    private $searchInput;
    private $friendListInput;
    private $selectedFriendInput;
    private $passwordInput;

    public function __construct( $formName, $userId, $albumId = null )
    {
        parent::__construct();

        $this->formName = $formName;

        $this->privacyInput = new PROTECTEDPHOTOS_CLASS_PrivacyFormElement(self::ELEMENT_PRIVACY, $albumId);
        $this->searchInput = new PROTECTEDPHOTOS_CLASS_SearchUserFormElement(self::ELEMENT_SEARCH);
        $this->friendListInput = new PROTECTEDPHOTOS_CLASS_FriendListFormElement(self::ELEMENT_FRIEND_LIST, $userId);
        $this->friendListInput->setSelectedList(PROTECTEDPHOTOS_BOL_Service::getInstance()->getFriendIds($albumId));
        $this->selectedFriendInput = new PROTECTEDPHOTOS_CLASS_SelectedFriendListFormElement(self::ELEMENT_SELECTED_LIST, $albumId);
        $this->selectedFriendInput->addValidator(new PROTECTEDPHOTOS_CLASS_SelectedFriendListValidator(
            $this->formName,
            $this->privacyInput->getName()
        ));
        $this->passwordInput = new PROTECTEDPHOTOS_CLASS_PasswordFormElement(self::ELEMENT_PASSWORD, $albumId);
        $this->passwordInput->addValidator(new PROTECTEDPHOTOS_CLASS_PasswordValidator(
            $this->formName,
            $this->privacyInput->getName()
        ));

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('protectedphotos')->getStaticJsUrl() . 'form.js', 'text/javascript', 0);
    }

    public function setPrivacyOptions( array $options )
    {
        $this->privacyInput->setOptions($options);
    }

    public function setSearchPlaceHolder( $placeholder )
    {
        $this->searchInput->setPlaceholder($placeholder);
    }

    public function setSearchApiUrl( $url )
    {
        $this->searchInput->setUrl($url);
    }

    public function setValue( $values )
    {
        $this->privacyInput->setValue(isset($values[self::ELEMENT_PRIVACY]) ? $values[self::ELEMENT_PRIVACY] : null);
        $this->searchInput->setValue(isset($values[self::ELEMENT_SEARCH]) ? $values[self::ELEMENT_SEARCH] : null);
        $this->friendListInput->setValue(isset($values[self::ELEMENT_FRIEND_LIST]) ? $values[self::ELEMENT_FRIEND_LIST] : null);
        $this->selectedFriendInput->setValue(isset($values[self::ELEMENT_SELECTED_LIST]) ? $values[self::ELEMENT_SELECTED_LIST] : null);
        $this->passwordInput->setValue(isset($values[self::ELEMENT_PASSWORD]) ? $values[self::ELEMENT_PASSWORD] : null);
    }

    public function getValue()
    {
        return array(
            self::ELEMENT_PRIVACY => $this->privacyInput->getValue(),
            self::ELEMENT_SEARCH => $this->searchInput->getValue(),
            self::ELEMENT_FRIEND_LIST => $this->friendListInput->getValue(),
            self::ELEMENT_SELECTED_LIST => array_map('intval', explode(',', $this->selectedFriendInput->getValue())),
            self::ELEMENT_PASSWORD => $this->passwordInput->getValue()
        );
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->bindJs();

        $this->assign('privacyMarkup', $this->privacyInput->renderInput());
        $this->assign('passwordMarkup', $this->passwordInput->renderInput());
        $this->assign('searchMarkup', $this->searchInput->renderInput());
        $this->assign('friendListMarkup', $this->friendListInput->renderInput());
        $this->assign('selectedListMarkup', $this->selectedFriendInput->renderInput());
    }

    private function bindJs()
    {
        $formName = $this->formName;
        
        $js = array_reduce(array(
            $this->privacyInput,
            $this->searchInput,
            $this->friendListInput,
            $this->selectedFriendInput,
            $this->passwordInput
        ), function( $carry, $input ) use ($formName)
        {
            $carry .= ';(function()
            {
                var form = owForms["' . $formName . '"];
                ' . $input->getElementJs() . '
                form.addElement(formElement);
            }());';

            return $carry;
        }, '');

        $js .= 'var content = (_scope && _scope.floatBox) ? _scope.floatBox.$body : null;
        OW.trigger("protectedphotos.start", ["' . $this->formName . '", content]);';

        OW::getDocument()->addOnloadScript($js);
    }
}
