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
 * Users quick search component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>, Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.5.3
 */
class USEARCH_MCLASS_QuickSearchForm extends USEARCH_CLASS_QuickSearchForm
{
    public function __construct( $controller )
    {
        parent::__construct($controller);
        
        $questionNameList = $this->searchService->getQuickSerchQuestionNames();
        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);
        
        $list = json_decode(json_encode($questionDtoList), true);
        
        BASE_MCLASS_JoinFormUtlis::setLabels($this, $list);
        BASE_MCLASS_JoinFormUtlis::setInvitations($this, $list);
        BASE_MCLASS_JoinFormUtlis::setColumnCount($this);
    }
}