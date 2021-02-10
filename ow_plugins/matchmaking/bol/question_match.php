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
 * Data Transfer Object for `matchmaking_question_match` table.
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.bol
 * @since 1.0
 */
class MATCHMAKING_BOL_QuestionMatch extends OW_Entity
{
    
    /**
     * 
     * @var string
     */
    public $questionName;
    
    /**
     * 
     * @var string
     */
    public $matchQuestionName;    
    
    /**
     * 
     * @var int
     */
    public $coefficient;
    
    /**
     * 
     * @var string
     */
    public $match_type = 'exact';
    
    /**
     * 
     * @var int
     */
    public $required = 0;
}