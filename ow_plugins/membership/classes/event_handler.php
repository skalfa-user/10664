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
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_plugins.membership.classes
 * @since 1.6.0
 */
class MEMBERSHIP_CLASS_EventHandler
{

    /* predefined membership events */
    const ON_DELIVER_SALE_NOTIFICATION = 'membership.deliver_sale_notification';

    /**
     * @var MEMBERSHIP_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return MEMBERSHIP_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function deleteUserMembership( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            MEMBERSHIP_BOL_MembershipService::getInstance()->deleteUserTrialsByUserId($userId);
            MEMBERSHIP_BOL_MembershipService::getInstance()->deleleUserMembershipByUserId($userId);
        }
    }

    public function deleteRole( OW_Event $event )
    {
        $params = $event->getParams();

        $roleId = (int) $params['roleId'];

        if ( $roleId > 0 )
        {
            MEMBERSHIP_BOL_MembershipService::getInstance()->deleteUserMembershipsByRoleId($roleId);
            MEMBERSHIP_BOL_MembershipService::getInstance()->deleteMembershipTypeByRoleId($roleId);
        }
    }

    public function addAdminNotification( BASE_CLASS_EventCollector $coll )
    {
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

        $types = $membershipService->getTypeListWithPlans();
        $plans = 0;

        if ( $types )
        {
            foreach ( $types as $type )
            {
                if ( !empty($type['plans']) )
                {
                $plans += count($type['plans']);
            }
        }
        }

        if ( !$types || !$plans )
        {
            $coll->add(
                OW::getLanguage()->text(
                    'membership',
                    'plugin_configuration_notice',
                    array('url' => OW::getRouter()->urlForRoute('membership_admin'))
                )
            );
        }
    }

    public function adsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('membership');
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'membership' => array(
                    'label' => $language->text('membership', 'auth_group_label')
                )
            )
        );
    }

    public function billingAddGatewayProduct( BASE_CLASS_EventCollector $event )
    {
        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $types = $service->getTypePlanList();

        if ( !$types )
        {
            return;
        }

        foreach ( $types as $type )
        {
            foreach ( $type as $plan )
            {
                $data[] = array(
                    'pluginKey' => 'membership',
                    'label' => $plan['plan_format'],
                    'entityType' => 'membership_plan',
                    'entityId' => $plan['dto']->id
                );
            }
        }

        if ( empty($data) )
        {
            return;
        }

        $event->add($data);
    }

    public function onCollectProfileActionToolbarItem( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( !OW::getUser()->isAuthorized('membership') )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $linkId = uniqid('toolbar-action-set-membership');

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $types = $service->getTypeList();

        if ( !$types )
        {
            return;
        }

        $label = OW::getLanguage()->text('membership', 'edit_membership');
        $script =
        '$("#' . $linkId . '").click(function(){
            document.setMembershipFloatBox = OW.ajaxFloatBox(
                "MEMBERSHIP_CMP_SetMembership",
                { userId: ' . $userId . ' },
                { width: 500, title: ' . json_encode($label) . ' }
            );
        });';

        OW::getDocument()->addOnloadScript($script);

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "membership.edit_membership",
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $label,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 2,
            
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation')
        );

        $event->add($resultArray);
    }

    public function onAuthLayerCheck( BASE_CLASS_EventCollector $event )
    {
        /*$params = $event->getParams();
        if ( empty($params['actionName']) )
        {
            return;
        }

        $groupName = $params['groupName'];
        $actionName = $params['actionName'];
        $userId = $params['userId'];

        $authService = BOL_AuthorizationService::getInstance();
        $action = $authService->findAction($groupName, $actionName);

        if ( !$action )
        {
            return;
        }

        $isAuthorized = $authService->isActionAuthorizedForUser($userId, $groupName, $actionName, $params['ownerId']);

        $data = array(
            'pluginKey' => 'membership',
            'priority' => 1,
            'permission' => $isAuthorized
        );

        $event->add($data);*/
    }

    public function onAuthLayerCheckCollectError( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $actionName = $params['actionName'];
        $groupName = $params['groupName'];

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();
        $action = $authService->findAction($groupName, $actionName);

        if ( !$action )
        {
            return;
        }

        $canPurchase = $service->actionCanBePurchased($action->id);

        if ( !$canPurchase )
        {
            return;
        }

        $data = array(
            'pluginKey' => 'membership',
            'label' => OW::getLanguage()->text('membership', 'upgrade'),
            'url' => OW::getRouter()->urlForRoute('membership_subscribe'),
            'priority' => 1
        );

        $event->add($data);
    }

    public function getPluginForMenuDesktop( BASE_CLASS_EventCollector $event )
    {

        $event->add(
            array(
                'label' => OW::getLanguage()->text('membership', 'membership'),
                'url' => OW::getRouter()->urlForRoute('membership_subscribe'),
                'iconClass' => 'ow_ic_moderator',
                'key' => 'membership',
                'order' => 1
            )
        );
    }

    public function onDeliverSaleNotification( OW_Event $event )
    {
        $params = $event->getParams();
        $data['send_renewed_membership_email'] = boolval(true & $params['is_rebill']);
        $data['send_purchased_membership_email'] = boolval(true & !$params['is_rebill']);
        $event->setData($data);
    }

    public function init()
    {
        $this->genericInit();
        $em = OW::getEventManager();

        $em->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'deleteUserMembership'));
        $em->bind(BOL_AuthorizationService::ON_BEFORE_ROLE_DELETE, array($this, 'deleteRole'));
        $em->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        $em->bind('base.billing_add_gateway_product', array($this, 'billingAddGatewayProduct'));
        $em->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onCollectProfileActionToolbarItem'));
        $em->bind('base.collect_subscribe_menu', array($this, 'getPluginForMenuDesktop'));
        $em->bind(MEMBERSHIP_CLASS_EventHandler::ON_DELIVER_SALE_NOTIFICATION, array($this, 'onDeliverSaleNotification'));
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $plugin = OW::getPluginManager()->getPlugin('membership');

        $classesToAutoload = array(
            'RadioGroupItemField' => $plugin->getRootDir() . 'classes' . DS . 'radio_group_item_field.php'
        );

        OW::getAutoloader()->addClassArray($classesToAutoload);

        $em->bind('ads.enabled_plugins', array($this, 'adsEnabled'));
        $em->bind('authorization.layer_check', array($this, 'onAuthLayerCheck'));
        $em->bind('authorization.layer_check_collect_error', array($this, 'onAuthLayerCheckCollectError'));
    }
}
