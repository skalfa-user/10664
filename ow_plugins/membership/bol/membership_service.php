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
 * Membership Service Class.
 *
 * @author Egor Bulgakov, Sergey Pryadkin <egor.bulgakov@gmail.com, GiperProger@gmail.com>
 * @package ow.ow_plugins.membership.bol
 * @since 1.8.4
 */
class MEMBERSHIP_BOL_MembershipService
{
    const EVENT_ON_BEFORE_FIND_EXPIRED_MEMBERSHIPS = 'membership.on_before_find_expired_memberships';
    const EXPIRE_USER_MEMBERSHIP_LIST = 'membership.expire_user_membership_list';
    const EXPIRE_USER_MEMBERSHIP = 'membership.expire_user_membership';
    const GET_PRODUCT_ID = 'membership.get_product_id';

    const PERIOD_DAYS = 'days';
    const PERIOD_MONTHS = 'months';
    
    /**
     * @var MEMBERSHIP_BOL_MembershipTypeDao
     */
    protected $membershipTypeDao;
    /**
     * @var MEMBERSHIP_BOL_MembershipPlanDao
     */
    protected $membershipPlanDao;
    /**
     * @var MEMBERSHIP_BOL_MembershipUserDao
     */
    protected $membershipUserDao;
    /**
     * @var MEMBERSHIP_BOL_MembershipUserTrialDao
     */
    protected $membershipUserTrialDao;
    /**
     * Class instance
     *
     * @var MEMBERSHIP_BOL_MembershipService
     */
    private static $classInstance;
    
    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->membershipTypeDao = MEMBERSHIP_BOL_MembershipTypeDao::getInstance();
        $this->membershipPlanDao = MEMBERSHIP_BOL_MembershipPlanDao::getInstance();
        $this->membershipUserDao = MEMBERSHIP_BOL_MembershipUserDao::getInstance();
        $this->membershipUserTrialDao = MEMBERSHIP_BOL_MembershipUserTrialDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return MEMBERSHIP_BOL_MembershipService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    /* ------- Memebrship type methods ------- */

    /**
     * Get list of all membership types
     *
     * @param int $accTypeId
     * @return array of MEMBERSHIP_BOL_MembershipType
     */
    public function getTypeList( $accTypeId = null )
    {
        return $this->membershipTypeDao->getAllTypeList($accTypeId);
    }

    /**
     * Get list of mambership types & their plans
     *
     * @param int $accTypeId
     * @return array mixed
     */
    public function getTypeListWithPlans( $accTypeId = null )
    {
        $types = $this->membershipTypeDao->getTypeList($accTypeId);

        $typesWithPlans = array();

        foreach ( $types as $key => $type )
        {
            $typesWithPlans[$key] = $type;
            $plans = $this->membershipPlanDao->findPlanListByTypeId($type['id']);
            if ( $plans )
            {
                foreach ( $plans as $plan )
                {
                    $typesWithPlans[$key]['plans'][] = array(
                        'dto' => $plan,
                        'plan_format' => $this->getFormattedPlan($plan->price, $plan->period, $plan->recurring, null, $plan->periodUnits)
                    );
                }
            }
        }

        return $typesWithPlans;
    }

    public function getPlanProductId( $planId )
    {
        if ( !$planId )
        {
            return null;
        }

        $event = new OW_Event(self::GET_PRODUCT_ID, array('id' => $planId));
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }

    /**
     * Finds membership type by type Id
     * 
     * @param int $typeId
     * @return MEMBERSHIP_BOL_MembershipType
     */
    public function findTypeById( $typeId )
    {
        return $this->membershipTypeDao->findById($typeId);
    }

    /**
     * Finds membership type by plan Id
     * 
     * @param int $planId
     * @return MEMBERSHIP_BOL_MembershipType
     */
    public function findTypeByPlanId( $planId )
    {
        if ( !$planId )
        {
            return false;
        }

        $plan = $this->findPlanById($planId);

        if ( $plan )
        {
            return $this->findTypeById($plan->typeId);
        }

        return false;
    }

