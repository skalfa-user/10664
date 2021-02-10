<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class BILLINGSTRIPE_BOL_Payment extends OW_Entity
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
    public $stripePaymentId; // 'pi_'
    /**
     * @var string
     */
    public $stripeCustomerId; // 'cus_'
    /**
     * @var int
     */
    public $createStamp;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var string
     */
    public $card;
}
