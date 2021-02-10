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
 * Data Transfer Object for `smsverification_users` table.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.smsverification.bol
 * @since 1.7.6
 */
class SMSVERIFICATION_BOL_User extends OW_Entity
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
     * @var string
     */
    public $number;
    /**
     * @var string
     */
    public $code;
    
    /**
     * @var bool
     */
    public $isVeryfied;
    
    /**
     * @var string
     */
    public $country;
    
    /**
     * @var string
     */
    public $countryCode;
   
}