    /**
     * Adds membership type & plan if passed
     * 
     * @param MEMBERSHIP_BOL_MembershipType $type
     * @param MEMBERSHIP_BOL_MembershipPlan $plan
     * @return boolean
     */
    public function addType( MEMBERSHIP_BOL_MembershipType $type, MEMBERSHIP_BOL_MembershipPlan $plan = null )
    {
        $this->membershipTypeDao->save($type);

        if ( $plan !== null )
        {
            $plan->typeId = $type->id;

            $this->addPlan($plan);
        }

        return true;
    }

    /**
     * Updates membership type
     * 
     * @param MEMBERSHIP_BOL_MembershipType $type
     * @return int
     */
    public function updateType( MEMBERSHIP_BOL_MembershipType $type )
    {
        $this->membershipTypeDao->save($type);

        return $type->id;
    }

    /**
     * Deletes membership type
     * 
     * @param int $typeId
     */
    public function deleteType( $typeId )
    {
        $this->membershipTypeDao->deleteById($typeId);
    }

    /**
     * Deletes membership type & its plans
     * 
     * @param int $typeId
     */
    public function deleteTypeWithPlans( $typeId )
    {
        $this->deleteUserTrialsByTypeId($typeId);

        $this->membershipTypeDao->deleteById($typeId);

        $this->membershipPlanDao->deletePlansByTypeId($typeId);
    }
    /* ------- Memebrship plan methods ------- */

    /**
     * Finds plan by Id
     * 
     * @param int $planId
     * @return MEMBERSHIP_BOL_MembershipPlan
     */
    public function findPlanById( $planId )
    {
        return $this->membershipPlanDao->findById((int) $planId);
    }

    /**
     * Get the list of membership plans
     *
     * @param array $exclude
     * @return array
     */
    public function getTypePlanList( array $exclude = array() )
    {
        $plans = $this->membershipPlanDao->findPlanList($exclude);

        $typePlans = array();
        foreach ( $plans as $plan )
        {
            $plan->price = floatval($plan->price);
            $pl = array(
                'dto' => $plan,
                'plan_format' => $this->getFormattedPlan($plan->price, $plan->period, $plan->recurring, null, $plan->periodUnits),
                'productId' => $this->getPlanProductId($plan->id)
            );

            $typePlans[$plan->typeId][] = $pl;
        }

        return $typePlans;
    }

    /**
     * Get list of plans by membership type Id
     * 
     * @param int $typeId
     * @return array of MEMBERSHIP_BOL_MembershipPlan
     */
    public function getPlanList( $typeId )
    {
        $plans = $this->membershipPlanDao->findPlanListByTypeId($typeId);

        $typePlans = array();
        foreach ( $plans as $plan )
        {
            $plan->price = floatval($plan->price);
            $typePlans[] = array(
                'dto' => $plan,
                'plan_format' => $this->getFormattedPlan($plan->price, $plan->period, $plan->recurring, null, $plan->periodUnits),
                'productId' => $this->getPlanProductId($plan->id)
            );
        }

        return $typePlans;
    }

    /**
     * Adds membership plan
     * 
     * @param MEMBERSHIP_BOL_MembershipPlan $plan
     * @return int
     */
    public function addPlan( MEMBERSHIP_BOL_MembershipPlan $plan )
    {
        $this->membershipPlanDao->save($plan);

        return $plan->id;
    }

    /**
     * Updates plan
     * 
     * @param MEMBERSHIP_BOL_MembershipPlan $plan
     * @return int
     */
    public function updatePlan( MEMBERSHIP_BOL_MembershipPlan $plan )
    {
        $this->membershipPlanDao->save($plan);

        return $plan->id;
    }

    /**
     * Deletes plan
     * 
     * @param int $planId
     */
    public function deletePlan( $planId )
    {
        $this->membershipPlanDao->deleteById($planId);
    }
    
    public function deletePlansByTypeId( $typeId )
    {
        $this->membershipPlanDao->deletePlansByTypeId($typeId);
    }

