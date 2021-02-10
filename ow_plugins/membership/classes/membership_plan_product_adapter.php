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
 * Membership plan product adapter class.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.classes
 * @since 1.0
 */
class MEMBERSHIP_CLASS_MembershipPlanProductAdapter implements OW_BillingProductAdapter
{
    const PRODUCT_KEY = 'membership_plan';

    const RETURN_ROUTE = 'membership_subscribe';

    public function getProductKey()
    {
        return self::PRODUCT_KEY;
    }

    public function getProductOrderUrl()
    {
        if ( OW::getPluginManager()->isPluginActive('cvideoupload') )
        {
            $service = CVIDEOUPLOAD_BOL_Service::getInstance();
            
            if ( !empty($service->getSessionBackUrl()) )
            {
                return OW::getRouter()->urlForRoute('cvideoupload.video-upload');
            }
            else
            {
                return OW::getRouter()->urlForRoute(self::RETURN_ROUTE);
            }
        }
        else
        {
            return OW::getRouter()->urlForRoute(self::RETURN_ROUTE);
        }
    }

    public function deliverSale( BOL_BillingSale $sale )
    {
        $planId = $sale->entityId;

        $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

        $plan = $membershipService->findPlanById($planId);
        $type = $membershipService->findTypeByPlanId($planId);

        if ( $plan && $type )
        {
            $isRebill = false;
            if ( $sale->recurring )
            {
                /* @var $currentMembership MEMBERSHIP_BOL_MembershipUser */
                $currentMembership = $membershipService->getUserMembership($sale->userId);

                if ( $currentMembership && $currentMembership->recurring && $currentMembership->typeId == $type->id )
                {
                    $isRebill = true;
                }
            }

            $userMembership = new MEMBERSHIP_BOL_MembershipUser();
            
            $userMembership->userId = $sale->userId;
            $userMembership->typeId = $type->id;
            $userMembership->expirationStamp = time() + (int) $plan->period * $membershipService->getInstance()->getPeriodUnitFactor($plan->periodUnits);
            $userMembership->recurring = $sale->recurring;
    
            $membershipService->setUserMembership($userMembership);

            $title = $membershipService->getMembershipTitle($type->roleId);

            $event = new OW_Event(
                MEMBERSHIP_CLASS_EventHandler::ON_DELIVER_SALE_NOTIFICATION,
                array('membership' => $userMembership, 'sale' => $sale, 'is_rebill' => $isRebill)
            );
            OW::getEventManager()->trigger($event);
            $data = $event->getData();
            if ( $data['send_renewed_membership_email'] === true )
            {
                $membershipService->sendMembershipRenewedNotification($sale->userId, $title);
            }
            elseif ( $data['send_purchased_membership_email'] === true )
            {
                $membershipService->sendMembershipPurchasedNotification($sale->userId, $title, $userMembership->expirationStamp);
            }

            return true;
        }
        
        return false;
    }
}