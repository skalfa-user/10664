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
 * @package ow_plugins.google_maps_location.components
 * @since 1.0
 */

class GOOGLELOCATION_BING_CMP_Map extends GOOGLELOCATION_CMP_Map
{    
    
    public function initialize()
    {
        $points = "";
        $bounds = " var bounds; ";
        $count = 0;
        $pointList = array();
        
        foreach ( $this->points as $point )
        {
            $points .= " window.map[".(json_encode($this->name))."].addPoint(".((float)$point['location']['lat']).", ".((float)$point['location']['lng']).", ".  json_encode($point['title']).", ".json_encode($point['content']).", ".json_encode($point['isOpen']).", ".json_encode($point['icon'])." ); \n";
            
            if ( $this->setAutoBounds || !$this->isSetBounds )
            {
                array_push($pointList, " new Microsoft.Maps.Location(".(float)$point['location']['southWestLat'].",".(float)$point['location']['southWestLng'].") ");
                $count++;
            }
            
        }

        if( $count > 0 )
        {
            $bounds .= " bounds = new Microsoft.Maps.LocationRect.fromLocations([".  implode(",", $pointList)."]); ";
            $bounds .= "
                        window.map[".(json_encode($this->name))."].fitBounds([bounds.getSoutheast().latitude, bounds.getNorthwest().longitude, bounds.getNorthwest().latitude, bounds.getSoutheast().longitude]);
                     "; 
        }
        
        if( $this->isSetBounds )
        {
            $boundsList = array($this->southWestLat, $this->southWestLng, $this->northEastLat, $this->northEastLng);
            $bounds = "
                window.map[".(json_encode($this->name))."].fitBounds(".  json_encode($boundsList)."); ";
        }

        $mapOptions = $this->options;
        if ( empty($mapOptions['minZoom']) )
        {
            $mapOptions['minZoom'] = 2;
        }

        unset($mapOptions['zoom']);

        if ( isset($mapOptions['center']) )
        {
            unset($mapOptions['center']);
        }

        if ( isset($mapOptions['mapTypeId']) )
        {
            unset($mapOptions['mapTypeId']);
        }
        
        $displaySearchInput = "";
        if( $this->displaySearchInput )
        {
            $displaySearchInput =" window.map[".(json_encode($this->name))."].displaySearchInput(); ";
        }
        
        $script = "GOOGLELOCATION_INIT_SCOPE.push(function() {
            var latlng = new Microsoft.Maps.Location(".((float)$this->centerLatitude).", ".((float)$this->centerLonditude).");
    
            var options = ".json_encode($mapOptions).";
    
            window.map[".(json_encode($this->name))."] = new window.GOOGLELOCATION.Map(".json_encode($this->attributes['id']).");
            window.map[".(json_encode($this->name))."].initialize(options);
            
            {$displaySearchInput}
    
            {$bounds}
            
            {$points}
                
            window.map[".(json_encode($this->name))."].createMarkerCluster();
         });";
        
        OW::getDocument()->addOnloadScript($script);

        $this->attributes['style'] = (!empty($this->attributes['style']) ? $this->attributes['style'] : "") . "width:".$this->width.";height:".$this->height.";";
        $this->attributes['style'] .= " position:relative; ";
        $tag = UTIL_HtmlTag::generateTag('div', $this->attributes, true);

        $this->assign('map', $tag);
    }

    public function getZoom()
    {
        return $this->options['zoom'];
    }

    public function setZoom($zoom)
    {
        $this->options['zoom'] = $zoom;
    }
    
    public function setMaxZoom($zoom)
    {
        $this->options['maxZoom'] = $zoom;
    }

    public function setMinZoom($zoom)
    {
        $this->options['minZoom'] = $zoom;
    }
    
    public function getMaxZoom()
    {
        return !empty($this->options['maxZoom']) ? $this->options['maxZoom'] : null;
    }

    public function getMinZoom()
    {
        return !empty($this->options['minZoom']) ? $this->options['minZoom'] : null;
    }
    
    public function disableDefaultUI($value)
    {
        $this->options['showDashboard'] = !$value;
        $this->options['showMapTypeSelector'] = !$value;
        $this->options['showScalebar'] = !$value;
    }
    
    public function disableInput($value)
    {   
        $this->options['disableKeyboardInput'] = $value;
        $this->options['disableMouseInput'] = $value;
        $this->options['disableTouchInput'] = $value;
        $this->options['disableUserInput'] = $value;
    }

    public function disablePanning($value)
    {
        $this->options['disablePanning'] = $value;
    }

    public function disableZooming($value)
    {
        $this->options['disableZooming'] = $value;
    }
}