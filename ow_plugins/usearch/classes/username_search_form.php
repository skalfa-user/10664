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
 * Username search component
 *
 * @author Sergei Kiselev <arrserg@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.8.1
 */
class USEARCH_CLASS_UsernameSearchForm extends USEARCH_CLASS_MainSearchForm
{
    const FORM_SESSEION_VAR = 'USERNAME_SEARCH_FORM_DATA';
    
    /**
     * @param OW_ActionController $controller
     */
    public function __construct( $controller )
    {
        parent::__construct($controller, 'UsernameSearchForm');
    }

    protected function initForm($controller)
    {
        $this->controller = $controller;

        $controller->assign('section_prefix', self::SECTION_TR_PREFIX);
        $controller->assign('question_prefix', self::QUESTION_TR_PREFIX);

        $this->setId('UsernameSearchForm');

        $username = new TextField('username');
        $username->setLabel(OW::getLanguage()->text('usearch', 'username'));
        $username->setRequired();
        $this->addElement($username);

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);
    }

    protected function getFormSessionVar()
    {
        return self::FORM_SESSEION_VAR;
    }

}
