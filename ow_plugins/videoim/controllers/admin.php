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
 * Video IM admin controller
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugin.videoim.controllers
 * @since 1.8.1
 */
class VIDEOIM_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Menu tabs
     */
    const TAB_SERVER_LIST = 'tab_server_list';
    const TAB_SETTINGS = 'tab_settings';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $router = OW::getRouter();
        $language = OW::getLanguage();
        $handler = OW::getRequestHandler()->getHandlerAttributes();
        $menus = array();

        $serverList = new BASE_MenuItem();
        $serverList->setLabel($language->text('videoim', 'admin_menu_server_list_label'));
        $serverList->setUrl($router->urlForRoute('videoim_admin_config'));
        $serverList->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === self::TAB_SERVER_LIST);
        $serverList->setKey(self::TAB_SERVER_LIST);
        $serverList->setIconClass('ow_ic_info');
        $serverList->setOrder(0);
        $menus[] = $serverList;

        $settings = new BASE_MenuItem();
        $settings->setLabel($language->text('videoim', 'admin_menu_settings_label'));
        $settings->setUrl($router->urlForRoute('videoim_admin_settings'));
        $settings->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === self::TAB_SETTINGS);
        $settings->setKey(self::TAB_SETTINGS);
        $settings->setIconClass('ow_ic_gear_wheel');
        $settings->setOrder(1);
        $menus[] = $settings;

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menus));
    }

    /**
     * Default action
     */
    public function index()
    {
        $isDemoModeActivated = VIDEOIM_BOL_VideoImService::getInstance()->isDemoModeActivated();

        if ( $isDemoModeActivated )
        {
            // reload the current page
            OW::getFeedback()->error( OW::getLanguage()->text('videoim', 'settings_update_unavailable') );
            $this->redirect( OW::getRouter()->urlForRoute('admin_plugins_installed') );
        }

        // validate and save config
        if ( OW::getRequest()->isPost() && !empty($_POST['urls']) )
        {
            $serverList = array();

            // collect server list
            $index = 0;
            foreach ($_POST['urls'] as $url)
            {
                // process url
                $url = trim(strip_tags($url));

                if ( $url )
                {
                    $serverList[] = array(
                        'urls' => $url,
                        'username' => !empty($_POST['username'][$index])
                            ? trim(strip_tags($_POST['username'][$index]))
                            : null,
                        'credential' => !empty($_POST['credential'][$index])
                            ? trim(strip_tags($_POST['credential'][$index]))
                            : null
                    );
                }

                $index++;
            }

            OW::getConfig()->saveConfig('videoim', 'server_list', json_encode($serverList));

            // reload the current page
            OW::getFeedback()->info(OW::getLanguage()->text('videoim', 'settings_updated'));
            $this->redirect();
        }

        // set current page's settings
        if ( !OW::getRequest()->isAjax() )
        {
            $this->setPageHeading(OW::getLanguage()->text('videoim', 'admin_config'));

            // include necessary js and css files
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('videoim')->getStaticCssUrl() . 'admin.css');
        }

        // get default plugin's config values
        $configs = OW::getConfig()->getValues('videoim');
        $serverList = json_decode($configs['server_list']);

        // init view variables
        $this->assign('serverList', $serverList);
    }

    /**
     * Settings
     */
    public function settings()
    {
        $language = OW::getLanguage();
        $service = VIDEOIM_BOL_VideoImService::getInstance();

        $this->setPageHeading($language->text('videoim', 'admin_config'));

        $fieldName = 'track_credits_type';
        $configs = OW::getConfig()->getValues('videoim');

        $settingsForm = new Form('settings_form');
        $settingsForm->setAction(OW::getRouter()->urlForRoute('videoim_admin_settings'));

        $trackCreditsType = new Selectbox($fieldName);
        $trackCreditsType->addValidator(new RequiredValidator());
        $trackCreditsType->addOptions([
            $service::TRACK_CREDITS_BOTH => $language->text('videoim', 'both'),
            $service::TRACK_CREDITS_INITIATOR => $language->text('videoim', 'initiator'),
            $service::TRACK_CREDITS_INTERLOCUTOR => $language->text('videoim', 'interlocutor')
        ]);
        if( isset($configs[$fieldName]) )
        {
            $trackCreditsType->setValue($configs[$fieldName]);
        }
        $trackCreditsType->setLabel($language->text('videoim', 'from_whom_track_credits'));
        $settingsForm->addElement($trackCreditsType);

        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $settingsForm->addElement($submit);

        if ( OW::getRequest()->isPost() && $settingsForm->isValid($_POST) )
        {
            OW::getConfig()->saveConfig('videoim', $fieldName, $_POST[$fieldName]);

            OW::getFeedback()->info($language->text('videoim', 'settings_updated'));

            $this->redirect();
        }

        $this->addForm($settingsForm);
    }
}
