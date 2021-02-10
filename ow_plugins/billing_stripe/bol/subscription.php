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
 * Data Transfer Object for `stripe_subscription` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.billing_stripe.bol
 * @since 1.0
 */
class BILLINGSTRIPE_BOL_Subscription extends OW_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $saleId;
    /**
     * @var string
     */
    public $stripeSubscriptionId; // 'sub_'
    /**
     * @var string
     */
    public $stripeCustomerId; // 'cus_'
    /**
     * @var string
     */
    public $stripeInitialInvoiceId; // 'in_'
    /**
     * @var int
     */
    public $startStamp;
    /**
     * @var int
     */
    public $currentStartStamp;
    /**
     * @var int
     */
    public $currentEndStamp;
    /**
     * @var string
     */
    public $plan;
}