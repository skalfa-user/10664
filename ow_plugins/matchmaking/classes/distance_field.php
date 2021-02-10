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

class MATCHMAKING_CLASS_DistanceField extends TextField
{
    public function  __construct( $name )
    {
        parent::__construct($name);
        
        if ( OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            $validator = new GOOGLELOCATION_CLASS_DistanceValidator();
            $this->addValidator($validator);
        }
        
        //$this->addAttribute('class', 'ow_matchmaking_distance_field');
    }
    
    public function renderInput( $params = null )
    {
        if ( $this->getValue() !== null )
        {
            $this->addAttribute('value', str_replace('"', '&quot;', $this->value));
        }
        else if ( $this->getHasInvitation() )
        {
            $this->addAttribute('value', $this->invitation);
            $this->addAttribute('class', 'invitation');
        }
        
        $html = '<span class="ow_matchmaking_distance_field">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '</span>';

        $locationHtml = "";

        if ( OW::getPluginManager()->isPluginActive('googlelocation') && OW::getUser()->isAuthenticated() )
        {
            $userId = OW::getUser()->getId();
            $location =  GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserId($userId);
            $url = BOL_UserService::getInstance()->getUserUrlForUsername(OW::getUser()->getUserObject()->username);

            if ( !empty($location) && !empty($location->address) )
            {
                $locationHtml = "<span class='ow_matchmaking_location'><a href='{$url}'>{$location->address}</a></span>";
            }
            else
            {
                $locationHtml = "<span class='ow_matchmaking_location'><a href='{$url}'>".OW::getLanguage()->text("matchmaking","location")."</a></span>";
            }
        }

        if ( OW::getConfig()->getValue('googlelocation', 'distance_units') == GOOGLELOCATION_BOL_LocationService::DISTANCE_UNITS_MILES )
        {
            $html .= '<span class="ow_matchmaking_distance_miles_from" >'.OW::getLanguage()->text('googlelocation', 'miles_from').'</span>'.$locationHtml;
        }
        else 
        {
            $html .= '<span class="ow_matchmaking_distance_miles_from" >'.OW::getLanguage()->text('googlelocation', 'kms_from').'</span>'.$locationHtml;
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('matchmaking')->getStaticCssUrl() . 'matchmaking.css');
        
        return $html;
    }
}

