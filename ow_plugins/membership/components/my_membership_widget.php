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
 * My membership widget component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.components
 * @since 1.0
 */
class MEMBERSHIP_CMP_MyMembershipWidget extends BASE_CLASS_Widget
{
    private $membershipService;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        
        $userId = OW::getUser()->getId();
        
        if ( !$userId )
        {
            $this->setVisible(false); 
            return;
        }
        
        $membership = $this->membershipService->getUserMembership($userId);

        if ( !$membership )
        {
            $this->setVisible(false);
            return;
        }
        
        $this->assign('membership', $membership);
        
        $type = $this->membershipService->findTypeById($membership->typeId);
        if ( $type )
        {
            $this->assign('title', $this->membershipService->getMembershipTitle($type->roleId));
        }
        
        $this->setSettingValue(
            self::SETTING_TOOLBAR,
            array(
                array(
                    'label' => OW::getLanguage()->text('membership', 'subscribe'),
                    'href' => OW::getRouter()->urlForRoute('membership_subscribe')
                )
            )
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('membership', 'my_membership'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}