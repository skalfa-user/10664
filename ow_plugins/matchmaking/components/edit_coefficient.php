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
class MATCHMAKING_CMP_EditCoefficient extends OW_Component
{
    /**
     * MATCHMAKING_BOL_Service
     */
    private $service;

    /**
     * Constructor.
     *
     */
    public function __construct( $itemId, $coefficient )
    {
        parent::__construct();

        $this->service = MATCHMAKING_BOL_Service::getInstance();
        $cmpId = uniqid();

        $this->assign('maxCoefficient', MATCHMAKING_BOL_Service::MAX_COEFFICIENT);
        $this->assign('coefficient', $coefficient);
        $this->assign('cmpId', $cmpId);


        $jsParamsArray = array(
            'cmpId' => $cmpId,
            'itemsCount' => MATCHMAKING_BOL_Service::MAX_COEFFICIENT,
            'id' => $itemId,
            'checkedCoefficient' => $coefficient,
            'respondUrl' => OW::getRouter()->urlFor('MATCHMAKING_CTRL_Admin', 'ruleEditFormResponder')
        );

        OW::getDocument()->addOnloadScript("var editCoefficient$cmpId = new EditCoefficient(" . json_encode($jsParamsArray) . "); editCoefficient$cmpId.init();");
    }
}