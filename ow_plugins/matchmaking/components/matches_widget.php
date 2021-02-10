<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
/**
 * User matches list component
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.components
 * @since 1.0
 */
class MATCHMAKING_CMP_MatchesWidget extends BASE_CMP_UsersWidget
{

    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int) $params->customParamList['count'];
        $service = MATCHMAKING_BOL_Service::getInstance();
        $userId = $params->additionalParamList['entityId'];

        $toolbar = array(
            'newest_first' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('matchmaking_members_page_sorted', array('sortOrder' => 'newest'))
            ),
            'most_compatible_first' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('matchmaking_members_page_sorted', array('sortOrder' => 'compatible'))
            )
        );

        $matchCount = $service->findMatchCount($userId);

        if ($matchCount > $count)
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar['newest_first']));
        }

        $newestList = $service->findMatchList($userId, 0, $count, 'newest');

        $resultList = array(
            'newest_first' => array(
                'menu-label' => OW::getLanguage()->text('matchmaking', 'latest'),
                'menu_active' => true,
                'userIds' => $this->getIdList( $newestList ),
                'toolbar' => ( $matchCount > $count ? array($toolbar['newest_first']) : false ),
                ),
            'most_compatible_first' => array(
                'menu-label' => OW::getLanguage()->text('matchmaking', 'top'),
                'userIds' => $this->getIdList( $service->findMatchList($userId, 0, $count, 'compatible') ),
                'toolbar' => ( $matchCount > $count ? array($toolbar['most_compatible_first']) : false ),
                )
        );

        return $resultList;
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('base', 'user_list_widget_settings_count'),
            'value' => '9'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('matchmaking', 'matches_index'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    protected function getIdList( $users )
    {
        $resultArray = array();

        if ( $users )
        {
            foreach ( $users as $item )
            {
                $resultArray[] = $item['id'];
            }
        }

        return $resultArray;
    }
}