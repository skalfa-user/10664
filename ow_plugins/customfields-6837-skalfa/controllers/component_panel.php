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

class CUSTOMFIELDS_CTRL_ComponentPanel extends BASE_CTRL_ComponentPanel
{
    public function profile($paramList)
    {
        // add custom CSS
        OW::getDocument()->addStyleSheet(CUSTOMFIELDS_BOL_Service::getPlugin()->getStaticCssUrl() . 'profile.css');

        return parent::profile($paramList);
    }
}