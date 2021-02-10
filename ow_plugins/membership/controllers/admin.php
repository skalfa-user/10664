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
 * Membership admin controller.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.controllers
 * @since 1.0
 */
class MEMBERSHIP_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    protected $membershipService;

    const SESSION_ACCOUNT_TYPE = "MEMBERSHIP_ACCOUNT_TYPE";

    public function __construct()
    {
        parent::__construct();

        $this->membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
    }

    protected function getMenu( $active = 'memberships' )
    {
        $language = OW::getLanguage();
        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('membership', 'admin_menu_memberships'));
        $item->setUrl(OW::getRouter()->urlForRoute('membership_admin'));
        $item->setKey('memberships');
        $item->setActive($active == 'memberships');
        $item->setIconClass('ow_ic_update');
        $item->setOrder(0);

        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('membership', 'admin_menu_subscribe'));
        $item->setUrl(OW::getRouter()->urlForRoute('membership_admin_subscribe'));
        $item->setKey('subscribe');
        $item->setActive($active == 'subscribe');
        $item->setIconClass('ow_ic_script');
        $item->setOrder(1);

        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('membership', 'admin_menu_browse_users'));
        $item->setUrl(OW::getRouter()->urlForRoute('membership_admin_browse_users_st'));
        $item->setKey('users');
        $item->setActive($active == 'users');
        $item->setIconClass('ow_ic_user');
        $item->setOrder(2);

        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('membership', 'admin_menu_settings'));
        $item->setUrl(OW::getRouter()->urlForRoute('membership_admin_settings'));
        $item->setKey('settings');
        $item->setActive($active == 'settings');
        $item->setIconClass('ow_ic_gear_wheel');
        $item->setOrder(3);

        $menuItems[] = $item;
        
        return new BASE_CMP_ContentMenu($menuItems);
    }

    public function index()
    {
        $lang = OW::getLanguage();
        $this->addComponent('menu', $this->getMenu('memberships'));

        $accType = null;
        if ( isset($_GET['accountType']) )
        {
            OW::getSession()->set(self::SESSION_ACCOUNT_TYPE, trim($_GET['accountType']));
        }

        if ( OW::getSession()->get(self::SESSION_ACCOUNT_TYPE) )
        {
            $accType = OW::getSession()->get(self::SESSION_ACCOUNT_TYPE);
        }

        $accTypes = BOL_QuestionService::getInstance()->findAllAccountTypesWithLabels();
        $this->assign('showAccTypes', count($accTypes) > 1);

        $accKeys = array_keys($accTypes);
        $accType = (!isset($accType) || !in_array($accType, $accKeys) ) ? $accKeys[0] : $accType;

        $accountTypeDto = BOL_QuestionService::getInstance()->findAccountTypeByName($accType);

        $memberships = $this->membershipService->getTypeListWithPlans($accountTypeDto->id);
        $this->assign('memberships', $memberships);

        $assignedMemberships = array();
        foreach ($memberships as $membership) 
        {
            $assignedMemberships[] = $membership['roleId'];
        }

        $msForm = OW::getClassInstance('MEMBERSHIP_CLASS_AddMembershipForm', $assignedMemberships);

        $this->addForm($msForm);

        if ( OW::getRequest()->isPost() )
        {
            $periodUnitsList = MEMBERSHIP_BOL_MembershipService::getInstance()->getPeriodUnitsList();
            
            switch ( $_POST['form_name'] )
            {
                case 'add-membership-form':
                    if ( $msForm->isValid($_POST) && $msForm->process() )
                    {
                        OW::getFeedback()->info($lang->text('membership', 'membership_added'));
                        $this->redirect();
                    }
                    break;

                case 'edit-plans-form':
                    if ( isset($_POST['periods']) )
                    {
                        foreach ( $_POST['periods'] as $planId => $period )
                        {
                            $plan = $this->membershipService->findPlanById($planId);
                            if ( $plan )
                            {
                                $plan->period = isset($_POST['periods'][$planId]) && intval($_POST['periods'][$planId]) ? $_POST['periods'][$planId] : $plan->period;

                                $plan->periodUnits = !empty($_POST['periodUnits'][$planId]) && in_array($_POST['periodUnits'][$planId], $periodUnitsList) ? $_POST['periodUnits'][$planId] : $periodUnitsList[0];
                                $plan->price = isset($_POST['prices'][$planId]) && floatval($_POST['prices'][$planId]) ? $_POST['prices'][$planId] : $plan->price;
                                $plan->recurring = isset($_POST['recurring'][$planId]) ? (bool) $_POST['recurring'][$planId] : false;
                                $this->membershipService->updatePlan($plan);
                            }
                        }
                    }

                    if ( isset($_POST['paid_periods']) )
                    {
                        foreach ( $_POST['paid_periods'] as $index => $period )
                        {
                            if ( !empty($period) && !empty($_POST['paid_prices'][$index]) )
                            {
                                $plan = new MEMBERSHIP_BOL_MembershipPlan();
                                $plan->period = intval($period);
                                $plan->price = floatval($_POST['paid_prices'][$index]);
                                $plan->periodUnits = !empty($_POST['paid_period_units'][$index]) && in_array($_POST['paid_period_units'][$index], $periodUnitsList) ? $_POST['paid_period_units'][$index] : $periodUnitsList[0];
                                $plan->recurring = isset($_POST['paid_recurring'][$index]);
                                $plan->typeId = $_POST['type_id'];
                                $this->membershipService->addPlan($plan);
                            }
                        }
                    }
                    
                    if ( isset($_POST['trial_periods']) )
                    {
                        foreach ( $_POST['trial_periods'] as $index => $period )
                        {
                            if ( !empty($period) )
                            {
                                $plan = new MEMBERSHIP_BOL_MembershipPlan();
                                $plan->period = intval($period);
                                $plan->periodUnits = !empty($_POST['trial_period_units'][$index]) && in_array($_POST['trial_period_units'][$index], $periodUnitsList) ? $_POST['trial_period_units'][$index] : $periodUnitsList[0];
                                $plan->price = 0;
                                $plan->recurring = 0;
                                $plan->typeId = $_POST['type_id'];
                                $this->membershipService->addPlan($plan);
                            }
                        }
                    }

                    OW::getFeedback()->info($lang->text('membership', 'plans_updated'));
                    $this->redirect();

                    break;
            }
        }

        $form = new MEMBERSHIP_CLASS_AccTypeSelectForm();
        $this->addForm($form);

        $form->getElement('accountType')->setValue($accType);

        $users = array();
        if ( $memberships )
        {
            foreach ( $memberships as $type )
            {
                $users[$type['id']] = $this->membershipService->countUsersByMembershipType($type['id']);
            }
        }
        $this->assign('users', $users);

        $msForm->getElement('accType')->setValue($accountTypeDto->id);

        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());
        $this->assign('accTypesUrl', OW::getRouter()->urlForRoute('questions_account_types'));

        $this->setPageHeading($lang->text('membership', 'admin_page_heading_memberships'));

        $lang->addKeyForJs('membership', 'no_types_selected');
        $lang->addKeyForJs('membership', 'type_delete_confirm');

        $script =
        '$("#types a.edit_type").click(function(){
            document.editMembershipFloatBox = OW.ajaxFloatBox(
                "MEMBERSHIP_CMP_EditMembership",
                { typeId: $(this).data("id") },
                { width: 660, title: ' . json_encode($lang->text('membership', 'edit_membership')) . ' }
            );
        });

        $("#types a.delete_type").click(function(){
            if ( $(this).data("count") ) {
                document.deleteMembershipFloatBox = OW.ajaxFloatBox(
                    "MEMBERSHIP_CMP_DeleteMembership",
                    { typeId: $(this).data("id") },
                    { width: 400, title: ' . json_encode($lang->text('membership', 'delete_membership')) . ' }
                );
            }
            else if ( confirm('.json_encode($lang->text('membership', 'type_delete_confirm')).') )
            {
                $.ajax({
                    type: "POST",
                    url: ' . json_encode(OW::getRouter()->urlForRoute('membership_delete_type')) . ',
                    data: { typeId : $(this).data("id") },
                    dataType: "json",
                    success : function(data){
                        if ( data.result ){
                            document.location.reload();
                        }
                    }
                });
            }
        });
        ';

        OW::getDocument()->addOnloadScript($script);
    }

    public function subscribe()
    {
        if ( isset($_POST['actions']) )
        {
            $hidden = array();

            foreach ( $_POST['actions'] as $id => $isDisplayed )
            {
                if ( $isDisplayed == 0 )
                {
                    array_push($hidden, $id);
                }
            }

            $this->membershipService->setSubscribeHiddenActions($hidden);
            $this->redirect();
        }

        $lang = OW::getLanguage();
        $this->addComponent('menu', $this->getMenu('subscribe'));

        $service = BOL_AuthorizationService::getInstance();

        $actions = $service->getActionList();
        $groups = $service->getGroupList();

        $groupActionList = array();

        foreach ( $groups as $group )
        {
            /* @var $group BOL_AuthorizationGroup */
            $groupActionList[$group->id]['name'] = $group->name;
            $groupActionList[$group->id]['actions'] = array();
        }

        foreach ( $actions as $action )
        {
            /* @var $action BOL_AuthorizationAction */
            $groupActionList[$action->groupId]['actions'][] = $action;
        }

        $pm = OW::getPluginManager();
        foreach ( $groupActionList as $key => $value )
        {
            if ( count($value['actions']) === 0 || !$pm->isPluginActive($value['name']) )
            {
                unset($groupActionList[$key]);
            }
        }

        $this->assign('groupActionList', $groupActionList);
        
        // collecting labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $this->assign('labels', $dataLabels);

        $this->setPageHeading($lang->text('membership', 'admin_page_heading_memberships'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $this->assign('hidden', $this->membershipService->getSubscribeHiddenActions());
    }
    
    public function users( array $params )
    {
        $roleId = !empty($params['roleId']) ? $params['roleId'] : null;
        
        $lang = OW::getLanguage();
        $userService = BOL_UserService::getInstance();
        
        $menu = $this->getMenu('users');

        $this->addComponent('menu', $menu);
        
        $this->setPageHeading($lang->text('membership', 'admin_page_heading_users_by_membership'));
        $this->setPageHeadingIconClass('ow_ic_user');
        
        $this->assign('route', OW::getRouter()->urlForRoute('membership_admin_browse_users_st'));
        
        $memberships = $this->membershipService->getTypeList();
        
        $types = array();
        $firstRoleId = null;
        foreach ( $memberships as $id => $type )
        {
            if ( $id == 0 )
            {
                $firstRoleId = $type->roleId;
            }
            $types[$id]['dto'] = $type;
            $types[$id]['title'] = $this->membershipService->getMembershipTitle($type->roleId);
        }

        $this->assign('types', $types);
        
        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $onPage = 20;
        
        $roleId = $roleId ? $roleId : ($firstRoleId ? $firstRoleId : null);
        
        if ( !$roleId )
        {
            return;
        }
        
        $this->assign('roleId', $roleId);
        $typeIdList = $this->membershipService->getMembershipTypeIdListByRoleId($roleId);
        $list = $this->membershipService->getUserListByMembershipTypeIdList($typeIdList, $page, $onPage);

        if ( !$list )
        {
            return;
        }
        
        $this->assign('list', $list);
        
        $total = $this->membershipService->countUsersByMembershipTypeIdList($typeIdList);
        
        // Paging
        $pages = (int) ceil($total / $onPage);
        $paging = new BASE_CMP_Paging($page, $pages, $onPage);
        $this->addComponent('paging', $paging);
        
        $userIdList = array();

        foreach ( $list as $user )
        {
            if ( !in_array($user['userId'], $userIdList) )
            {
                array_push($userIdList, $user['userId']);
            }
        }

        $this->assign('avatars', BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, true, false, false));
        
        $userNameList = $userService->getUserNamesForList($userIdList);
        $this->assign('userNameList', $userNameList);

        $displayNameList = $userService->getDisplayNamesForList($userIdList);
        $this->assign('displayNames', $displayNameList);

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, array('sex', 'birthdate', 'email'));
        $this->assign('questionList', $questionList);

        $onlineStatus = $userService->findOnlineStatusForUserList($userIdList);
        $this->assign('onlineStatus', $onlineStatus);
    }

    public function settings()
    {
        $lang = OW::getLanguage();
        $config = OW::getConfig();

        $form = new MEMBERSHIP_CLASS_SettingsForm();
        $this->addForm($form);

        $form->getElement('period')->setValue($config->getValue('membership', 'notify_period'));

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();
            $config->saveConfig('membership', 'notify_period', (int) $values['period']);

            OW::getFeedback()->info($lang->text('membership', 'settings_updated'));
            $this->redirect();
        }

        $menu = $this->getMenu('settings');
        $this->addComponent('menu', $menu);

        $this->setPageHeading($lang->text('membership', 'admin_page_heading_settings'));
        $this->setPageHeadingIconClass('ow_ic_user');
    }
}

