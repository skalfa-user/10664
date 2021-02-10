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
 * Profile view component 
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.components
 * @since 1.0
 */
class MATCHMAKING_CMP_CompatibilityWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->additionalParamList;
        
        if ( !OW::getUser()->isAuthenticated() || OW::getUser()->getId() == $params['entityId'] )
        {
            $this->setVisible(false);
            return;
        }
        
        $service = MATCHMAKING_BOL_Service::getInstance();

        $percent = $service->getCompatibility(OW::getUser()->getId(), $params['entityId']);
        
        $this->assign('percent', $percent);

    }
    
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('matchmaking', 'compatibility_with'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_FREEZE => true
            
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}