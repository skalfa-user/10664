<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_CMP_MobileExperience extends BASE_CLASS_Widget
{
    const IOS = 'skadateios';
    const ANDROID = 'skandroid';

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $active = array(
            self::IOS => OW::getPluginManager()->isPluginActive(self::IOS),
            self::ANDROID => OW::getPluginManager()->isPluginActive(self::ANDROID)
        );

        if ( !$active[self::IOS] && !$active[self::ANDROID] )
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

        $event = new BASE_CLASS_EventCollector('app.promo_info');
        OW::getEventManager()->trigger($event);
        $data = call_user_func_array('array_merge', $event->getData());
        $promos = array();

        foreach ( array_keys($banners) as $bunner )
        {
            if ( !isset($data[$bunner]) )
            {
                continue;
            }

            $promos[$bunner] = array(
                'app_url' => $data[$bunner]['app_url']
            );
        }

        if ( count($promos) === 0 )
        {
            $this->setVisible(false);

            return;
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('skadate')->getStaticCssUrl() . 'mobile_experience.css');
        $this->assign('promos', $promos);
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
        $language = OW::getLanguage();
        $pluginManager = OW::getPluginManager();

        return array(
            'banners' => array(
                'presentation' => self::PRESENTATION_CUSTOM,
                'label' => $language->text('skadate', 'banners_label'),
                'render' => function( $uniqName, $name, $value )
                {
                    OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('skadate')->getStaticJsUrl() . 'mobile_experience.js');
                    OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString(';window.SKADATE_ME_SETTINGS({$params});', array(
                        'params' => array(
                            'iosActive' => OW::getPluginManager()->isPluginActive(SKADATE_CMP_MobileExperience::IOS),
                            'androidActive' => OW::getPluginManager()->isPluginActive(SKADATE_CMP_MobileExperience::ANDROID)
                        )
                    )));

                    $input = new CheckboxGroup('banners');
                    $input->setOptions(array(
                        SKADATE_CMP_MobileExperience::IOS => OW::getLanguage()->text('skadate', 'ios_label'),
                        SKADATE_CMP_MobileExperience::ANDROID => OW::getLanguage()->text('skadate', 'android_label')
                    ));
                    $input->setColumnCount(2);
                    $input->setValue(!empty($value) ? array_keys($value) : null);

                    return $input->renderInput();
                },
                'value' => array(
                    self::IOS => $pluginManager->isPluginActive(self::IOS),
                    self::ANDROID => $pluginManager->isPluginActive(self::ANDROID)
                )
            ),
            'show_first' => array(
                'presentation' => self::PRESENTATION_CUSTOM,
                'label' => $language->text('skadate', 'show_first_label'),
                'render' => function( $uniqName, $name, $value )
                {
                    $input = new RadioField('show_first[]');
                    $input->setOptions(array(
                        SKADATE_CMP_MobileExperience::IOS => OW::getLanguage()->text('skadate', 'ios_label'),
                        SKADATE_CMP_MobileExperience::ANDROID => OW::getLanguage()->text('skadate', 'android_label')
                    ));
                    $input->setValue($value);
                    $input->setColumnCount(2);

                    return $input->renderInput();
                },
                'value' => $pluginManager->isPluginActive(self::IOS) ? self::IOS : self::ANDROID
            )
        );
    }

    public static function processSettingList( $settingList, $place, $isAdmin )
    {
        $settingList = parent::processSettingList($settingList, $place, $isAdmin);

        $settingList['show_first'] = array_shift($settingList['show_first']);
        $settingList['banners'] = array_flip($settingList['banners']);

        if ( !OW::getPluginManager()->isPluginActive(self::IOS) && isset($settingList['banners'][self::IOS]) )
        {
            unset($settingList['banners'][self::IOS]);
        }

        if ( !OW::getPluginManager()->isPluginActive(self::ANDROID) && isset($settingList['banners'][self::ANDROID]) )
        {
            unset($settingList['banners'][self::ANDROID]);
        }

        return $settingList;
    }
}