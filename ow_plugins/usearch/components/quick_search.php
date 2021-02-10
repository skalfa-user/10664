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
 * User quick search component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>, Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.components
 * @since 1.5.3
 */
class USEARCH_CMP_QuickSearch extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $form = OW::getClassInstance('USEARCH_CLASS_QuickSearchForm', $this);
        $this->addForm($form);

        $this->assign('form', $form);
        $this->assign('advancedUrl', OW::getRouter()->urlForRoute('users-search'));
        $this->assign('questions', USEARCH_BOL_Service::getInstance()->getQuickSerchQuestionNames());
    }
}