    /**
     * Get plan formatted string
     * 
     * @param float $price
     * @param int $period
     * @param boolean $recurring
     * @param string $currency
     * @return string
     */
    public function getFormattedPlan( $price, $period, $recurring = false, $currency = null, $periodUnints = self::PERIOD_DAYS )
    {
        if ( $price == 0 )
        {
            $langKey = 'plan_struct_trial';
            $params = array('period' => $period);
        }
        else
        {
            $currency = isset($currency) ? $currency : BOL_BillingService::getInstance()->getActiveCurrency();
            $params = array('currency' => $currency, 'price' => floatval($price), 'period' => $period);
            $langKey = $recurring ? 'plan_struct_recurring' : 'plan_struct';
        }
        
        $periodUnints = in_array($periodUnints, $this->getPeriodUnitsList()) ? $periodUnints : self::PERIOD_DAYS;
        
        $params['periodUnits'] = OW::getLanguage()->text('membership', $periodUnints);

        $lang = OW::getLanguage();

        return $lang->text('membership', $langKey, $params);
    }

    /* ------- Misc methods ------- */

    /**
     * Get membership title by authorization role Id 
     * 
     * @param int $roleId
     * @return string
     */
    public function getMembershipTitle( $roleId )
    {
        if ( !$roleId )
        {
            return null;
        }
        $role = BOL_AuthorizationService::getInstance()->getRoleById($roleId);

        if ( $role )
        {
            return OW::getLanguage()->text('base', 'authorization_role_' . $role->name);
        }

        return null;
    }
    
    /**
     * Set user membership
     * 
     * @param MEMBERSHIP_BOL_MembershipUser $userMembership
     */
    public function setUserMembership( MEMBERSHIP_BOL_MembershipUser $userMembership )
    {
        $userId = $userMembership->userId;
        $newType = $this->findTypeById($userMembership->typeId);

        /* @var $currentMembership MEMBERSHIP_BOL_MembershipUser */
        $currentMembership = $this->getUserMembership($userId);

        $authService = BOL_AuthorizationService::getInstance();

        if ( $currentMembership )
        {
            $currentType = $this->findTypeById($currentMembership->typeId);
            if ( $currentType )
            {
                $authService->deleteUserRole($userId, $currentType->roleId);
            }
            $this->deleleUserMembership($currentMembership);
        }

        $authService->saveUserRole($userId, $newType->roleId);
        $this->membershipUserDao->save($userMembership);
    }

    public function setDefaultMembership( $userId )
    {
        /* @var $currentMembership MEMBERSHIP_BOL_MembershipUser */
        $currentMembership = $this->getUserMembership($userId);

        $authService = BOL_AuthorizationService::getInstance();

        if ( $currentMembership )
        {
            $type = $this->findTypeById($currentMembership->typeId);
            if ( $type )
            {
                $authService->deleteUserRole($userId, $type->roleId);
            }
            $authService->assignDefaultRoleToUser($userId);
            $this->membershipUserDao->deleteById($currentMembership->id);
        }

        return true;
    }

    /**
     * Deletes users' expired memberships
     * 
     * @return boolean
     */
    public function expireUsersMemberships()
    {
        $eventParams = $this->getQueryFilter(self::EVENT_ON_BEFORE_FIND_EXPIRED_MEMBERSHIPS);
        $msList = $this->membershipUserDao->findExpiredMemberships($eventParams);

        $event = new OW_Event(self::EXPIRE_USER_MEMBERSHIP_LIST, [], $msList);
        $msList = OW::getEventManager()->trigger($event);

        if ( !$msList->getData() )
        {
            return true;
        }
        
        $authService = BOL_AuthorizationService::getInstance();
        
        foreach ( $msList->getData() as $ms )
        {
            $type = $this->findTypeById($ms->typeId);
            $userId = $ms->userId;

            if ( $type )
            {
                $authService->deleteUserRole($userId, $type->roleId);
            }

            $authService->assignDefaultRoleToUser($userId);
            $this->membershipUserDao->deleteById($ms->id);

            $event = new OW_Event(self::EXPIRE_USER_MEMBERSHIP, array('id' => $ms->id, 'userId' => $userId, 'typeId' => $ms->typeId));
            OW::getEventManager()->trigger($event);

            if ( $type )
            {
                $label = $this->getMembershipTitle($type->roleId);
                $this->sendMembershipExpiredNotification($userId, $label);
            }
        }
        
        return true;
    }

