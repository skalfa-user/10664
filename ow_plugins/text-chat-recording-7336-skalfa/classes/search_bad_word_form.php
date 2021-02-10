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
 * Class SKTEXTCR_CLASS_SearchBadWordForm
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CLASS_SearchBadWordForm extends Form
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
        parent::__construct( SKTEXTCR_BOL_Service::FORM_NAME_BAD_WORD );

        $this->service = SKTEXTCR_BOL_Service::getInstance();

        $badWord = new TextField('badWord');
        $badWord->setValue(!empty($params->badWord) ? $params->badWord : '');
        $badWord->setLabel($this->service->getLanguageText('admin_search_bag_word'));
        $badWord->setRequired(true);
        $this->addElement($badWord);

        $submit = new Submit('submit');
        $submit->setValue($this->service->getLanguageText('admin_go_bag_word_button'));
        $this->addElement($submit);
    }
}