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
 * Membership subscribe page controller.
 *
 * @author Egor Bulgakov, Sergey Pryadkin <egor.bulgakov@gmail.com, GiperProger@gmail.com>
 * @package ow.ow_plugins.membership.controllers
 * @since 1.8.2
 */
class MEMBERSHIP_CTRL_Subscribe extends OW_ActionController
{

    public function index()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        $form = new MEMBERSHIP_CLASS_SubscribeForm();
        $this->addForm($form);

        $menu = $this->getMenu();

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process();
        }

        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();

        $actions = $membershipService->getSubscribePageGroupActionList();
        $this->assign('groupActionList', $actions);


        $accTypeName = OW::getUser()->getUserObject()->getAccountType();
        $accType = BOL_QuestionService::getInstance()->findAccountTypeByName($accTypeName);

        $mTypes = $membershipService->getTypeList($accType->id);


        /* @var $defaultRole BOL_AuthorizationRole */
        $defaultRole = $authService->getDefaultRole();

        /* @var $default MEMBERSHIP_BOL_MembershipType */
        $default = new MEMBERSHIP_BOL_MembershipType();
        $default->roleId = $defaultRole->id;

        $mTypes = array_merge(array($default), $mTypes);

        $userId = OW::getUser()->getId();
        $userMembership = $membershipService->getUserMembership($userId);
        $userRoleIds = array($defaultRole->id);
        
        if ( $userMembership )
        {
            $type = $membershipService->findTypeById($userMembership->typeId);
            if ( $type )
            {
                $userRoleIds[] = $type->roleId;
                $this->assign('currentTitle', $membershipService->getMembershipTitle($type->roleId));
            }

            $this->assign('current', $userMembership);
        }
        
        $permissions = $authService->getPermissionList();

        $perms = array();
        foreach ( $permissions as $permission )
        {
            /* @var $permission BOL_AuthorizationPermission */
            $perms[$permission->roleId][$permission->actionId] = true;
        }

        $exclude = $membershipService->getUserTrialPlansUsage($userId);

        $mPlans = $membershipService->getTypePlanList($exclude);

        $plansNumber = 0;
        $mTypesPermissions = array();
        foreach ( $mTypes as $membership )
        {
            $mId = $membership->id;
            $plans = isset($mPlans[$mId]) ? $mPlans[$mId] : [];
            $data = array(
                'id' => $mId,
                'title' => $membershipService->getMembershipTitle($membership->roleId),
                'roleId' => $membership->roleId,
                'permissions' => isset($perms[$membership->roleId]) ? $perms[$membership->roleId] : null,
                'current' => in_array($membership->roleId, $userRoleIds),
                'plans' => $plans
            );
            $plansNumber += count($plans);
            $mTypesPermissions[$mId] = $data;
        }

        $this->assign('mTypePermissions', $mTypesPermissions);
        $this->assign('plansNumber', $plansNumber);
        $this->assign('typesNumber', count($mTypes));

        if( !is_null($menu) )
        {
            $this->addComponent('menu', $menu);
        }

        // collecting labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $this->assign('labels', $dataLabels);
        
        $gateways = BOL_BillingService::getInstance()->getActiveGatewaysList();
        $this->assign('gatewaysActive', (bool) $gateways);
        
        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('membership', 'subscribe_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $jsParams = array(
            'billingGatewayExtraInfoUrl' => OW::getRouter()->urlForRoute('base_billing_gateway_extra_info'),
            'langKey' => 'extra_info_membership'
        );

        $script = ' var info = new gatewayExtraInfo(); info.init('.  json_encode($jsParams).' );';
        OW::getDocument()->addOnloadScript($script);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'gateway_actions.js');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('membership')->getStaticCssUrl() . 'subscribe_page.css');

    }

    /**
     * @return BASE_CMP_ContentMenu or null
     */
    protected function getMenu()
    {
        $event = new BASE_CLASS_EventCollector('base.collect_subscribe_menu');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if(count($data) <= 1)
        {
            return null;
        }

        $menu = new BASE_CMP_ContentMenu();

        foreach ( $data as $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($item['label']);
            $menuItem->setIconClass($item['iconClass']);
            $menuItem->setUrl($item['url']);
            $menuItem->setKey($item['key']);
            $menuItem->setOrder(empty($item['order']) ? 999 : $item['order']);
            $menu->addElement($menuItem);
        }

        return $menu;
    }
}
