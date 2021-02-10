<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakytow <kainisoft@gmail.com>
 * @package ow_plugins.badwords.bol
 * @since 1.1
 */
class BADWORDS_BOL_BadwordsDto extends OW_Entity
{
    public $text;
    
    public function getText()
    {
        return $this->text;
    }
    
    public function setText( $value )
    {
        $this->text = (string)$value;
        
        return $this;
    }
}
