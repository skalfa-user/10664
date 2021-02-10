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
class GOOGLELOCATION_CLASS_Location extends InvitationFormElement
{
    protected $region;
    protected $displayMap = true;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->region = GOOGLELOCATION_BOL_LocationService::getInstance()->getLanguageCode();

        $this->addAttribute('type', 'text');
        $this->addAttribute('id', 'google_location_' . uniqid(rand(0, 999999)));
        $this->setHasInvitation(false);
        $this->addAttribute('class', '');
    }

    /**
     * Should map be displayed?
     *
     * @param boolean $value
     * @return $this
     */
    public function setDisplayMap( $value )
    {
        $this->displayMap = $value;

        return $this;
    }
    
    /**
     * Sets form element value.
     *
     * @param mixed $value
     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( !empty($value) && is_array($value) && ( !empty($value['json']) || !empty($value['remove']) ) )
        {
            $this->value = $value;
        }

        return $this;
    }

    private function setListValue( $value )
    {
        if ( !empty($value) && !empty($value['json']) )
        {
            $this->value = $value;
        }

        return $this;
    }

    public function setRequired( $value = true )
    {
        if ( $value )
        {
            $this->addValidator(new LocationRequireValidator());
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

    /**
     * Sets form element value.
     *
     * @param mixed $value
     * @return FormElement
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets form element value.
     *
     * @param mixed $value
     * @return FormElement
     */
    public function getListValue()
    {
        return $this->value;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'location.js', "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);

        $name = json_encode($this->getName());
        $id = json_encode($this->getId());
        $lat = !empty($this->value['latitude']) ? (float) $this->value['latitude'] : 0;
        $lon = !empty($this->value['longitude']) ? (float) $this->value['longitude'] : 0;
        $northEastLat = !empty($this->value['northEastLat']) ? (float) $this->value['northEastLat'] : 0;
        $northEastLng = !empty($this->value['northEastLng']) ? (float) $this->value['northEastLng'] : 0;
        $southWestLat = !empty($this->value['southWestLat']) ? (float) $this->value['southWestLat'] : 0;
        $southWestLng = !empty($this->value['southWestLng']) ? (float) $this->value['southWestLng'] : 0;

        $params = array(
            'lat' => $lat,
            'lng' => $lon,
            'northEastLat' => $northEastLat,
            'northEastLng' => $northEastLng,
            'southWestLat' => $southWestLat,
            'southWestLng' => $southWestLng,
            'region' => $this->region,
            'countryRestriction' => GOOGLELOCATION_BOL_LocationService::getInstance()->getCountryRestriction(),
            'customMarkerIcon' => GOOGLELOCATION_BOL_LocationService::getInstance()->getDefaultMarkerIcon()
        );
        
        $mapElementId = $this->displayMap ? $this->getName() . '_map' : '';

        OW::getDocument()->addOnloadScript(' GOOGLELOCATION_INIT_SCOPE.push( function(){ window.googlemap_location = new window.OW_GoogleMapLocation( ' . json_encode($this->getName()) . ', ' . json_encode($this->getId()) . ', ' . json_encode($mapElementId) . ' );
                                             window.googlemap_location.initialize(' . json_encode($params) . '); }); ');
        
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
            'value' => !empty($this->value['northEastLat']) ? $this->escapeValue($this->value['northEastLat']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[northEastLng]',
            'value' => !empty($this->value['northEastLng']) ? $this->escapeValue($this->value['northEastLng']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[southWestLat]',
            'value' => !empty($this->value['southWestLat']) ? $this->escapeValue($this->value['southWestLat']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[southWestLng]',
            'value' => !empty($this->value['southWestLng']) ? $this->escapeValue($this->value['southWestLng']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[json]',
            'value' => !empty($this->value['json']) ? $this->escapeValue($this->value['json']) : '');

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);
        
        $attribute = array(
            'type' => 'hidden',
            'name' => $this->getName() . '[remove]',
            'value' => !empty($this->value['remove']) ? $this->escapeValue($this->value['remove']) : 0);

        $html .= UTIL_HtmlTag::generateTag('input', $attribute);

        $attribute = $this->attributes;
        unset($attribute['name']);
        $attribute['value'] = !empty($this->value['address'])  ? $this->value['address'] : '';
        $attribute['class'] .= ' ow_left ow_googlelocation_location_input';

        if ( empty($attribute['value']) && $this->hasInvitation )
        {
            $attribute['value'] = $this->invitation;
            $attribute['class'] .= ' invitation';
        }

        $html .= '<div class="googlelocation_form_element_div clearfix">'.
                    UTIL_HtmlTag::generateTag('input', $attribute).
                    '<div class="googlelocation_address_icon_div">
                        <span id='.json_encode($this->getId().'_icon').' style="'.(!empty($this->value['json']) ? 'display:none': 'display:inline').'" class="ic_googlemap_pin googlelocation_address_icon"></span>
                        <div id='.json_encode($this->getId().'_delete_icon').'  style="'.(empty($this->value['json']) ? 'display:none': 'display:inline').'" class="ow_miniic_delete owm_ic_close_cont googlelocation_delete_icon"></div>
                    </div>
                 </div>';

        $html .= '<div id="' . $this->getName() . '_map" style="margin-top:10px;width:90%;height:200px;display:none;position:relative;">
        </div>';

        return $html;
    }

    protected function escapeValue( $string )
    {
        return htmlspecialchars($string);
    }

   public function getElementJs()
   {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        if ( ((int) OW::getConfig()->getValue('base', 'soft_version')) >= 10600 ) {
            $js .= $this->generateValidatorAndFilterJsCode("formElement");
        }

        $js .= "
            formElement.invitationString = ".  json_encode($this->invitation).";
            
            $(\"input#".$this->getId()."\" ).bind('focus.invitation', {formElement:formElement},
                    function(e){
                        el = $(this);
                        el.removeClass('invitation');
                        if( el.val() == '' || el.val() == e.data.formElement.invitationString){
                            el.val('');
                            //hotfix for media panel
                            if( 'htmlarea' in el.get(0) ){
                                el.unbind('focus.invitation').unbind('blur.invitation');
                                el.get(0).htmlarea();
                                el.get(0).htmlareaFocus();
                            }
                        }
                        else{
                            el.unbind('focus.invitation').unbind('blur.invitation');
                        }
                    }
                );

			formElement.getValue = function() {

				var \$inputs = $(\"input[name^='".$this->getName()."']\");
                
                var values = {};

		        $.each( \$inputs,
		            function(index, data){
		                if( $(this).val() != '' )
		                {
		                    values[$(this).attr('name').replace(/".$this->getName()."\[(\w+)\]/, '$1')] = $(this).val();
		                }
		            }
		        );

                if( values.address == formElement.invitationString )
                {
                    values.address = '';
                }

		        return values;
			};

			formElement.resetValue = function() {

		        var \$inputs = $(\"input[name^='".$this->getName()."']\");

		        $.each( \$inputs,
		            function(index, data){
		                $(this).val('');
		            }
		        );
			};

			formElement.setValue = function(value){	};
		";

       return $js;
    }
}

class LocationRequireValidator extends RequiredValidator
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
                if( !window.googlemap_location.isValid() ){ throw " . json_encode($this->getError()) . "; return;}
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}
