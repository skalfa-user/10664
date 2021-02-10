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
 * Users main search component
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.plugin.usearch.mobile.classes
 * @since 1.7.4
 */
class USEARCH_MCLASS_MainSearchForm extends USEARCH_CLASS_MainSearchForm
{
    /**
     * @param OW_ActionController $controller
     */
    public function __construct( $controller )
    {
        parent::__construct($controller);
        $questionService = BOL_QuestionService::getInstance();
        
        $list = $questionService->findSearchQuestionsForAccountType('all');
        
        BASE_MCLASS_JoinFormUtlis::setLabels($this, $list);
        BASE_MCLASS_JoinFormUtlis::setInvitations($this, $list);
        BASE_MCLASS_JoinFormUtlis::setColumnCount($this);
    }
}