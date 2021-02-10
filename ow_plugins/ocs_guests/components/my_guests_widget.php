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

class OCSGUESTS_CMP_MyGuestsWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $count = (int) $params->customParamList['count'];
        $service = OCSGUESTS_BOL_Service::getInstance();
        
        $userId = OW::getUser()->getId();
        $guests = $service->findGuestsForUser($userId, 1, $count);

        if ( !$guests )
        {
        	$this->setVisible(false);
        	return;
        }

        $userIdList = array();
        foreach ( $guests as $guest )
        {
        	array_push($userIdList, $guest->guestId);
        }
        
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
        
        foreach ( $avatars as &$item )
        {
        	$item['class'] = 'ow_guest_avatar';
        }
        
        $event = new OW_Event('bookmarks.is_mark', array(), $avatars);
        OW::getEventManager()->trigger($event);
        
        if ( $event->getData() )
        {
            $avatars = $event->getData();
        }

        $this->assign('avatars', $avatars);
        $this->assign('guests', $guests);

        $total = $service->countGuestsForUser($userId);
        
        if ( $total > $count )
        {
	        $toolbar = array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('ocsguests.list')
            );
	        $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar));
        }
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('ocsguests', 'guest_list_widget_settings_count'),
            'value' => '6'
        );

        return $settingList;
    }
    
    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => true,
        	self::SETTING_SHOW_TITLE => true,
        	self::SETTING_ICON => self::ICON_FRIENDS,
        	self::SETTING_TITLE => OW::getLanguage()->text('ocsguests', 'viewed_profile')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}