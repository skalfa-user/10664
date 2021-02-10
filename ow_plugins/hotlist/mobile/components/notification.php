<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 *
 * @author Podiachev Evgenii <joker.OW2@gmail.com>
 * @package ow.ow_plugins.hotlist.mobile.components
 * @since 1.7.6
 */
class HOTLIST_MCMP_Notification extends OW_MobileComponent
{
    public function __construct( $message )
    {
        parent::__construct();
        $this->assign('message', strip_tags($message));
    }
}