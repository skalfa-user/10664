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
 * Data Transfer Object for `usercredits_action` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.user_credits.bol
 * @since 1.0
 */
class USERCREDITS_BOL_Action extends OW_Entity
{
    /**
     * @var string
     */
    public $pluginKey;
    /**
     * @var string
     */
    public $actionKey;
    /**
     * @var int
     */
    public $isHidden = 0;
    /**
     * @var string
     */
    public $settingsRoute;
    /**
     * @var int
     */
    public $active = 1;
}