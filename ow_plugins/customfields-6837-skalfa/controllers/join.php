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

class CUSTOMFIELDS_CTRL_Join extends BASE_CTRL_Join
{
    public function index($params)
    {
        $urlParams = $_GET;

        if (is_array($params) && !empty($params))  {
            $urlParams = array_merge($_GET, $params);
        }

        parent::index($params);

        if (!empty($this->joinForm)) {
            $this->joinForm->setAction(
                OW::getRouter()->urlFor('SKADATE_CTRL_Join', 'joinFormSubmit', $urlParams)
            );
        }

        // change template
        $this->setTemplate(CUSTOMFIELDS_BOL_Service::getPlugin()->getCtrlViewDir() . 'join_index.html');
    }

    public function joinFormSubmit($params)
    {
        parent::joinFormSubmit($params);
        // change template
        $this->setTemplate(CUSTOMFIELDS_BOL_Service::getPlugin()->getCtrlViewDir() . 'join_index.html');
    }
}