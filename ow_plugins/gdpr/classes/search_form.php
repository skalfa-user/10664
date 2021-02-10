<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class GDPR_CLASS_SearchForm extends Form
{
    public function __construct()
    {
        parent::__construct('gdpr-search-form');

        $text = new TextField('gdpr_search_input');
        $text->setRequired(true);
        $this->addElement($text->setLabel(OW::getLanguage()->text('gdpr', 'gdpr_search_input_label')));

        $submit = new Submit('gdpr_search_btn');
        $submit->setValue(OW::getLanguage()->text('gdpr', 'gdpr_search_btn_label'));
        $this->addElement($submit);
    }
}
