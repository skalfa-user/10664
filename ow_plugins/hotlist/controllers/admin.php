<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Hot List settings
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.hotlist.controllers
 * @since 1.0
 */
class HOTLIST_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $service;

    public function __construct()
    {
        $this->service = HOTLIST_BOL_Service::getInstance();

        parent::__construct();
    }

    public function index( $params = array() )
    {
        $userService = BOL_UserService::getInstance();

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('hotlist', 'admin_heading_settings'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');

        $settingsForm = new Form('settingsForm');
        $settingsForm->setId('settingsForm');

        $expiration_time = new TextField('expiration_time');
        $expiration_time->setRequired();
        $expiration_time->setLabel($language->text('hotlist', 'label_expiration_time'));
        $expiration_time_value = (int)OW::getConfig()->getValue('hotlist', 'expiration_time') / 86400;
        $expiration_time->setValue($expiration_time_value);

        $settingsForm->addElement($expiration_time);

        $submit = new Submit('save');
        $submit->addAttribute('class', 'ow_ic_save');
        $submit->setValue($language->text('hotlist', 'label_save_btn_label'));

        $settingsForm->addElement($submit);

        $this->addForm($settingsForm);

        if ( OW::getRequest()->isPost() )
        {
            if ( $settingsForm->isValid($_POST) )
            {
                $data = $settingsForm->getValues();

                
                OW::getConfig()->saveConfig('hotlist', 'expiration_time', $data['expiration_time']*86400);

                OW::getFeedback()->info($language->text('hotlist', 'settings_saved'));
                $this->redirect();
            }
        }
    }


}
