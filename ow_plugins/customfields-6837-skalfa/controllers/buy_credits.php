<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class CUSTOMFIELDS_CTRL_BuyCredits extends USERCREDITS_CTRL_BuyCredits
{
    public function index()
    {
        // add custom CSS
        OW::getDocument()->addStyleSheet(CUSTOMFIELDS_BOL_Service::getPlugin()->getStaticCssUrl() . 'subscribe.css');
        // call parent
        parent::index();
        // change template
        $this->setTemplate(CUSTOMFIELDS_BOL_Service::getPlugin()->getCtrlViewDir() . 'buy_credits_index.html');
    }
}