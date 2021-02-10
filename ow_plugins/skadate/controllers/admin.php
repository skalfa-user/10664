<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @var SKADATE_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = SKADATE_BOL_Service::getInstance();
    }

    public function uninstall()
    {
        
    }

    public function settings()
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();
        $licenseInfo = json_decode($config->getValue('skadate', 'license_info'), true);

        $pluginTitle = OW::getPluginManager()->getPlugin("skadate")->getDto()->getTitle();
        $this->setPageHeading($language->text("skadate", "settings_page_headeing", array("pluginTitle" => $pluginTitle)));

        $licenseKey = $config->getValue('skadate', 'license_key');
        $branding = $config->getValue('skadate', 'brand_removal');
        $this->assign('data',
            array(
            $language->text('skadate', 'info_name_label') => array_key_exists('registeredname', $licenseInfo) ? $licenseInfo['registeredname'] : 'None',
            $language->text('skadate', 'info_lkey_label') => $licenseKey,
            $language->text('skadate', 'info_domain_label') => array_key_exists('validdomain', $licenseInfo) ? $licenseInfo['validdomain'] : 'None',
            $language->text('skadate', 'info_ip_label') => array_key_exists('validip', $licenseInfo) ? $licenseInfo['validip'] : 'None',
            $language->text('skadate', 'info_dir_label') => array_key_exists('validdirectory', $licenseInfo) ? $licenseInfo['validdirectory'] : 'None',
            $language->text('skadate', 'info_brand_label') => $branding ? 'Yes' : 'No',
            $language->text('skadate', 'info_created_label') => array_key_exists('regdate', $licenseInfo) ? $licenseInfo['regdate'] : 'None',
            $language->text('skadate', 'info_expires_label') => array_key_exists('nextduedate', $licenseInfo) ? $licenseInfo['nextduedate'] : 'None'
        ));

        $url = OW::getRouter()->urlFor("SKADATE_CTRL_Admin", "checkLicense");
        $params = array(BOL_StorageService::URI_VAR_BACK_URI => OW::getRequest()->getRequestUri());
        $this->assign("validateUrl", OW::getRequest()->buildUrlQueryString($url, $params));
    }

    public function invalidKey()
    {
        if ( !trim(OW::getConfig()->getValue("skadate", "license_key")) )
        {
            $this->assign("input", true);
        }

        $url = OW::getRouter()->urlFor("SKADATE_CTRL_Admin", "checkLicense");
        $params = array(BOL_StorageService::URI_VAR_BACK_URI => OW::getRequest()->getRequestUri());
        $this->assign("validateUrl", OW::getRequest()->buildUrlQueryString($url, $params));
    }

    public function checkLicense()
    {
        if ( !empty($_GET["key"]) )
        {
            OW::getConfig()->saveConfig("skadate", "license_key", trim($_GET["key"]));
        }

        if ( SKADATE_BOL_LicenceService::getInstance()->validateKey() )
        {
            if ( OW::getConfig()->getValue("skadate", "license_key_valid") )
            {
                OW::getFeedback()->info(OW::getLanguage()->text("skadate", "validation_success"));
            }
            else
            {
                OW::getFeedback()->error(OW::getLanguage()->text("skadate", "validation_failed"));
            }
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text("skadate", "validation_failed_tech"));
        }

        $backUrl = OW::getRouter()->urlForRoute("admin_default");

        if ( isset($_GET[BOL_StorageService::URI_VAR_BACK_URI]) )
        {
            $backUrl = OW_URL_HOME . urldecode($_GET[BOL_StorageService::URI_VAR_BACK_URI]);
        }

        $this->redirect($backUrl);
    }
}
