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

use Symfony\Component\HttpKernel\Exception\Exception;


class SKMOBILEAPP_BOL_PaymentsService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS = 'ios';
    const COUNTER_TO_REMOVE = 12;
    const NEXT_CHECK_TIME = 7200; // 2 hours
    const APP_ONLY_MEMBERSHIP_ACTIONS = 'app_only';
    const ALL_MEMBERSHIP_ACTIONS = 'all';

    const MOBILE_BILLING_PAYPAL = 'billingpaypal';
    const MOBILE_BILLING_STRIPE = 'billingstripe';

    static $allowedMobileBillingGateways = [
        self::MOBILE_BILLING_PAYPAL,
        self::MOBILE_BILLING_STRIPE
    ];

    static $redirectableMobileBillingGateways = [
        self::MOBILE_BILLING_PAYPAL
    ];

    /**
     * @var SKMOBILEAPP_BOL_InappsPurchaseDao
     */
    protected $purchaseDao;

    /**
     * @var SKMOBILEAPP_BOL_ExpirationPurchaseDao
     */
    protected $expirationPurchaseDao;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->purchaseDao = SKMOBILEAPP_BOL_InappsPurchaseDao::getInstance();
        $this->expirationPurchaseDao = SKMOBILEAPP_BOL_ExpirationPurchaseDao::getInstance();
    }

    /**
     * Get mobile stripe gateway data
     * 
     * @return array
     */
    public function getMobileStripeGatewayData() 
    {
        $isBillingAddressRequired = BOL_BillingService::getInstance()->getGatewayConfigValue('billingstripe', 'requireData');

        // app questions format
        $basicFields = [[
            'section' => 'billing_card_details',
            'items' => [[
                'type' => BOL_QuestionService::QUESTION_PRESENTATION_TEXT,
                'key' => 'card_name',
                'label' => 'billing_card_name',
                'placeholder' => 'billing_card_name_placeholder',
                'params' => [
                    'stacked' => true
                ],
                'validators' => [
                    ['name' => 'require']
                ]
            ]]
        ]];

        if ( $isBillingAddressRequired ) 
        {
            $countries = [];

            // process country list
            foreach( BILLINGSTRIPE_CLASS_StripeAdapter::getCountryList() as $code => $country ) 
            {
                $countries[] = [
                    'value' => $code,
                    'title' => $country
                ];
            }

            $billingFields = [
                'section' => 'billing_information',
                'items' => [[
                    'type' => BOL_QuestionService::QUESTION_PRESENTATION_SELECT,
                    'key' => 'country',
                    'label' => 'billing_country',
                    'placeholder' => 'billing_country_placeholder',
                    'values' => $countries,
                    'validators' => [
                        [ 'name' => 'require' ]
                    ],
                    'params' => [
                        'hideEmptyValue' => true
                    ]
                ], [
                    'type' => BOL_QuestionService::QUESTION_PRESENTATION_TEXT,
                    'key' => 'state',
                    'label' => 'billing_state',
                    'placeholder' => 'billing_state_placeholder',
                    'params' => [
                        'stacked' => true
                    ],
                    'validators' => [
                        [ 'name' => 'require' ]
                    ]
                ], [
                    'type' => BOL_QuestionService::QUESTION_PRESENTATION_TEXT,
                    'key' => 'address_line',
                    'label' => 'billing_address',
                    'placeholder' => 'billing_address_placeholder',
                    'params' => [
                        'stacked' => true
                    ],
                    'validators' => [
                        [ 'name' => 'require' ]
                    ]
                ], [
                    'type' => BOL_QuestionService::QUESTION_PRESENTATION_TEXT,
                    'key' => 'zip_code',
                    'label' => 'billing_zip',
                    'placeholder' => 'billing_zip_placeholder',
                    'params' => [
                        'stacked' => true
                    ],
                    'validators' => [
                        [ 'name' => 'require' ]
                    ]
                ]]
            ];

            $basicFields[] = $billingFields;
        }

        return $basicFields;
    }

    /**
     * get Stripe publish key
     *
     * @return string
     */
    public function getStripePublishKey()
    {
        return BILLINGSTRIPE_CLASS_StripeAdapter::getPublicKey();
    }

    /**
     * Process mobile purchase status
     *
     * @param BOL_BillingSale $sale
     * @param array $purchaseOptions
     * @return mixed
     */
    public function processMobilePurchaseStatus(BOL_BillingSale $sale, $purchaseOptions = [])
    {
        $billingGateway = BOL_BillingService::getInstance()->findGatewayById($sale->gatewayId);
        $adapterClassName = $billingGateway->adapterClassName;
        $adapterInstance = OW::getClassInstance($adapterClassName);

        return $adapterInstance->processApplicationPayment($sale, $purchaseOptions);
    }
 
    /**
     * Get mobile pay pal gateway data
     * 
     * @return array
     */
    public function getMobilePayPalGatewayData() 
    {
        $paypalAdapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();
        $formActionUrl = null;
        $fields = $paypalAdapter->getFields(null, true);
        $options = [];

        // collect options
        foreach ($fields as $key => $field) {
            if( $key == 'form_action_url') {
                $formActionUrl = $field;

                continue;
            }

            $options[] = [
                'key' => $key, 
                'value' => ($key == 'return' || $key == 'cancel_return' ? OW::getRouter()->urlForRoute('base_index') : $field)
            ];
        }

        return [
            'options' => $options, 
            'formUrl' => $formActionUrl
        ];
    }
 
    /**
     * Get mobile sale fields
     * 
     * @param BOL_BillingSale $sale
     */
    public function getMobileSaleFields(BOL_BillingSale $sale)
    {
        $billingGateway = BOL_BillingService::getInstance()->findGatewayById($sale->gatewayId);
        $adapterClassName = $billingGateway->adapterClassName;
        $adapterInstance = OW::getClassInstance($adapterClassName);
        $extraFields = $adapterInstance->getExtraFields($sale);

        return $extraFields;
    }

    /**
     * Process mobile purchase token
     * 
     * @param array $creditCardData
     * @return string
     */
    public function processMobilePurchaseToken(array $creditCardData)
    {
        $gatewayKey = isset($creditCardData['gatewayKey']) 
            ? $creditCardData['gatewayKey'] 
            : '';

        $billingGateway = BOL_BillingService::getInstance()->findGatewayByKey($gatewayKey);
        $adapterClassName = $billingGateway->adapterClassName;
        $adapterInstance = OW::getClassInstance($adapterClassName);
        $token = $adapterInstance->createToken($creditCardData);

        return $token;
    }

    /**
     * Process mobile purchase
     * 
     * @param string | number $token
     * @param BOL_BillingSale $sale
     * @return array
     */
    public function processMobilePurchase($token, BOL_BillingSale $sale)
    {
        $billingGateway = BOL_BillingService::getInstance()->findGatewayById($sale->gatewayId);
        $adapterClassName = $billingGateway->adapterClassName;
        $adapterInstance = OW::getClassInstance($adapterClassName);

        return $adapterInstance->processApplicationPayment($token, $sale);
    }

    /**
     * Init mobile purchase session
     *
     * @param array $billingSessionData
     * @param integer $userId
     * @throws Exception
     * @return integer
     */
    public function initMobilePurchaseSession($billingSessionData, $userId) 
    {
        $pluginKey = isset($billingSessionData['pluginKey']) 
            ? $billingSessionData['pluginKey'] 
            : '';

        $gatewayKey = isset($billingSessionData['gatewayKey']) 
            ? $billingSessionData['gatewayKey'] 
            : '';

        $product = isset($billingSessionData['product']) 
            ? $billingSessionData['product'] 
            : [];
 
        $productId = isset($product['id']) 
            ? floatval($product['id']) 
            : 0;

        $productPrice = isset($product['price']) 
            ? floatval($product['price']) 
            : 0;

        $productPeriod = isset($product['period']) 
            ? $product['period'] 
            : 30;
 
        $isProductRecurring = isset($product['isRecurring']) 
            ? $product['isRecurring'] 
            : false;

        $periodUnits = isset($product['periodUnits']) 
            ? $product['periodUnits'] 
            : null;

        $billingService = BOL_BillingService::getInstance();
        $productDescription = '';

        switch ($pluginKey)
        {
            case SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY:
                $productAdapter = new MEMBERSHIP_CLASS_MembershipPlanProductAdapter();
                $productDescription = MEMBERSHIP_BOL_MembershipService::getInstance()->
                        getFormattedPlan($productPrice, $productPeriod, $isProductRecurring, null, $periodUnits);
                break;

            case SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY:
                $productAdapter = new USERCREDITS_CLASS_UserCreditsPackProductAdapter();
                $productDescription = USERCREDITS_BOL_CreditsService::
                        getInstance()->getPackTitle($productPrice, (isset($product['credits']) ? $product['credits'] : 0));
                break;

            default:
                throw new Exception('Plugin is not supported');
        }

        // sale object
        $sale = new BOL_BillingSale();
        $sale->pluginKey = $pluginKey;
        $sale->entityDescription = strip_tags($productDescription);
        $sale->entityKey = $productAdapter->getProductKey();
        $sale->entityId = $productId;
        $sale->price = $productPrice;
        $sale->period = $productPeriod;
        $sale->userId = $userId;
        $sale->recurring = $isProductRecurring;
        $sale->periodUnits = $periodUnits;

        $saleId = $billingService->initSale($sale, $gatewayKey);

        return $saleId;
    }

    /**
     * Add trial membership
     * 
     * @param integer $userId
     * @param MEMBERSHIP_BOL_MembershipPlan $plan
     * @return void
     */
    public function addTrialMembership($userId, MEMBERSHIP_BOL_MembershipPlan $plan) 
    {
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        $userMembership = new MEMBERSHIP_BOL_MembershipUser();

        $userMembership->userId = $userId;
        $userMembership->typeId = $plan->typeId;
        $userMembership->expirationStamp = time() + $plan->period * 
                MEMBERSHIP_BOL_MembershipService::getInstance()->getPeriodUnitFactor($plan->periodUnits);

        $userMembership->recurring = 0;
        $userMembership->trial = 1;

        $membershipService->setUserMembership($userMembership);
        $membershipService->addTrialPlanUsage($userId, $plan->id, $plan->period, $plan->periodUnits);
    }

    /**
     * Get full membership info
     * 
     * @param integer $id
     * @param integer $userId
     * @return array
     */
    public function getFullMembershipInfo($id, $userId)
    {
        $membershipId = (int) $id;
        $authService = BOL_AuthorizationService::getInstance();
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        $defaultRole = $authService->getDefaultRole();
        $groupActionList = $membershipService->getSubscribePageGroupActionList();
        $userMembership = $membershipService->getUserMembership($userId);
        $userRoleIds = array();
        $currentMembershipTitle = '';

        // get default membership level 
        if ( !$membershipId ) 
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
            $mTypes = array($membershipService->findTypeById($membershipId) );
        }

        // find user's roles ids
        if ( $userMembership ) 
        {
            $type = $membershipService->findTypeById($userMembership->typeId);

            if ( $type ) 
            {
                $userRoleIds[] = $type->roleId;
                $currentMembershipTitle = $membershipService->getMembershipTitle($type->roleId);
            }
        }

        $permissions = $authService->getPermissionList();
        $perms = array();

        // get list of allowed permissions for user's roles
        foreach ( $permissions as $permission ) 
        {
            /* @var $permission BOL_AuthorizationPermission */
            $perms[$permission->roleId][$permission->actionId] = true;
        }

        $exclude = $membershipService->getUserTrialPlansUsage($userId);
        $mPlans = $membershipService->getTypePlanList( $exclude );
        $mTypesPermissions = array();

        foreach ( $mTypes as $membership )
        {
            $mId = $membership->id;
            $plans = isset($mPlans[$mId]) ? $mPlans[$mId] : null;

            $data = array(
                'id' => $mId,
                'title' => $membershipService->getMembershipTitle($membership->roleId),
                'roleId' => $membership->roleId,
                'permissions' => isset($perms[$membership->roleId]) ? $perms[$membership->roleId] : null,
                'current' => isset($userRoleIds) ? in_array($membership->roleId, $userRoleIds) : null,
                'plans' =>  $plans
            );

            $mTypesPermissions[$membershipId] = $data;
        }

        // get permissions labeles
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $allowedPermissions = array();
        $showMembershipActions = OW::getConfig()->getValue('skmobileapp', 'inapps_show_membership_actions');
        $appMembershipActions = $showMembershipActions != self::ALL_MEMBERSHIP_ACTIONS
            ? SKMOBILEAPP_BOL_Service::getInstance()->getAppPermissionList()
            : array();


        // filter permissions actions related  to admin settings 
        foreach( $groupActionList as $groupAction ) 
        {
            foreach( $groupAction['actions'] as $action ) 
            {
                foreach( $mTypesPermissions as $mTypesPermission ) 
                {
                    if ( isset($mTypesPermission['permissions'][$action->id]) ) 
                    {
                        $allowToAdd  = $showMembershipActions == self::ALL_MEMBERSHIP_ACTIONS ? true : false;

                        if ( !$allowToAdd ) 
                        {
                            foreach ( $appMembershipActions as $appMembershipData ) 
                            {
                                if ($appMembershipData['group'] == $groupAction['name'] && in_array($action->name, $appMembershipData['actions'])) {
                                    $allowToAdd = true;

                                    break;
                                }
                            }
                        }

                        if (!$allowToAdd) {
                            continue;
                        }

                        $permissionLabel = !empty($dataLabels[$groupAction['name']]['actions'][$action->name])
                            ? $dataLabels[$groupAction['name']]['actions'][$action->name]
                            : $action->name;

                        if ( !isset($allowedPermissions[$groupAction['name']]) ) 
                        {
                            $allowedPermissions[$groupAction['name']] = array(
                                'label' => !empty($dataLabels[$groupAction['name']]) ? $dataLabels[$groupAction['name']]['label'] : $groupAction['name'],
                                'permissions' => [
                                    $permissionLabel
                                ]
                            );
                        } 
                        else 
                        {
                            $allowedPermissions[$groupAction['name']]['permissions'][] = $permissionLabel;
                        }
                    }
                }
            }
        }

        // process allowed permissions
        if ( $allowedPermissions ) 
        {
            $processedPermissions = array();
            foreach( $allowedPermissions as $allowedPermission ) 
            {
                $processedPermissions[] = $allowedPermission;
            }

            $allowedPermissions = $processedPermissions;
        }

        // process plans
        $processedPlans = [];

        if (isset($mPlans[$membershipId])) {
            foreach( $mPlans[$membershipId] as $plan )
            {
                $processedPlans[] = [
                    'id' => (int) $plan['dto']->id,
                    'price' => floatval($plan['dto']->price),
                    'period' => (int) $plan['dto']->period,
                    'periodUnits' => $plan['dto']->periodUnits,
                    'productId' => $plan['productId'],
                    'isRecurring' => $plan['dto']->recurring == 1
                ];
            }
        }

        $isActive = $userMembership && $userMembership->typeId == $membershipId || !$userMembership && !$membershipId;

        return array(
            'id' => (int) $membershipId,
            'title' => $mTypesPermissions[$membershipId]['title'],
            'isActive' => $isActive,
            'isActiveAndTrial' => $isActive && $userMembership && $userMembership->trial == 1,
            'isPlansAvailable' => count($processedPlans) > 1,
            'expire' =>  $isActive && $userMembership
                ? UTIL_DateTime::formatDate($userMembership->expirationStamp) 
                : null,
            'isRecurring' => $isActive && $userMembership && $userMembership->recurring == 1
                ? true
                : false,
            'actions' => $allowedPermissions,
            'plans' => $processedPlans
        );
    }

    /**
     * Get all memberships
     * 
     * @param integer $userId
     * @return array
     */
    public function getMemberships($userId)
    {
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();

        $accTypeName = BOL_UserService::getInstance()->findUserById($userId)->getAccountType();
        $accType = BOL_QuestionService::getInstance()->findAccountTypeByName($accTypeName);

        $mTypes = $membershipService->getTypeList($accType->id);

        /* @var $defaultRole BOL_AuthorizationRole */
        $defaultRole = $authService->getDefaultRole();

        /* @var $default MEMBERSHIP_BOL_MembershipType */
        $default = new MEMBERSHIP_BOL_MembershipType();
        $default->roleId = $defaultRole->id;

        $mTypes = array_merge(array($default), $mTypes);

        $userMembership = $membershipService->getUserMembership($userId);
        $exclude = $membershipService->getUserTrialPlansUsage($userId);
        $mPlans = $membershipService->getTypePlanList($exclude);
        $memberships = [];

        foreach ( $mTypes as $membership )
        {
            $isActive = $userMembership && $userMembership->typeId == $membership->id || !$userMembership && !$membership->id;

            $data = array(
                'id' => (int) $membership->id,
                'title' => $membershipService->getMembershipTitle($membership->roleId),
                'isActive' => $isActive,
                'isActiveAndTrial' => $isActive && $userMembership && $userMembership->trial == 1,
                'isPlansAvailable' => isset($mPlans[$membership->id]),
                'expire' =>  $isActive && $userMembership
                    ? UTIL_DateTime::formatDate($userMembership->expirationStamp) 
                    : null,
                'isRecurring' => $isActive && $userMembership && $userMembership->recurring == 1
                    ? true
                    : false
            );

            $memberships[] = $data;
        }

        return $memberships;
    }

    /**
     * Create and deliver
     * 
     * @param array $result
     * @param array $data
     * @param integer $userId
     */
    public function createAndDeliver($result, $data, $userId)
    {
        $language = OW::getLanguage();
        $billingService = BOL_BillingService::getInstance();

        $productId = $data['originalProductId'];
        $purchaseTime = $result['purchaseTime'];
        $orderId = $result['orderId'];
        $purchaseToken = $result['purchaseToken'];

        if ($data['platform'] == self::PLATFORM_ANDROID)
        {
            $product = $this->findProductByAndroidProductId($productId);
            $inappsPlatformLabel = $language->text('skmobileapp', 'inapps_' . self::PLATFORM_ANDROID . '_platform_label');
        }
        else
        {
            $product = $this->findProductByItunesProductId($productId);
            $inappsPlatformLabel = $language->text('skmobileapp', 'inapps_' . self::PLATFORM_IOS . '_platform_label');
        }

        if ( !isset($product['pluginKey']) )
        {
            throw new Exception('Product not found');
        }

        // sale object
        $sale = new BOL_BillingSale();
        $sale->pluginKey = $product['pluginKey'];
        $sale->entityDescription = $product['entityDescription'] . ' ' . $inappsPlatformLabel;
        $sale->entityKey = $product['entityKey'];
        $sale->entityId = $product['entityId'];
        $sale->price = $product['price'];
        $sale->period = $product['period'];
        $sale->userId = $userId;
        $sale->recurring = $product['recurring'];
        $sale->periodUnits = ( isset($product['periodUnits']) ? $product['periodUnits'] : null );

        $dateProduct = array(
            'userId' => $userId,
            'username' => BOL_UserService::getInstance()->getUserName($userId),
            'pluginKey' => $product['pluginKey'],
            'entityDescription' => $product['entityDescription']
        );

        if ( isset($product['membershipTitle']) )
        {
            $dateProduct['membershipTitle'] = $product['membershipTitle'];
        }

        $saleId = $billingService->initSale($sale, SKMOBILEAPP_CLASS_InAppPurchaseAdapter::GATEWAY_KEY);


        $sale = $billingService->getSaleById($saleId);

        $sale->timeStamp = $purchaseTime / 1000;
        $sale->transactionUid = $orderId;
        $sale->extraData = json_encode(array(
            'orderId' => $orderId,
            'platform' => isset($data['platform']) ? $data['platform'] : 'unknown',
            'purchaseToken' => $purchaseToken,
            'extra' => $data['transactionData']
        ));

        BOL_BillingSaleDao::getInstance()->save($sale);

        $productAdapter = null;
        switch ( $sale->pluginKey )
        {
            case SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY:
                $productAdapter = new MEMBERSHIP_CLASS_MembershipPlanProductAdapter();
                break;

            case SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY:
                $productAdapter = new USERCREDITS_CLASS_UserCreditsPackProductAdapter();
                break;
        }

        return $billingService->deliverSale($productAdapter, $sale);
    }

    /**
     * Find product by android product id
     * 
     * @param integer $productId
     * @return array
     */
    public function findProductByAndroidProductId( $productId )
    {
        $entityKey = strtolower(substr($productId, 0, strrpos($productId, '_')));
        $entityId = (int) substr($productId, strrpos($productId, '_') + 1);

        if ( !strlen($entityKey) || !$productId )
        {
            return null;
        }

        $pm = OW::getPluginManager();
        $return = array();

        switch ( $entityKey )
        {
            case 'membership_plan':
                if ( !$pm->isPluginActive(SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY) )
                {
                    return null;
                }

                $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

                $plan = $membershipService->findPlanById($entityId);
                if ( !$plan )
                {
                    return null;
                }

                $type = $membershipService->findTypeById($plan->typeId);
                $return['pluginKey'] = SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY;
                $return['entityDescription'] = $membershipService->getFormattedPlan(
                    $plan->price, $plan->period, $plan->recurring,
                    null, $plan->periodUnits);
                $return['membershipTitle'] = $membershipService->getMembershipTitle($type->roleId);
                $return['price'] = floatval($plan->price);
                $return['period'] = $plan->period;
                $return['recurring'] = $plan->recurring;
                $return['periodUnits'] = $plan->periodUnits;

                break;

            case 'user_credits_pack':
                if ( !$pm->isPluginActive(SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY) )
                {
                    return null;
                }

                $creditsService = USERCREDITS_BOL_CreditsService::getInstance();

                $pack = $creditsService->findPackById($entityId);
                if ( !$pack )
                {
                    return null;
                }

                $return['pluginKey'] = SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY;
                $return['entityDescription'] = $creditsService->getPackTitle($pack->price, $pack->credits);
                $return['price'] = floatval($pack->price);
                $return['period'] = 30;
                $return['recurring'] = 0;

                break;
        }

        $return['entityKey'] = $entityKey;
        $return['entityId'] = $entityId;

        return $return;
    }

    /**
     * Find product by itunes product id
     * 
     * @param @integer $productId
     * @return array
     */
    public function findProductByItunesProductId( $productId )
    {
        $entityKey = strtolower(substr($productId, 0, strrpos($productId, '_')));
        $entityId = (int) substr($productId, strrpos($productId, '_') + 1);

        if ( !strlen($entityKey) || !$productId )
        {
            return null;
        }

        $pm = OW::getPluginManager();
        $return = array();

        switch ( $entityKey )
        {
            case 'membership_plan':
                if ( !$pm->isPluginActive(SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY) )
                {
                    return null;
                }

                $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

                $plan = $membershipService->findPlanById($entityId);
                if ( !$plan )
                {
                    return null;
                }

                $type = $membershipService->findTypeById($plan->typeId);

                $return['pluginKey'] = SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY;
                $return['entityDescription'] = $membershipService->getFormattedPlan(
                    $plan->price, $plan->period, $plan->recurring,
                    null, $plan->periodUnits
                );
                $return['membershipTitle'] = $membershipService->getMembershipTitle($type->roleId);

                $return['price'] = floatval($plan->price);
                $return['period'] = $plan->period;
                $return['recurring'] = $plan->recurring;

                break;

            case 'user_credits_pack':
                if ( !$pm->isPluginActive(SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY) )
                {
                    return null;
                }

                $creditsService = USERCREDITS_BOL_CreditsService::getInstance();

                $pack = $creditsService->findPackById($entityId);
                if ( !$pack )
                {
                    return null;
                }

                $return['pluginKey'] = SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY;
                $return['entityDescription'] = $creditsService->getPackTitle($pack->price, $pack->credits);
                $return['price'] = floatval($pack->price);
                $return['period'] = 30;
                $return['recurring'] = 0;

                break;
        }

        $return['entityKey'] = $entityKey;
        $return['entityId'] = $entityId;

        return $return;
    }

    /**
     * Validate android purchase
     * 
     * @param array $data
     * @return array
     */
    public function validateAndroidPurchase($data)
    {
        $transactionData = $data['transactionData'];
        $receipt = json_decode($transactionData['receipt'], 1);

        $androidPurchase = new SKMOBILEAPP_CLASS_AndroidPurchase();
        $result = $androidPurchase->validateReceipt($transactionData['signature'], $transactionData['receipt']);

        $result = array(
            'status' => $result ? true: false,
            'productId' => $transactionData['productId'],
            'purchaseTime' => $receipt['purchaseTime'],
            'orderId' => isset($receipt['orderId']) ? $receipt['orderId'] : 'test_' . $receipt['purchaseTime'],
            'purchaseToken' => $receipt['purchaseToken'],
        );

        return $result;
    }

    /**
     * Validate IOS purchase
     * 
     * @param array $data
     * @return array
     */
    public function validateIOSPurchase($data)
    {
        $itunesPurchase = new SKMOBILEAPP_CLASS_ItunesPurchase();

        $transactionData = $data['transactionData'];
        $transactionId = isset($transactionData['transactionId']) ? $transactionData['transactionId'] : '';

        $result = $itunesPurchase->validateReceipt($transactionData['receipt'], $transactionId);

        return array(
            'status' => $result ? true: false,
            'productId' => $result ? $result['product_id'] : null,
            'purchaseTime' => $result ? $result['purchase_date_ms'] : null,
            'orderId' => $result ?  $result['transaction_id'] : null,
            'purchaseToken' => null
        );
    }

    /**
     * Delete inapps purchase by object
     * 
     * @param SKMOBILEAPP_BOL_InappsPurchase $inappsPurchase
     * @return void
     */
    public function deleteInappsPurchaseByObject( SKMOBILEAPP_BOL_InappsPurchase $inappsPurchase)
    {
        $this->purchaseDao->delete($inappsPurchase);
    }

    /**
     * Find inapp by membership Id
     */
    public function findInappByMembershipId($membershipId)
    {
        $membershipId = (int) $membershipId;

        return $this->purchaseDao->findByMembershipId($membershipId);
    }

    /**
     * Update expiration purchase
     */
    public function updateExpirationPurchase( SKMOBILEAPP_BOL_ExpirationPurchase $expirationPurchase )
    {
        return $this->expirationPurchaseDao->save($expirationPurchase);
    }

    /**
     * Update inapps purchase
     */
    public function updateInappsPurchase( SKMOBILEAPP_BOL_InappsPurchase $inappsPurchase )
    {
        return $this->purchaseDao->save($inappsPurchase);
    }

    /**
     * Find expiration purchase
     */
    public function findExpirationPurchase( $userId, $membershipId )
    {
        $userId = intval($userId);
        $membershipId = intval($membershipId);

        return $this->expirationPurchaseDao->findExpirationPurchase( $userId, $membershipId );
    }

    /**
     * extend membership user
     */
    public function extendMembershipUser( $saleId, $membershipId )
    {
        $saleId = intval($saleId);
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

        $membershipUser = $membershipService->getMembershipUserById($membershipId);

        if ( !empty($membershipUser) )
        {
            $plan = $this->getMembershipPlanBySaleId($saleId);

            if ( !empty($plan) )
            {
                $membershipUser->expirationStamp = time() + (int) $plan->period * $membershipService->getInstance()->getPeriodUnitFactor($plan->periodUnits);
                $membershipService->updateMembershipUser($membershipUser);
            }
        }
    }

    /**
     * Get membership plan by saleId
     */
    public function getMembershipPlanBySaleId( $saleId )
    {
        $saleId = intval($saleId);
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

        $sale = BOL_BillingService::getInstance()->getSaleById($saleId);

        if ( !empty($sale) )
        {
            return $membershipService->findPlanById($sale->entityId);
        }

        return null;
    }

    /**
     * Delete expiration purchase
     */
    public function deleteExpirationPurchase( $membershipId, $userId )
    {
        $membershipId = intval($membershipId);
        $userId = intval($userId);

        $example = new OW_Example();
        $example->andFieldEqual('membershipId', $membershipId);
        $example->andFieldEqual('userId', $userId);

        $this->expirationPurchaseDao->deleteByExample($example);
    }

    /**
     * Delete inapps purchase
     */
    public function deleteInappsPurchase( $membershipId )
    {
        $membershipId = intval($membershipId);

        $membershipId = intval($membershipId);
        $example = new OW_Example();
        $example->andFieldEqual('membershipId', $membershipId);

        $this->purchaseDao->deleteByExample($example);
    }

    /**
     * Delete expiration purchase by user Id
     */
    public function deleteExpirationPurchaseByUserId( $userId )
    {
        $userId = intval($userId);

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->expirationPurchaseDao->deleteByExample($example);
    }

    /**
     * Reset membership user
     */
    public function resetMembershipUser( MEMBERSHIP_BOL_MembershipUser $membershipUser )
    {
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

        $membershipUser->expirationStamp = time();
        $membershipService->updateMembershipUser($membershipUser);
    }

    /**
     * Expire users subscriptions
     */
    public function expireUsersSubscriptions( $first, $count )
    {
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();
        $billingService = BOL_BillingService::getInstance();

        $list = $this->expirationPurchaseDao->findExpiredSubscriptions($first, $count);

        if ( empty($list) )
        {
            return true;
        }

        foreach ( $list as $expirationPurchase )
        {
            /* @var SKMOBILEAPP_BOL_ExpirationPurchase $expirationPurchase */

            $info = $this->findInappByMembershipId($expirationPurchase->membershipId);
            $membershipUser = $membershipService->getMembershipUserById($expirationPurchase->membershipId);
            $sale = $billingService->getSaleById($info->saleId);

            if ( empty($info) || empty($membershipUser) || empty($sale) || $expirationPurchase->counter >= self::COUNTER_TO_REMOVE )
            {
                if ( !empty($membershipUser) )
                {
                    //the next time you start the crown, it is automatically deleted
                    $this->resetMembershipUser($membershipUser);
                }

                if ( !empty($info) )
                {
                    $this->deleteInappsPurchaseByObject($info);
                }

                $this->expirationPurchaseDao->delete($expirationPurchase);

                break;
            }

            /* @var SKMOBILEAPP_BOL_InappsPurchase $info */

            $extraData = json_decode($sale->extraData, true);

            $purchaseToken = isset($extraData['purchaseToken']) ? $extraData['purchaseToken'] : null;
            $productId = $sale->entityKey . '_' . $sale->entityId;
            $receipt = isset($extraData['extra']['receipt']) ? $extraData['extra']['receipt'] : null;
            $transactionId = isset($extraData['extra']['transactionId']) ? $extraData['extra']['transactionId'] : null;

            // check
            switch ( $info->platform )
            {
                case self::PLATFORM_ANDROID:

                    $inAppPurchaseValidator = new SKMOBILEAPP_CLASS_AndroidPurchase();
                    $validatePurchaseSubscription = $inAppPurchaseValidator->activePurchasesSubscriptions($productId, $purchaseToken);

                    break;

                case self::PLATFORM_IOS:

                    $inAppPurchaseValidator = new SKMOBILEAPP_CLASS_ItunesPurchase();
                    $validatePurchaseSubscription = $inAppPurchaseValidator->activePurchasesSubscriptions($receipt, $transactionId, $productId);

                    break;

                default:
            }

            if ( isset($validatePurchaseSubscription) )
            {
                if ( $validatePurchaseSubscription == SKMOBILEAPP_CLASS_AbstractInAppPurchase::FAILURE )
                {
                    $expirationPurchase->counter += 1;
                    $expirationPurchase->expirationTime = time() + self::NEXT_CHECK_TIME;

                    $this->updateExpirationPurchase($expirationPurchase);
                }
                elseif ($validatePurchaseSubscription == SKMOBILEAPP_CLASS_AbstractInAppPurchase::CANCEL)
                {
                    if ( !empty($membershipUser) )
                    {
                        //the next time you start the crown, it is automatically deleted
                        $this->resetMembershipUser($membershipUser);
                    }

                    $this->expirationPurchaseDao->delete($expirationPurchase);
                    $this->deleteInappsPurchaseByObject($info);
                }
                else
                {
                    //register a purchase

                    $transactionUid = '';

                    if ( isset($validatePurchaseSubscription['orderId']) )
                    {
                        $transactionUid = $validatePurchaseSubscription['orderId'];
                    }
                    else if ( $validatePurchaseSubscription['transaction_id'] )
                    {
                        $transactionUid = $validatePurchaseSubscription['transaction_id'];
                    }

                    $sale->id = null;
                    $sale->timeStamp = time();
                    $sale->transactionUid = $transactionUid;

                    $sale->extraData = json_encode(array(
                        'orderId' => $transactionUid,
                        'purchaseToken' => $purchaseToken,
                        'extra' => isset($extraData['extra']) ? $extraData['extra'] : [],
                        'dataValidatePurchase' => $validatePurchaseSubscription
                    ));

                    $saleId = $billingService->initSale($sale, SKMOBILEAPP_CLASS_InAppPurchaseAdapter::GATEWAY_KEY);
                    $sale = $billingService->getSaleById($saleId);
                    $sale->status = BOL_BillingSaleDao::STATUS_DELIVERED;

                    BOL_BillingSaleDao::getInstance()->save($sale);

                    // delete temporary record
                    $this->expirationPurchaseDao->delete($expirationPurchase);
                }
            }
        }

        return true;
    }
}
