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
 * Class SKTEXTCR_CLASS_SearchByUserNameForm
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CLASS_SearchByUserNameForm extends Form
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
        parent::__construct( SKTEXTCR_BOL_Service::FORM_NAME_BY_NAME );

        $this->service = SKTEXTCR_BOL_Service::getInstance();

        $userName = new TextField('userName');
        $userName->setValue(!empty($params->userName) ? $params->userName : '');
        $userName->setLabel($this->service->getLanguageText('admin_search_by_username'));
        $userName->setRequired(true);
        $this->addElement($userName);

        $submit = new Submit('submit');
        $submit->setValue($this->service->getLanguageText('admin_go_sender_button'));
        $this->addElement($submit);
    }
}