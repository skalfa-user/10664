<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_CTRL_Join extends BASE_CTRL_Join
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index( $params )
    {
        $urlParams = $_GET;
        if ( is_array($params) && !empty($params) )
        {
            $urlParams = array_merge($_GET, $params);
        }

        parent::index($params);

        if ( !empty($this->joinForm) )
        {
            $this->joinForm->setAction(OW::getRouter()->urlFor('SKADATE_CTRL_Join', 'joinFormSubmit', $urlParams));
        }

        $this->setTemplate(OW::getPluginManager()->getPlugin('skadate')->getCtrlViewDir() . 'join_index.html');
    }

    public function joinFormSubmit( $params )
    {
        parent::joinFormSubmit($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('skadate')->getCtrlViewDir() . 'join_index.html');
    }
}
