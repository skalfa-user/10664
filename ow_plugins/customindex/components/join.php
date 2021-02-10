<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CUSTOMINDEX_CMP_Join extends OW_Component
{
    /**
     * Join form
     *
     * @var CUSTOMINDEX_CLASS_JoinForm
     */
    protected $joinForm;

    /**
     * Responder url
     *
     * @var string
     */
    protected $responderUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if ( (int) OW::getConfig()->
                getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS ) {

            $this->setVisible(false);
        }
    }

    /**
     * On before render
     */
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $language = OW::getLanguage();

        $this->joinForm = OW::getClassInstance('CUSTOMINDEX_CLASS_JoinForm', $this);
        $this->joinForm->setAction(OW::getRouter()->urlForRoute('customindex_submit_handler'));
        $this->responderUrl = OW::getRouter()->urlFor('BASE_CTRL_Join', 'ajaxResponder');

        $this->addForm($this->joinForm);

        // add css
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
            getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getStaticCssUrl() . 'join.css');

        // init langs
        $language->addKeyForJs('base', 'join_error_username_not_valid');
        $language->addKeyForJs('base', 'join_error_username_already_exist');
        $language->addKeyForJs('base', 'join_error_email_not_valid');
        $language->addKeyForJs('base', 'join_error_email_already_exist');
        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');

        // include js
        $onLoadJs = ' window.join = new OW_BaseFieldValidators( ' .
            json_encode(array(
                'formName' => $this->joinForm->getName(),
                'responderUrl' => $this->responderUrl,
                'passwordMaxLength' => UTIL_Validator::PASSWORD_MAX_LENGTH,
                'passwordMinLength' => UTIL_Validator::PASSWORD_MIN_LENGTH)) . ',
                ' . UTIL_Validator::EMAIL_PATTERN . ', ' . UTIL_Validator::USER_NAME_PATTERN . ' ); ';

        OW::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . 'base_field_validators.js');
    }
}
