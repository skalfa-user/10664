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
 * Data Transfer Object for `usercredits_action_price` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.user_credits.bol
 * @since 1.6.1
 */
class USERCREDITS_BOL_ActionPrice extends OW_Entity
{
    /**
     * @var int
     */
    public $actionId;
    /**
     * @var int
     */
    public $accountTypeId;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var int
     */
    public $disabled = 0;
}