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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.matchmaking.classes
 * @since 1.0
 */
class MATCHMAKING_CLASS_Coefficient extends FormElement
{
    /**
     * @var integer
     */
    protected $count;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, $value = 0 )
    {
        parent::__construct($name);

        $this->count = MATCHMAKING_BOL_Service::MAX_COEFFICIENT;
        $this->setValue($value);
        $this->addValidator(new CoefficientValidator());
    }

    /**
     * @see FormElement::renderLabel()
     *
     * @return string
     */
    public function renderLabel()
    {
        return '<label>' . $this->getLabel() . '</label>';
    }
    
    public function setValue($value)
    {
        parent::setValue($value);
        
        $this->addAttribute('value', $value);
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $this->addAttribute('type', 'hidden');
        $jsParamsArray = array(
            'cmpId' => $this->getName(),
            'itemsCount' => MATCHMAKING_BOL_Service::MAX_COEFFICIENT,
            'id' => $this->getId(),
            'checkedCoefficient' => $this->getValue()
        );
        OW::getDocument()->addOnloadScript("var ".$this->getName()." = new MatchmakingCoefficient(" . json_encode($jsParamsArray) . "); ".$this->getName().".init();");
        
        $renderedString = UTIL_HtmlTag::generateTag('input', $this->attributes);
        $renderedString .= '<div id="' . $this->getName() . '">';
        $renderedString .= '<div class="coefficients_cont clearfix">';

        for ( $i = 1; $i <= $this->count; $i++ )
        {
            $renderedString .= '<a href="javascript://" class="coefficient_item" id="' . $this->getName() . '_item_' . $i . '">&nbsp;</a>';
        }
        $renderedString .= '</div></div>';

        return $renderedString;
    }
}


/**
 * Coefficient Required validator.
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.matchmaking.classes
 * @since 1.0
 */
class CoefficientValidator extends RequiredValidator
{
    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( is_array($value) )
        {
            if ( sizeof($value) === 0 )
            {
                return false;
            }
        }
        else if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return false;
        }
        else if ($value == "0")
        {
            return false;
        }

        return true;
    }

    /**
     * @see OW_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
                if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
                else if ( value == '0' ){ throw " . json_encode($this->getError()) . "; }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}
