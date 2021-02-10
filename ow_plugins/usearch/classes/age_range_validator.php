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
 * Age range form field validator
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.5.3
 */
class USEARCH_CLASS_AgeRangeValidator extends OW_Validator
{
    private $from;

    private $to;

    public function __construct( $from, $to )
    {
        $this->from = $from;
        $this->to = $to;

        $this->setErrorMessage(OW::getLanguage()->text('usearch', 'age_range_incorrect'));
    }

    public function isValid( $value )
    {
        if ( !isset($value['from']) || !isset($value['to']) )
        {
            return false;
        }

        if ( (int) $value['from'] < $this->from || (int) $value['from'] > $this->to )
        {
            return false;
        }

        if ( (int) $value['to'] < $this->from || (int) $value['to'] > $this->to )
        {
            return false;
        }

        if ( (int) $value['from'] > (int) $value['to'] )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            validate : function( value ){
                if ( value.from == undefined || value.to == undefined )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }
                if ( parseInt(value.from) < ".$this->from." || parseInt(value.from) > ".$this->to." )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }
                if ( parseInt(value.to) < ".$this->from." || parseInt(value.to) > ".$this->to." )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }

                if ( parseInt(value.from) > parseInt(value.to) )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }
            },
            getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";

        return $js;
    }
}