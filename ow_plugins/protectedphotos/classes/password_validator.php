<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.protected_photos.classes
 * @since 1.8.1
 */
class PROTECTEDPHOTOS_CLASS_PasswordValidator extends OW_Validator
{
    private $formName;
    private $privacyInputName;

    public function __construct( $formName, $privacyInputName )
    {
        $this->formName = $formName;
        $this->privacyInputName = $privacyInputName;
        $this->errorMessage = OW::getLanguage()->text('base', 'form_validator_required_error_message');
    }

    public function isValid( $value )
    {
        if ( !empty($_POST[$this->privacyInputName]) && trim($_POST[$this->privacyInputName]) === PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD )
        {
            return mb_strlen(trim($value)) !== 0;
        }

        return true;
    }

    public function getJsValidator()
    {
        return UTIL_JsGenerator::composeJsString('{
        	validate: function( value ){
        	    if ( owForms[{$formName}].getElement({$privacyInputName}).getValue() === {$privacyPassword} )
        	    {
        	        if ( value.trim().length === 0 )
        	        {
        	            throw this.getErrorMessage();
        	        }
        	    }
            },
        	getErrorMessage: function(){return {$errorMessage}}
        }', array(
            'formName' => $this->formName,
            'privacyInputName' => $this->privacyInputName,
            'privacyPassword' => PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD,
            'errorMessage' => $this->errorMessage
        ));
    }
}