    public function getRemainingPeriod( $expTime )
    {
        if ( $expTime < time() )
        {
            return 0;
        }

        return ceil(( $expTime - time() ) / 3600 / 24);
    }

    /**
     * Returns user's membership
     * 
     * @param int $userId
     * @return MEMBERSHIP_BOL_MembershipUser
     */
    public function getUserMembership( $userId )
    {
        return $this->membershipUserDao->findByUserId($userId);
    }
    
    public function getUserListByMembershipType( $typeId, $page, $onPage )
    {
        return $this->membershipUserDao->findByTypeId($typeId, $page, $onPage);
    }

    public function getUserObjectListByMembershipType( $typeId, $page, $onPage )
    {
        return $this->membershipUserDao->findObjectsByTypeId($typeId, $page, $onPage);
    }

    public function getUserListByMembershipTypeIdList( $typeIdList, $page, $onPage )
    {
        return $this->membershipUserDao->findByTypeIdList($typeIdList, $page, $onPage);
    }
    
    public function countUsersByMembershipType( $typeId )
    {
        return $this->membershipUserDao->countByTypeId($typeId);
    }

    public function countUsersByMembershipTypeIdList( $typeIdList )
    {
        return $this->membershipUserDao->countByTypeIdList($typeIdList);
    }

    public function getMembershipTypeIdListByRoleId( $roleId )
    {
        return $this->membershipTypeDao->getTypeIdListByRoleId($roleId);
    }

    public function deleteMembershipTypeByRoleId( $roleId )
    {
        $types = $this->membershipTypeDao->getTypeIdListByRoleId($roleId);
        
        if ( $types )
        {
            foreach ( $types as $typeId )
            {
                $this->deleteUserTrialsByTypeId($typeId);
                $this->membershipPlanDao->deletePlansByTypeId($typeId);
            }
        }
            
        $this->membershipTypeDao->deleteByRoleId($roleId);
        
        return true;        
    }
    
    public function deleteUserMembershipsByRoleId( $roleId )
    {
        $types = $this->membershipTypeDao->getTypeIdListByRoleId($roleId);
        
        if ( $types )
        {
            foreach ( $types as $typeId )
            {
                $this->membershipUserDao->deleteByTypeId($typeId);
            }
        }
        
        return true;        
    }

    /**
     * Deletes user's membership
     * 
     * @param MEMBERSHIP_BOL_MembershipUser $userMembership
     * @return boolean
     */
    public function deleleUserMembership( MEMBERSHIP_BOL_MembershipUser $userMembership )
    {
        $this->membershipUserDao->delete($userMembership);

        return true;
    }
    
    public function deleleUserMembershipByUserId( $userId )
    {
        $membership = $this->getUserMembership($userId);
        
        if ( $membership )
        {   
            $this->membershipUserDao->delete($membership);
        }

        return true;
    }

    /**
     * Returns array of actions not shown on subscribe page
     * 
     * @return array
     */
    public function getSubscribeHiddenActions()
    {
        $json = OW::getConfig()->getValue('membership', 'subscribe_hidden_actions');

        return mb_strlen($json) ? json_decode($json) : array();
    }

    /**
     * Sets array of actions not shown on subscribe page
     * 
     * @param array $actions
     * @return boolean
     */
    public function setSubscribeHiddenActions( array $actions = array())
    {
        OW::getConfig()->saveConfig('membership', 'subscribe_hidden_actions', json_encode($actions));

        return true;
    }

