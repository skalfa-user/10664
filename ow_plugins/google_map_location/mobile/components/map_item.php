<?php
/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.mobile.components
 * @since 1.0
 */

class GOOGLELOCATION_MCMP_MapItem extends GOOGLELOCATION_CMP_MapItem
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(OW::getPluginManager()->getPlugin('googlelocation')->getMobileCmpViewDir().'map_item.html');
    }
}