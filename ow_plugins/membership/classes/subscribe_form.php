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
 * @author Pryadkin Sergey <GiperProger@gmai.com>
 * @package ow_plugins.membership.classes
 * @since 1.8.0
 */
class MEMBERSHIP_CLASS_SubscribeForm extends Form
{
    public function __construct()
    {
        parent::__construct('subscribe-form');

        $this->addFields();
    }

    protected function addFields()
    {

        $planField = new RadioGroupItemField('plan');
        $planField->setRequired();
        $this->addElement($planField);

        $gatewaysField = new BillingGatewaySelectionField('gateway');
        $gatewaysField->setRequired();
        $gatewaysField->addAttribute('autocomplete', 'off');
        $this->addElement($gatewaysField);

        $submit = new Submit('subscribe');
        $submit->setValue(OW::getLanguage()->text('membership', 'checkout'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();
        $lang = OW::getLanguage();
        $userId = OW::getUser()->getId();

        $billingService = BOL_BillingService::getInstance();
        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

        $url = OW::getRouter()->urlForRoute('membership_subscribe');

        if ( !$plan = $membershipService->findPlanById($values['plan']) )
        {
            OW::getFeedback()->error($lang->text('membership', 'plan_not_found'));
            OW::getApplication()->redirect($url);
        }

        if ( $plan->price == 0 ) // trial plan
        {
            // check if trial plan used
            $used = $membershipService->isTrialUsedByUser($userId);

            if ( $used )
            {
                OW::getFeedback()->error($lang->text('membership', 'trial_used_error'));
                OW::getApplication()->redirect($url);
            }
            else // give trial plan
            {
                $userMembership = new MEMBERSHIP_BOL_MembershipUser();

                $userMembership->userId = $userId;
                $userMembership->typeId = $plan->typeId;
                $userMembership->expirationStamp = time() + $plan->period * MEMBERSHIP_BOL_MembershipService::getInstance()->getPeriodUnitFactor($plan->periodUnits);
                $userMembership->recurring = 0;
                $userMembership->trial = 1;

                $membershipService->setUserMembership($userMembership);
                $membershipService->addTrialPlanUsage($userId, $plan->id, $plan->period, $plan->periodUnits);

                OW::getFeedback()->info($lang->text('membership', 'trial_granted', array('period' => $plan->period, 'periodUnits' => OW::getLanguage()->text('membership', $plan->periodUnits))));
                OW::getApplication()->redirect($url);
            }
        }

        if ( empty($values['gateway']['url']) || empty($values['gateway']['key']) )
        {
            OW::getFeedback()->error($lang->text('base', 'billing_gateway_not_found'));
            OW::getApplication()->redirect($url);
        }

        $gateway = $billingService->findGatewayByKey($values['gateway']['key']);
        if ( !$gateway || !$gateway->active )
        {
            OW::getFeedback()->error($lang->text('base', 'billing_gateway_not_found'));
            OW::getApplication()->redirect($url);
        }

        // create membership plan product adapter object
        $productAdapter = new MEMBERSHIP_CLASS_MembershipPlanProductAdapter();

        // sale object
        $sale = new BOL_BillingSale();
        $sale->pluginKey = 'membership';
        $sale->entityDescription = $membershipService->getFormattedPlan($plan->price, $plan->period, $plan->recurring, null, $plan->periodUnits);
        $sale->entityKey = $productAdapter->getProductKey();
        $sale->entityId = $plan->id;
        $sale->price = floatval($plan->price);
        $sale->period = $plan->period;
        $sale->userId = $userId ? $userId : 0;
        $sale->recurring = $plan->recurring;
        $sale->periodUnits = $plan->periodUnits;

        $saleId = $billingService->initSale($sale, $values['gateway']['key']);

        if ( $saleId )
        {
            // sale Id is temporarily stored in session
            $billingService->storeSaleInSession($saleId);
            $billingService->setSessionBackUrl($productAdapter->getProductOrderUrl());

            // redirect to gateway form page
            OW::getApplication()->redirect($values['gateway']['url']);
        }
    }
}