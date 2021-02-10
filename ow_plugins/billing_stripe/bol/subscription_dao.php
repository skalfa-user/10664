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
 * Data Access Object for `stripe_subscription` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.billing_stripe.bol
 * @since 1.0
 */
class BILLINGSTRIPE_BOL_SubscriptionDao extends OW_BaseDao
{
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BILLINGSTRIPE_BOL_SubscriptionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BILLINGSTRIPE_BOL_SubscriptionDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BILLINGSTRIPE_BOL_Subscription';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'billingstripe_subscription';
    }

    /**
     * @param $id
     * @return BILLINGSTRIPE_BOL_Subscription
     */
    public function findByStripeId( $id )
    {
        $example = new OW_Example();
        $example->andFieldEqual('stripeSubscriptionId', $id);

        return $this->findObjectByExample($example);
    }
}