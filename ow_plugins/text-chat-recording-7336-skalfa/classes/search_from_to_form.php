<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Class SKTEXTCR_CLASS_SearchFromToForm
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CLASS_SearchFromToForm extends Form
{
    /**
     * Service
     *
     * @var SKTEXTCR_BOL_Service
     */
    protected $service;

    /**
     * SKTEXTCR_CLASS_SearchBadWordForm constructor.
     *
     * @param SKTEXTCR_BOL_DataSearch|null $params
     */
    public function __construct(  SKTEXTCR_BOL_DataSearch $params = null  )
    {
        parent::__construct( SKTEXTCR_BOL_Service::FORM_NAME_FROM_TO );

        $this->service = SKTEXTCR_BOL_Service::getInstance();

        $userNameFrom = new TextField('userNameFrom');
        $userNameFrom->setValue(!empty($params->userNameFrom) ? $params->userNameFrom : '');
        $userNameFrom->setLabel($this->service->getLanguageText('admin_search_from'));
        $userNameFrom->setRequired(true);
        $this->addElement($userNameFrom);

        $userNameTo = new TextField('userNameTo');
        $userNameTo->setValue(!empty($params->userNameTo) ? $params->userNameTo : '');
        $userNameTo->setLabel($this->service->getLanguageText('admin_search_to'));
        $userNameTo->setRequired(true);
        $this->addElement($userNameTo);

        $submit = new Submit('submit');
        $submit->setValue($this->service->getLanguageText('admin_go_from_to_button'));
        $this->addElement($submit);
    }
}