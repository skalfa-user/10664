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
class SKMOBILEAPP_CMP_MobileExperience extends BASE_CLASS_Widget
{
    const IOS = 'skadateios';
    const ANDROID = 'skandroid';
    const PWA = 'pwa';
 
    const IOS_APP_URL = 'ios_app_url';
    const ANDROID_APP_URL = 'android_app_url';

    const CSS_CLASSES = array(
        self::IOS => 'ow_index_app_banner_ios',
        self::ANDROID => 'ow_index_app_banner_and',
        self::PWA => 'ow_index_app_banner_pwa'
    );
 
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $pluginManager = OW::getPluginManager();

        $appUrls = $this->getUrls();

        if ( !$appUrls )
        {
            $this->setVisible(false);

            return;
        }
        elseif ( empty($paramObj->customParamList['banners']) )
        {
            $this->setVisible(false);

            return;
        }

        $banners = $paramObj->customParamList['banners'];
        $banners = array_merge(array($paramObj->customParamList['show_first'] => true), $banners);

        $promos = $this->getPromos($banners);

        if ( count($promos) === 0 )
        {
            $this->setVisible(false);

            return;
        }

        $this->assign('promos', $promos);

        OW::getDocument()->addStyleSheet($pluginManager->getPlugin('skadate')->getStaticCssUrl() . 'mobile_experience.css');
        OW::getDocument()->addStyleSheet($pluginManager->getPlugin('skmobileapp')->getStaticCssUrl() . 'mobile_experience.css');
    }

    /**
     * Get promos
     * 
     * @param array $banners
     * @return array
     */
    protected function getPromos($banners)
    {
        $appUrls = $this->getUrls();
        $promos = array();

        foreach ( array_keys($banners) as $banner )
        {
            if ( !$appUrls[$banner] )
            {
                continue;
            }

            $promos[$banner] = array(
                'app_url' => $appUrls[$banner],
                'css_class' => self::CSS_CLASSES[$banner]
            );
        }

        return $promos;
    }

    /**
     * Get urls
     * 
     * @return array
     */
    protected function getUrls()
    {
        return array(
            self::IOS => OW::getConfig()->getValue('skmobileapp', self::IOS_APP_URL),
            self::ANDROID => OW::getConfig()->getValue('skmobileapp', self::ANDROID_APP_URL),
            self::PWA => SKMOBILEAPP_BOL_Service::getInstance()->getPwaUrl()
        );
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('skadate', 'mobile_experience_widget_title'),
            self::SETTING_ICON => self::ICON_PICTURE,
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getSettingList()
    {
        $config = OW::getConfig();
        $language = OW::getLanguage();

        $appUrls = array(
            self::IOS => $config->getValue('skmobileapp', self::IOS_APP_URL),
            self::ANDROID => $config->getValue('skmobileapp', self::ANDROID_APP_URL),
            self::PWA => SKMOBILEAPP_BOL_Service::getInstance()->getPwaUrl()
        );

        $hasIosAppUrl = !empty($appUrls[self::IOS]);
        $hasAndroidAppUrl = !empty($appUrls[self::ANDROID]);
        $hasPwaUrl = !empty($appUrls[self::PWA]);

        return array(
            'banners' => array(
                'presentation' => self::PRESENTATION_CUSTOM,
                'label' => $language->text('skadate', 'banners_label'),
                'render' => function( $uniqName, $name, $value ) use ($hasIosAppUrl, $hasAndroidAppUrl, $hasPwaUrl) {
                    $document = OW::getDocument();
                    $language = OW::getLanguage();

                    $document->addScript(OW::getPluginManager()->getPlugin('skmobileapp')->getStaticJsUrl() . 'mobile_experience.js');
                    $document->addOnloadScript(UTIL_JsGenerator::composeJsString(';window.SKADATE_ME_SETTINGS({$params});', array(
                        'params' => array(
                            'iosActive' => $hasIosAppUrl,
                            'androidActive' => $hasAndroidAppUrl,
                            'pwaActive' => $hasPwaUrl
                        )
                    )));

                    $input = new CheckboxGroup('banners');
                    $input->setOptions(array(
                        self::IOS => $language->text('skadate', 'ios_label'),
                        self::ANDROID => $language->text('skadate', 'android_label'),
                        self::PWA => $language->text('skmobileapp', 'pwa_label')
                    ));
                    $input->setColumnCount(2);
                    $input->setValue(!empty($value) ? array_keys($value) : null);

                    return $input->renderInput();
                },
                'value' => array(
                    self::IOS => $hasIosAppUrl,
                    self::ANDROID => $hasAndroidAppUrl,
                    self::PWA => $hasPwaUrl
                )
            ),
            'show_first' => array(
                'presentation' => self::PRESENTATION_CUSTOM,
                'label' => $language->text('skadate', 'show_first_label'),
                'render' => function( $uniqName, $name, $value ) use ($hasIosAppUrl, $hasAndroidAppUrl, $hasPwaUrl) {
                    $language = OW::getLanguage();

                    $input = new RadioField('show_first[]');
                    $input->setOptions(array(
                        self::IOS => $language->text('skadate', 'ios_label'),
                        self::ANDROID => $language->text('skadate', 'android_label'),
                        self::PWA => $language->text('skmobileapp', 'pwa_label')
                    ));
                    $input->setValue($value);
                    $input->setColumnCount(2);

                    return $input->renderInput();
                },
                'value' => !$hasAndroidAppUrl && !$hasIosAppUrl 
                    ? self::PWA
                    : ($hasIosAppUrl ? self::IOS : self::ANDROID)
            )
        );
    }

    public static function processSettingList( $settingList, $place, $isAdmin )
    {
        $config = OW::getConfig();

        $appUrls = array(
            self::IOS => $config->getValue('skmobileapp', self::IOS_APP_URL),
            self::ANDROID => $config->getValue('skmobileapp', self::ANDROID_APP_URL),
            self::PWA => SKMOBILEAPP_BOL_Service::getInstance()->getPwaUrl()
        );

        $settingList = parent::processSettingList($settingList, $place, $isAdmin);

        $settingList['show_first'] = array_shift($settingList['show_first']);
        $settingList['banners'] = array_flip($settingList['banners']);

        if ( empty($appUrls[self::IOS]) && isset($settingList['banners'][self::IOS]) )
        {
            unset($settingList['banners'][self::IOS]);
        }

        if ( empty($appUrls[self::ANDROID]) && isset($settingList['banners'][self::ANDROID]) )
        {
            unset($settingList['banners'][self::ANDROID]);
        }

        if ( empty($appUrls[self::PWA]) && isset($settingList['banners'][self::PWA]) )
        {
            unset($settingList['banners'][self::PWA]);
        }

        return $settingList;
    }
}