    /**
     * Returns the list of group actions for subscribe form 
     * 
     * @return array
     */
    public function getSubscribePageGroupActionList()
    {
        $service = BOL_AuthorizationService::getInstance();
        $actions = $service->getActionList();
        $groups = $service->getGroupList();
        $hiddenActions = $this->getSubscribeHiddenActions();

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
            if ( !in_array($action->id, $hiddenActions) )
            {
                $groupActionList[$action->groupId]['actions'][] = $action;
            }
        }

        $pm = OW::getPluginManager();
        foreach ( $groupActionList as $key => $value )
        {
            if ( count($value['actions']) === 0 || !$pm->isPluginActive($value['name']) )
            {
                unset($groupActionList[$key]);
            }
        }

        return $groupActionList;
    }

    public function getSubsequentRoleIdList()
    {
        $accTypeName = OW::getUser()->getUserObject()->getAccountType();
        $accType = BOL_QuestionService::getInstance()->findAccountTypeByName($accTypeName);
        $mTypes = $this->getTypeList($accType->id);

        $list = array();
        foreach ($mTypes as $type) 
        {
            $list[] = $type->roleId;
        }

        return $list;
    }

    public function getPromoActionList( $userId, $limit = 3 )
    {
        if ( !$userId )
        {
            return null;
        }

        $authService = BOL_AuthorizationService::getInstance();
        $userMembership = $this->getUserMembership($userId);
        $roleId = null;
        if ( $userMembership )
        {
            $roleId = $this->findTypeById($userMembership->typeId)->roleId;
        }
        else
        {
            $userRoleList = $authService->findUserRoleList($userId);
            if ( $userRoleList )
            {
                $lastRole = array_pop($userRoleList);
                $roleId = $lastRole->id;
            }
        }

        $roleIdList = $this->getSubsequentRoleIdList();
        if ( !$roleIdList )
        {
            return null;
        }

        $permissions = BOL_AuthorizationService::getInstance()->getPermissionList();
        $currentRoleActions = array();
        foreach ( $permissions as $permission )
        {
            if ( $permission->roleId == $roleId )
            {
                $currentRoleActions[] = $permission->actionId;
            }
        }

        $hiddenActions = $this->getSubscribeHiddenActions();

        $allowedActions = array();
        $count = 0;
        foreach ( $permissions as $permission )
        {
            if ( in_array($permission->roleId, $roleIdList) && !in_array($permission->actionId, $hiddenActions)
                && !in_array($permission->actionId, $allowedActions) && !in_array($permission->actionId, $currentRoleActions) )
            {
                if ( $count > $limit )
                {
                    break;
                }
                $allowedActions[] = $permission->actionId;
                $count++;
            }
        }

        // collecting labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $groupActionList = $this->getSubscribePageGroupActionList();

        $labels = array();
        foreach ( $groupActionList as $groupAction )
        {
            foreach ( $groupAction['actions'] as $action )
            {
                if  ( in_array( $action->id, $allowedActions) )
                {
                    $labels[] = isset($dataLabels[$groupAction['name']]) ? $dataLabels[$groupAction['name']]['actions'][$action->name] : null;
                }
            }
        }

        return $labels;
    }

    /**
     * Returns list of roles which can be assigned to memberships 
     * 
     * @param arary $assignedMemberships
     * @return array
     */
    public function getRolesAvailableForMembership(array $assignedMemberships = array())
    {
        $authService = BOL_AuthorizationService::getInstance();

        $roles = $authService->findNonGuestRoleList();
        $default = $authService->getDefaultRole();

        foreach ( $roles as $key => $role )
        {
            if ( $role->id == $default->id 
                    || ($assignedMemberships && in_array($role->id, $assignedMemberships)) )
            {
                unset($roles[$key]);
            }
        }

        return $roles;
    }

    /**
     * @param $actionId
     * @return bool
     */
    public function actionCanBePurchased( $actionId )
    {
        if ( !$actionId )
        {
            return false;
        }

        $memberships = $this->getTypeList();

        if ( !$memberships )
        {
            return false;
        }

        foreach ( $memberships as $ms )
        {
            /** @var MEMBERSHIP_BOL_MembershipType $ms */
            $perm = BOL_AuthorizationPermissionDao::getInstance()->findByRoleIdAndActionId($ms->roleId, $actionId);

            if ( $perm )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $userId
     * @param $planId
     * @param int $period
     * @return bool
     */
    public function addTrialPlanUsage( $userId, $planId, $period = 30, $periodUnits = self::PERIOD_DAYS )
    {
        if ( !$userId || !$planId )
        {
            return false;
        }
        
        $userTrial = new MEMBERSHIP_BOL_MembershipUserTrial();
        $userTrial->userId = $userId;
        $userTrial->planId = $planId;
        $userTrial->startStamp = time();
        $userTrial->expirationStamp = $userTrial->startStamp + $period * $this->getPeriodUnitFactor($periodUnits);
        
        $this->membershipUserTrialDao->save($userTrial);

        return true;
    }

    public function getUserTrialPlansUsage( $userId )
    {
        $list = $this->membershipUserTrialDao->findListByUserId($userId);

        $result = array();
        if ( !$list )
        {
            return $result;
        }

        foreach ( $list as $item )
        {
            $result[] = $item->planId;
        }

        return $result;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function isTrialUsedByUser( $userId )
    {
        if ( !$userId )
        {
            return false;
        }

        $trial = $this->membershipUserTrialDao->findByUserId($userId);

        return (bool) $trial;
    }

    public function deleteUserTrialsByTypeId( $typeId )
    {
        if ( !$typeId )
        {
            return true;
        }

        $plans = $this->membershipPlanDao->findPlanListByTypeId($typeId);

        if ( !$plans )
        {
            return true;
        }

        foreach ( $plans as $plan )
        {
            $this->membershipUserTrialDao->deleteByPlanId($plan->id);
        }

        return true;
    }

    public function deleteUserTrialsByUserId( $userId )
    {
        $this->membershipUserTrialDao->deleteByUserId($userId);
    }


    /** === Notifications === */

    /**
     * @param $userId
     * @param $membershipLabel
     * @param $expirationTime
     * @return bool
     */
    public function sendMembershipPurchasedNotification( $userId, $membershipLabel, $expirationTime )
    {
        if ( !$userId )
        {
            return false;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return false;
        }

        $lang = OW::getLanguage();

        $email = $user->email;

        $subject = $lang->text('membership', 'plan_purchase_notification_subject', array('membership' => $membershipLabel));

        $assigns = array(
            'membership' => $membershipLabel,
            'name' => BOL_UserService::getInstance()->getDisplayName($userId),
            'expirationDate' => UTIL_DateTime::formatSimpleDate($expirationTime, true)
        );
        $text = $lang->text('membership', 'plan_purchase_notification_text', $assigns);
        $html = $lang->text('membership', 'plan_purchase_notification_html', $assigns);

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($text)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }

    public function sendExpirationNotifications( $limit )
    {
        $expireToday = $this->membershipUserDao->getExpiringTodayMemberships($limit);

        if ( $expireToday )
        {
            foreach ( $expireToday as $membership )
            {
                $type = $this->findTypeById($membership->typeId);
                if ( !$type )
                {
                    $this->deleleUserMembership($membership);

                    continue;
                }

                $label = $this->getMembershipTitle($type->roleId);
                $this->sendMembershipExpiresTodayNotification($membership->userId, $label);
            }
        }

        $period = (int) OW::getConfig()->getValue('membership', 'notify_period');
        $expireSoon = $this->membershipUserDao->getExpiringSoonMemberships($period, $limit);

        if ( $expireSoon )
        {
            foreach ( $expireSoon as $membership )
            {
                $type = $this->findTypeById($membership->typeId);
                if ( !$type )
                {
                    $this->deleleUserMembership($membership);

                    continue;
                }

                $label = $this->getMembershipTitle($type->roleId);
                $this->sendMembershipExpiresNotification($membership->userId, $label);
            }
        }
    }

    /**
     * @param $userId
     * @param $membershipLabel
     * @return bool
     */
    public function sendMembershipExpiresNotification( $userId, $membershipLabel )
    {
        if ( !$userId )
        {
            return false;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return false;
        }

        $userMembership = $this->getUserMembership($userId);

        if ( !$userMembership )
        {
            return false;
        }

        $lang = OW::getLanguage();
        $period = (int) OW::getConfig()->getValue('membership', 'notify_period');

        $email = $user->email;

        $subject = $lang->text('membership', 'plan_expires_notification_subject', array('days' => $period));

        $assigns = array(
            'membership' => $membershipLabel,
            'name' => BOL_UserService::getInstance()->getDisplayName($userId),
            'days' => $period
        );
        $text = $lang->text('membership', 'plan_expires_notification_text', $assigns);
        $html = $lang->text('membership', 'plan_expires_notification_html', $assigns);

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($text)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);

            $userMembership->expirationNotified = 1;
            $this->membershipUserDao->save($userMembership);
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }

    /**
     * @param $userId
     * @param $membershipLabel
     * @return bool
     */
    public function sendMembershipExpiresTodayNotification( $userId, $membershipLabel )
    {
        if ( !$userId )
        {
            return false;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return false;
        }

        $userMembership = $this->getUserMembership($userId);

        if ( !$userMembership )
        {
            return false;
        }

        $lang = OW::getLanguage();

        $email = $user->email;

        $subject = $lang->text('membership', 'plan_expires_today_notification_subject');

        $assigns = array(
            'membership' => $membershipLabel,
            'name' => BOL_UserService::getInstance()->getDisplayName($userId),
        );
        $text = $lang->text('membership', 'plan_expires_today_notification_text', $assigns);
        $html = $lang->text('membership', 'plan_expires_today_notification_html', $assigns);

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($text)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);

            $userMembership->expirationNotified = 2;
            $this->membershipUserDao->save($userMembership);
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }

    /**
     * @param $userId
     * @param $membershipLabel
     * @return bool
     */
    public function sendMembershipExpiredNotification( $userId, $membershipLabel )
    {
        if ( !$userId )
        {
            return false;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return false;
        }

        $lang = OW::getLanguage();

        $email = $user->email;

        $subject = $lang->text('membership', 'plan_expired_notification_subject');

        $assigns = array(
            'membership' => $membershipLabel,
            'name' => BOL_UserService::getInstance()->getDisplayName($userId)
        );
        $text = $lang->text('membership', 'plan_expired_notification_text', $assigns);
        $html = $lang->text('membership', 'plan_expired_notification_html', $assigns);

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($text)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }

    /**
     * @param $userId
     * @param $membershipLabel
     * @return bool
     */
    public function sendMembershipRenewedNotification( $userId, $membershipLabel )
    {
        if ( !$userId )
        {
            return false;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return false;
        }

        $lang = OW::getLanguage();

        $email = $user->email;

        $subject = $lang->text('membership', 'plan_renewed_notification_subject');

        $assigns = array(
            'membership' => $membershipLabel,
            'name' => BOL_UserService::getInstance()->getDisplayName($userId)
        );
        $text = $lang->text('membership', 'plan_renewed_notification_text', $assigns);
        $html = $lang->text('membership', 'plan_renewed_notification_html', $assigns);

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($text)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }

    public static function formatDate( array $params, $smarty )
    {
        $timeStamp = (int) $params['timestamp'];
        $onlyDate = null;

        if ( !$timeStamp )
        {
            return '_INVALID_TS_';
        }

        if ( !(bool) OW::getConfig()->getValue('base', 'site_use_relative_time') )
        {
            return UTIL_DateTime::formatSimpleDate($timeStamp, $onlyDate);
        }

        $language = OW::getLanguage();

        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');

        $currentTs = time();

        $isCurrentDay = date('j', $timeStamp) === date('j', $currentTs);
        $isCurrentMonth = date('n', $timeStamp) === date('n', $currentTs);
        $isCurrentYear = date('Y', $timeStamp) === date('Y', $currentTs);
        $isTomorrow = ( date('j', $timeStamp) - date('j', $currentTs) ) === 1;
        $isYesterday = ( date('j', $currentTs) - date('j', $timeStamp) ) === 1;

        if ( $isCurrentMonth && $isCurrentYear )
        {
            if ( $isCurrentDay )
            {
                if ( $onlyDate )
                {
                    return $language->text('base', 'date_time_today');
                }

                $seconds = $currentTs - $timeStamp;
                $past = $seconds >= 0;
                $seconds = abs($seconds);

                switch ( true )
                {
                    case $seconds < 60:
                        return $language->text('base', 'date_time_within_one_minute');

                    case $seconds < 120:
                        return $past ? $language->text('base', 'date_time_one_minute_ago') : $language->text('membership', 'date_time_in_one_minute');

                    case $seconds < 3600:
                        $data = array('minutes' => floor($seconds / 60));
                        return $past ? $language->text('base', 'date_time_minutes_ago', $data) : $language->text('membership', 'date_time_in_minutes', $data);

                    case $seconds < 7200:
                        return $past ? $language->text('base', 'date_time_one_hour_ago') : $language->text('membership', 'date_time_in_one_hour');

                    default:
                        $data = array('hours' => floor($seconds / 3600));
                        return $past ? $language->text('base', 'date_time_hours_ago', $data) : $language->text('membership', 'date_time_in_hours', $data);
                }
            }
            else if ( $isYesterday )
            {
                if ( $onlyDate )
                {
                    return $language->text('base', 'date_time_yesterday');
                }

                return $language->text('base', 'date_time_yesterday') . ', ' . ( $militaryTime ? strftime("%H:%M", $timeStamp) : strftime("%I:%M%p", $timeStamp) );
            }
            else if ( $isTomorrow )
            {
                if ( $onlyDate )
                {
                    return $language->text('membership', 'date_time_tomorrow');
                }

                return $language->text('membership', 'date_time_tomorrow') . ', ' . ( $militaryTime ? strftime("%H:%M", $timeStamp) : strftime("%I:%M%p", $timeStamp) );
            }
        }

        if ( $onlyDate === null )
        {
            $onlyDate = true;
        }

        return UTIL_DateTime::formatSimpleDate($timeStamp, $onlyDate);
    }
    
    /**
     * Get list of all period units
     *
     * @return array
     */
    public function getPeriodUnitsList()
    {
        return array(self::PERIOD_DAYS, self::PERIOD_MONTHS);
    }
    
     /**
     * Get factor of selected period unit
     * for example: if $unit = 'days' result 60 * 60 * 24 
     *
     * @return int
     */
    public function getPeriodUnitFactor($unit) {
        $result = 60 * 60 * 24;

        switch ($unit) {
            case self::PERIOD_DAYS:
                $result = 60 * 60 * 24;
                break;
            case self::PERIOD_MONTHS:
                $result = 60 * 60 * 24 * 30;
                break;
        }

        return $result;
    }

    /**
     * Get query filter
     * 
     * @param $eventName
     * @param array $options
     * @param array $data
     * @return array
     */
    public function getQueryFilter( $eventName, array $options = array(), array $data = array() )
    {
        $event = new MEMBERSHIP_CLASS_QueryBuilderEvent($eventName, $options);

        OW::getEventManager()->trigger($event);

        return array(
            'select' => $event->getSelect(),
            'join' => $event->getJoin(),
            'where' => $event->getWhere(),
            'group_by' =>  $event->getGroupBy(),
            'order' => $event->getOrder()
        );
    }
    
    public function deleteMembershipUserByUserId( $userId )
    {
        $userId = intval($userId);
        
        $this->membershipUserDao->deleteByUserId($userId);
    }
    
    public function findRecurringExpiredSale()
    {
        return $this->membershipUserDao->findRecurringExpiredSale();
    }

    public function updateMembershipUser( MEMBERSHIP_BOL_MembershipUser $membershipUser )
    {
        $this->membershipUserDao->save($membershipUser);
    }

    /**
     * @param $id
     * @return MEMBERSHIP_BOL_MembershipUser
     */
    public function getMembershipUserById( $id )
    {
        $id = intval($id);

        return $this->membershipUserDao->findById($id);
    }
}