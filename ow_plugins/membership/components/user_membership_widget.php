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
 * User membership widget component
 * for user profile page
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.components
 * @since 1.0
 */
class MEMBERSHIP_CMP_UserMembershipWidget extends BASE_CLASS_Widget
{
    private $membershipService;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        
        $userId = (int) $paramObj->additionalParamList['entityId'];
        $viewerId = OW::getUser()->getId();
        
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
        
        $isModerator = OW::getUser()->isAuthorized('membership');
        $this->assign('isModerator', $isModerator);
        $this->assign('isOwner', $viewerId == $userId);
        
        $type = $this->membershipService->findTypeById($membership->typeId);
        $this->assign('title', $this->membershipService->getMembershipTitle($type->roleId));
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