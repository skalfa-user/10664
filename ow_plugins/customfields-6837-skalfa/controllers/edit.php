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

class CUSTOMFIELDS_CTRL_Edit extends BASE_CTRL_Edit
{
    public function index($params)
    {
        parent::index($params);
        // change template
        $this->setTemplate(CUSTOMFIELDS_BOL_Service::getPlugin()->getCtrlViewDir() . 'edit_index.html');
    }
}