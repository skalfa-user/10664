<?php

class SKMOBILEAPP_CLASS_AppnsCertificateValidator extends RequiredValidator
{
    /**
     * Cert extension
     */
    const CERT_EXTENSION = 'pem';

    /**
     * Is required
     */
    private $isRequired = false;

    /**
     * Constructor
     */
    public function __construct( $isRequired = false )
    {
        parent::__construct();

        $this->isRequired = $isRequired;
    }

    /**
     * Is valid
     * 
     * @return boolean
     */
    public function isValid($value)
    {
        $extension = !empty($value['name']) 
            ? pathinfo($value['name'], PATHINFO_EXTENSION)
            : null;

        if ( (!$extension && !$this->isRequired) || $extension === self::CERT_EXTENSION ) 
        {
            return true;
        }

        return false;
    }

    /**
     * @see OW_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return '{
            validate : function( value ) {}
        }';

        return "{
            validate : function( value ){
                if (!value) throw " . json_encode($this->getError()) . "; return; }
        },
            getErrorMessage : function() { 
                return " . json_encode($this->getError()) . 
            " }
        }";
    }
}
