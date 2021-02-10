<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
/**
 * Edit matchmaking rule
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.components
 * @since 1.0
 */
class MATCHMAKING_CMP_CreateCoefficient extends OW_Component
{
    /**
     * MATCHMAKING_BOL_Service
     */
    private $service;

    /**
     * Constructor.
     *
     */
    public function __construct( $name )
    {
        parent::__construct();

        $this->service = MATCHMAKING_BOL_Service::getInstance();
        $cmpId = uniqid();

        $this->assign('maxCoefficient', MATCHMAKING_BOL_Service::MAX_COEFFICIENT);
        $this->assign('coefficient', 0);
        $this->assign('cmpId', $cmpId);


        $jsParamsArray = array(
            'cmpId' => $cmpId,
            'itemsCount' => MATCHMAKING_BOL_Service::MAX_COEFFICIENT,
            'name' => $name,
            'checkedCoefficient' => 0,
            'respondUrl' => OW::getRouter()->urlFor('MATCHMAKING_CTRL_Admin', 'ruleEditFormResponder')
        );

        OW::getDocument()->addOnloadScript("var createCoefficient$cmpId = new CreateCoefficient(" . json_encode($jsParamsArray) . "); createCoefficient$cmpId.init();");
    }
}