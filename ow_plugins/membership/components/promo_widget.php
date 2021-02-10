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
 * Membership promo widget component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.components
 * @since 1.5.3
 */
class MEMBERSHIP_CMP_PromoWidget extends BASE_CLASS_Widget
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

        $limit = 3;
        $actions = $this->membershipService->getPromoActionList(OW::getUser()->getId(), $limit);
        if ( $actions == null )
        {
            $this->setVisible(false);
            return;
        }

        $showMore = count($actions) == ($limit + 1);
        $this->assign('showMore', $showMore);
        if ( $showMore )
        {
            array_pop($actions);
        }
        $this->assign('actions', $actions);
        
        $membership = $this->membershipService->getUserMembership($userId);
        $this->assign('membership', $membership);

        if ( $membership )
        {
            $type = $this->membershipService->findTypeById($membership->typeId);
            $this->assign('title', $this->membershipService->getMembershipTitle($type->roleId));
        }
        else
        {
            $authService = BOL_AuthorizationService::getInstance();
            $roles = $authService->getRoleListOfUsers(array($userId), false);
            if ( isset($roles[$userId]) )
            {
                $this->assign('title', $roles[$userId]['label']);
            }
        }

        $script = '$("#btn-sidebar-upgrade").click(function(){
            document.location.href = '.json_encode(OW::getRouter()->urlForRoute('membership_subscribe')).';
        });';
        OW::getDocument()->addOnloadScript($script);
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('membership', 'upgrade'),
            self::SETTING_ICON => self::ICON_UP_ARROW,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}