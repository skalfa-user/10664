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
 * @package ow.ow_plugins.mobile.hotlist.mobile.controllers
 * @since 1.7.6
 */
class HOTLIST_MCTRL_Responder extends OW_MobileActionController
{
    public function responder( $params )
    {
        HOTLIST_CMP_Floatbox::process($_POST);
    }
}