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
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.membership.mobile.controllers
 * @since 1.0
 */
class MEMBERSHIP_MCTRL_Subscribe extends OW_MobileActionController
{
    private $membershipService;
    private $userId;
    private $authService;

    public function __construct()
    {
        $this->membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        $this->userId = OW::getUser()->getId();
        $this->authService = BOL_AuthorizationService::getInstance();
    }
    public function index()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $actions = $this->membershipService->getSubscribePageGroupActionList();
        $this->assign('groupActionList', $actions);

        $accTypeName = OW::getUser()->getUserObject()->getAccountType();
        $accType = BOL_QuestionService::getInstance()->findAccountTypeByName($accTypeName);

        $mTypes = $this->membershipService->getTypeList($accType->id);

        /* @var $defaultRole BOL_AuthorizationRole */
        $defaultRole = $this->authService->getDefaultRole();
        /* @var $default MEMBERSHIP_BOL_MembershipType */
        $default = new MEMBERSHIP_BOL_MembershipType();
        $default->roleId = $defaultRole->id;

        $mTypes = array_merge(array($default), $mTypes);

        $userMembership = $this->membershipService->getUserMembership($this->userId);
        $userRoleIds = array($defaultRole->id);

        if ( $userMembership )
        {
            $type = $this->membershipService->findTypeById($userMembership->typeId);
            if ( $type )
            {
                $userRoleIds[] = $type->roleId;
                $this->assign('currentTitle', $this->membershipService->getMembershipTitle($type->roleId));
            }
            $this->assign('current', $userMembership);
            $this->assign('yourMembershipInfoLink', OW::getRouter()->urlForRoute('your_membership_info_mobile', array("membershipId" => $userMembership->typeId, "showCurrentMembershipInfo" => 1, "yourMembership" => 1)));
        }

        $permissions = $this->authService->getPermissionList();
        $perms = array();

