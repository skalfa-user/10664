<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.classes
 * @since 1.0
 */

class GOOGLELOCATION_CLASS_LocationSearch extends GOOGLELOCATION_CLASS_Location
{
    public function  __construct( $name )
    {
        parent::__construct($name);
        $this->addAttribute('class', 'ow_googlelocation_search_location');
        
        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));
        
        $validator = new GOOGLELOCATION_CLASS_DistanceValidator();
        $this->addValidator($validator);
    }
    
    public function renderInput( $params = null )
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'location.js', "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);

        /* OW::getDocument()->addOnloadScript(' $( document ).ready( function(){ window.googlemap_location_search = new OW_GoogleMapLocation( ' . json_encode($this->getName()) . ','
                . ' ' . json_encode($this->getId()) . ', '.  json_encode(GOOGLELOCATION_BOL_LocationService::getInstance()->getCountryRestriction()).' );
                                             window.googlemap_location_search.initialize(); }); '); */

        $params = array(
            'region' => $this->region,
            'countryRestriction' => GOOGLELOCATION_BOL_LocationService::getInstance()->getCountryRestriction()
        );

        OW::getDocument()->addOnloadScript(' GOOGLELOCATION_INIT_SCOPE.push( function(){ window.googlemap_location_search = new OW_GoogleMapLocation( ' . json_encode($this->getName()) . ', ' . json_encode($this->getId()) . ', null );
                                             window.googlemap_location_search.initialize(' . json_encode($params) . '); }); ');
        
        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[address]',
            'value' => !empty($this->value['address']) ? $this->escapeValue($this->value['address']) : '');

        $html = UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[latitude]',
            'value' => !empty($this->value['latitude']) ? $this->escapeValue($this->value['latitude']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[longitude]',
            'value' => !empty($this->value['longitude']) ? $this->escapeValue($this->value['longitude']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[northEastLat]',
            'value' => !empty($this->value['latitude']) ? $this->escapeValue($this->value['northEastLat']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[northEastLng]',
            'value' => !empty($this->value['longitude']) ? $this->escapeValue($this->value['northEastLng']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[southWestLat]',
            'value' => !empty($this->value['latitude']) ? $this->escapeValue($this->value['southWestLat']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[southWestLng]',
            'value' => !empty($this->value['longitude']) ? $this->escapeValue($this->value['southWestLng']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[json]',
            'value' => !empty($this->value['json']) ? $this->escapeValue($this->value['json']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'text',
            'name' => $this->getName() . '[distance]',
            'class' => 'ow_googlelocation_search_distance',
            'value' => !empty($this->value['distance']) ? $this->escapeValue($this->value['distance']) : '');

        $html .= '<span>' . UTIL_HtmlTag::generateTag('input', $attribute) . '</span>';

        if ( OW::getConfig()->getValue('googlelocation', 'distance_units') == GOOGLELOCATION_BOL_LocationService::DISTANCE_UNITS_MILES )
        {
            $html .= '<span class="ow_googlelocation_search_miles_from" >'.OW::getLanguage()->text('googlelocation', 'miles_from').'</span>';
        }
        else 
        {
            $html .= '<span class="ow_googlelocation_search_miles_from" >'.OW::getLanguage()->text('googlelocation', 'kms_from').'</span>';
        }

        $attribute = $this->attributes;
        unset($attribute['name']);
        $attribute['value'] = !empty($this->value['address'])  ? $this->value['address'] : '';
        $attribute['class'] .= ' ow_left ow_googlelocation_location_search_input';

        if ( empty($attribute['value']) && $this->hasInvitation )
        {
            $attribute['value'] = $this->invitation;
            $attribute['class'] .= ' invitation';
        }

        $html .= '<div class="googlelocation_address_div">'.
                    UTIL_HtmlTag::generateTag('input', $attribute).
                    '<div class="googlelocation_address_icon_div">
                        <span id='.json_encode($this->getId().'_icon').' style="'.(!empty($this->value['json']) ? 'display:none': 'display:inline').'" class="ic_googlemap_pin googlelocation_address_icon"></span>
                        <div id='.json_encode($this->getId().'_delete_icon').'  style="'.(empty($this->value['json']) ? 'display:none': 'display:inline').'" class="ow_miniic_delete owm_ic_close_cont googlelocation_delete_icon"></div>
                    </div>
                 </div>';
        
        return $html;
    }

    public function setDistance($distance)
    {
        if ( !empty($distance) )
        {
            $this->value['distance'] = (int) $distance;
        }
    }

    public function getDistance()
    {
        return !empty($this->value['distance']) ? $this->value['distance'] : 0 ;
    }

    public function setRequired( $value = true )
    {
        if ( $value )
        {
            $this->addValidator(new LocationSearchRequireValidator());
        }
        else
        {
            foreach ( $this->validators as $key => $validator )
            {
                if ( $validator instanceof RequiredValidator )
                {
                    unset($this->validators[$key]);
                    break;
                }
            }
        }

        return $this;
    }
}

class LocationSearchRequireValidator extends RequiredValidator
{
    public function isValid( $value )
    {
        $isValid = false;

        if ( !empty($value['json']) )
        {
            $isValid = true;
        }

        return $isValid;
    }

    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
                if( !window.googlemap_location_search.isValid() ){ throw " . json_encode($this->getError()) . "; return;}
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}




