<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 11/3/15
 * Time: 3:30 PM
 */

class GOOGLELOCATION_CLASS_DistanceValidator extends OW_Validator
{
    const MAX_DISTANCE = 999;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $distanceUnits = "";

        if ( OW::getConfig()->getValue('googlelocation', 'distance_units') == GOOGLELOCATION_BOL_LocationService::DISTANCE_UNITS_MILES )
        {
            $distanceUnits = OW::getLanguage()->text('googlelocation', 'miles');
        }
        else
        {
            $distanceUnits = OW::getLanguage()->text('googlelocation', 'kms');
        }

        $errorMessage = OW::getLanguage()->text('googlelocation', 'distance_validator_error', array( 'distance' => self::MAX_DISTANCE, 'units' => $distanceUnits ));

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Distance Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid( $value )
    {
        if ( empty($value) ) {
            return true;
        }

        if ( is_array($value) && empty($value['distance']) ) {
            return true;
        }

        if ( is_array($value) && $value['distance'] > self::MAX_DISTANCE )
        {
            return false;
        }

        if ( is_numeric($value) && $value > self::MAX_DISTANCE )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        return "{
            validate : function( value ){
            if( value && value.distance && value.distance > " .self::MAX_DISTANCE . " ){ throw " . json_encode($this->getError()) . "; return;}
        },
            getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}