        foreach ( $permissions as $permission )
        {
            /* @var $permission BOL_AuthorizationPermission */
            $perms[$permission->roleId][$permission->actionId] = true;
        }
        $plansNumber = 0;
        $mTypesPermissions = array();
        foreach ( $mTypes as $membership )
        {
            $mId = $membership->id;
            if( empty($mId) )  //for a basic membership, because it has't id
            {
                $mId = -1;
            }
            $data = array(
                'id' => $mId,
                'title' => $this->membershipService->getMembershipTitle($membership->roleId),
                'roleId' => $membership->roleId,
                'permissions' => isset($perms[$membership->roleId]) ? $perms[$membership->roleId] : null,
                'current' => in_array($membership->roleId, $userRoleIds),
                'membershipInfoLink' => OW::getRouter()->urlForRoute('membership_info_mobile', array("membershipId" => $mId, "showCurrentMembershipInfo" => 0)),
                'yourMembershipInfoLink' => OW::getRouter()->urlForRoute('your_membership_info_mobile', array("membershipId" => $mId, "showCurrentMembershipInfo" => 0, "yourMembership" => 1))
            );
            $mTypesPermissions[$mId] = $data;
        }

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);
        $this->assign('mTypePermissions', $mTypesPermissions);
        $this->assign('plansNumber', $plansNumber);
        $this->assign('typesNumber', count($mTypes));
        $subscribePageHeaders = $this->membershipUsercreditsPluginInfo();
        $this->setPageHeading($subscribePageHeaders);
    }

    protected function membershipUsercreditsPluginInfo()
    {
        $language = OW::getLanguage();
        if(OW::getPluginManager()->isPluginActive('usercredits'))
        {
            return $language->text('membership', 'membership_and_credits');
        }
        else
        {
            return $language->text('membership', 'memberships');
        }
    }

    protected function getMenu()
    {
        $menuArray = array();
        $event = new BASE_CLASS_EventCollector('base.collect_subscribe_menu');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if(count($data) == 1)
        {
            return new BASE_MCMP_ContentMenu();
        }

        if ( !empty($data) )
        {
            $menuArray = array_merge($menuArray, $data);
        }

        $menu = new BASE_MCMP_ContentMenu();

        foreach ( $menuArray as $item )
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

    public function membershipInfo($params)
    {
        $showCurrentMembershipInfo = true;
        $lang = OW::getLanguage();

        $yourMembership = isset($params['yourMembership']);




        $membersipId = isset($params['membershipId']) ? $params['membershipId'] : null;
        $defaultRole = $this->authService->getDefaultRole();

        $defaultTitle = $this->membershipService->getMembershipTitle($defaultRole->id);
        $this->assign('defaultTitle', $defaultTitle);

        $backUrl = OW::getRouter()->urlForRoute("membership_subscribe");
        $this->setCustomMasterPage($backUrl);

        $userRoleIds = null;

        $actions = $this->membershipService->getSubscribePageGroupActionList();
        $this->assign('groupActionList', $actions);

        $userMembership = $this->membershipService->getUserMembership($this->userId);

        if( $membersipId == -1 )
        {
            /* @var $default MEMBERSHIP_BOL_MembershipType */
            $default = new MEMBERSHIP_BOL_MembershipType();
            $default->roleId = $defaultRole->id;
            /* @var $defaultRole BOL_AuthorizationRole */
            $userRoleIds = array($defaultRole->id);
            $mTypes = array($default);

        }
        else
        {
            $mTypes = array( $this->membershipService->findTypeById($membersipId) );
            //$defaul = $this->authService->get
        }


        if ( $userMembership )
        {
            $type = $this->membershipService->findTypeById($userMembership->typeId);
            if ( $type )
            {
                $userRoleIds[] = $type->roleId;
                $this->assign('currentTitle', $this->membershipService->getMembershipTitle($type->roleId));
            }
            $this->assign('current', $userMembership);
            $this->assign('showCurrentMembershipInfo',  $params['showCurrentMembershipInfo']);

        }

        $permissions = $this->authService->getPermissionList();
        $perms = array();

        foreach ( $permissions as $permission )
        {
            /* @var $permission BOL_AuthorizationPermission */
            $perms[$permission->roleId][$permission->actionId] = true;
        }

        $exclude = $this->membershipService->getUserTrialPlansUsage( $this->userId );
        $mPlans = $this->membershipService->getTypePlanList( $exclude );

        foreach( $mPlans as &$mPlan )
        {
            foreach( $mPlan as &$mP )
            {
                $mP['link'] = OW::getRouter()->urlForRoute('membership_pay_page_mobile', array("planId" => $mP['dto']->id));
            }
        }

        $plansNumber = 0;
        $mTypesPermissions = array();

        foreach ( $mTypes as $membership )
        {
            $mId = $membership->id;
            $plans = isset($mPlans[$mId]) ? $mPlans[$mId] : null;
            $data = array(
                'id' => $mId,
                'title' => $this->membershipService->getMembershipTitle($membership->roleId),
                'roleId' => $membership->roleId,
                'permissions' => isset($perms[$membership->roleId]) ? $perms[$membership->roleId] : null,
                'current' => isset($userRoleIds) ? in_array($membership->roleId, $userRoleIds) : null,
                'plans' =>  $plans
            );
            $plansNumber += count($plans);
            $mTypesPermissions[$membersipId] = $data;

            if($yourMembership)
            {
                $this->setPageHeading($lang->text('membership', 'membershipInfo'));
            }
            else
            {
                $this->setPageHeading($data['title']);
            }

        }

        $subscribePageHeaders = $this->membershipUsercreditsPluginInfo();

        $this->assign('showCurrentMembershipInfo', $showCurrentMembershipInfo);
        $this->assign('membershipId', $membersipId);
        $this->assign('mTypePermissions', $mTypesPermissions);
        $this->assign('plansNumber', $plansNumber);
        $this->assign('typesNumber', count($mTypes));
        $this->assign('subscribePageHeader', $subscribePageHeaders);

        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $this->assign('labels', $dataLabels);
    }

    public function payPage( $params )
    {
        $planId = $params['planId'];
        $exclude = $this->membershipService->getUserTrialPlansUsage(OW::getUser()->getId());
        $mPlans = $this->membershipService->getTypePlanList($exclude);
        $planFormat = null;

        foreach( $mPlans as &$mPlan )
        {
            foreach( $mPlan as &$mP )
            {
                if( $mP['dto']->id == $planId )
                {
                    $planFormat = $mP['plan_format'];
                }
            }
        }

        $membershipDto = $this->membershipService->findTypeByPlanId($planId);

        $roleId = $membershipDto->roleId;

        $membershipTitle = $this->membershipService->getMembershipTitle($roleId);

        $backUrl = OW::getRouter()->urlForRoute("membership_info_mobile", array("membershipId" => $membershipDto->id, "showCurrentMembershipInfo" => 0));
        $this->setCustomMasterPage($backUrl);
        $form = new SubscribeForm();
        $this->addForm($form);
        $form->getElement('plan')->setValue($planId);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process();
        }

        $gateways = BOL_BillingService::getInstance()->getActiveGatewaysList();

        $this->assign('gatewaysActive', (bool) $gateways);
        $this->assign('planFormat', $planFormat);
        $this->assign('membershipTitle', $membershipTitle);

        $this->setPageHeading(OW::getLanguage()->text('membership', 'payment_provider'));

    }

    protected function setCustomMasterPage($backUrl)
    {
        $masterPage = OW::getDocument()->getMasterPage();
        $masterPage->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
        $masterPage->setLButtonData(array(OW_MobileMasterPage::BTN_DATA_CLASS => "owm_nav_menu owm_nav_back", OW_MobileMasterPage::BTN_DATA_HREF => $backUrl));
    }
}

class SubscribeForm extends MEMBERSHIP_CLASS_SubscribeForm
{
    protected function addFields()
    {
        $gatewaysField = new MobileBillingGatewaySelectionField('gateway');
        $gatewaysField->setRequired();
        $this->addElement($gatewaysField);

        OW::getDocument()->addOnloadScript(";
            var \$wrapper = $('#{$gatewaysField->getId()}').parents('ul'); 
            \$wrapper.find('input').bind('change', function(e){
                $('#{$gatewaysField->getId()}').parents('ul').find('input').each(function(i, el){
                    $(el).parent().removeClass('active');
                });
                var checked_element = \$wrapper.find('input:checked').get(0);
                $(checked_element).parent().addClass('active');
            });
        ");

        $submit = new Submit('subscribe');
        $submit->setValue(OW::getLanguage()->text('membership', 'checkout'));
        $this->addElement($submit);

        $planId = new HiddenField('plan');
        $this->addElement($planId);
    }
}


