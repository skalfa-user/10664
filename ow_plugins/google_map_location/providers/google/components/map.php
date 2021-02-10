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

class GOOGLELOCATION_GOOGLE_CMP_Map extends GOOGLELOCATION_CMP_Map
{

    public function initialize()
    {
        $points = "";
        $bounds = " var bounds; ";
        $count = 0;

        foreach ( $this->points as $point )
        {
            $points .= " window.map[".(json_encode($this->name))."].addPoint(".((float)$point['location']['lat']).", ".((float)$point['location']['lng']).", ".  json_encode($point['title']).", ".json_encode($point['content']).", ".json_encode($point['isOpen']).", ".json_encode($point['icon'])." ); \n";

            if ( $this->setAutoBounds || !$this->isSetBounds )
            {
                $swLat = $this->latitudeFilter((float)$point['location']['southWestLat']);
                $neLat = $this->latitudeFilter((float)$point['location']['northEastLat']);

                $sw = " new google.maps.LatLng(".$swLat.",".(float)$point['location']['southWestLng'].") ";
                $ne = " new google.maps.LatLng(".$neLat.",".(float)$point['location']['northEastLng'].") ";

                $bound = " new google.maps.LatLngBounds( $sw , $ne ) ";

                if ( $count == 0 )
                {
                    $bounds .= "
                        bounds = new google.maps.LatLngBounds( $sw , $ne );
                     ";
                }
                else
                {
                    $bounds .= "
                        bounds.union( new google.maps.LatLngBounds( $sw , $ne ) );
                     ";
                }

                $count++;
            }

        }

        if( $count > 0 )
        {
            $bounds .= "
                        window.map[".(json_encode($this->name))."].fitBounds([bounds.getSouthWest().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lat(), bounds.getNorthEast().lng()]);
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

        $mapOptionsString = " {
                zoom: ".json_encode($mapOptions['zoom']).",
                minZoom:".json_encode($mapOptions['minZoom']).",
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP, ";

        unset($mapOptions['zoom']);

        if ( isset($mapOptions['center']) )
        {
            unset($mapOptions['center']);
        }

        if ( isset($mapOptions['mapTypeId']) )
        {
            unset($mapOptions['mapTypeId']);
        }

        foreach( $this->options as $key => $value )
        {
            if ( isset($value) )
            {
                $mapOptionsString .=  " $key: $value, \n";
            }
        }

        $mapOptionsString .= "}";

        $displaySearchInput = "";
        if( $this->displaySearchInput )
        {
            $displaySearchInput =" window.map[".(json_encode($this->name))."].displaySearchInput(); ";
        }

        $script = "
            if (!window.GOOGLELOCATION_INIT_SCOPE) {
             window.GOOGLELOCATION_INIT_SCOPE = [];
            }
            window.GOOGLELOCATION_INIT_SCOPE.push(function(){
            var latlng = new google.maps.LatLng(".((float)$this->centerLatitude).", ".((float)$this->centerLonditude).");

            var options = $mapOptionsString;

            window.map[".(json_encode($this->name))."] = new GOOGLELOCATION.Map(".json_encode($this->attributes['id']).");
            window.map[".(json_encode($this->name))."].initialize(options);
            
            {$displaySearchInput}

            {$bounds}
            
            {$points} 
                
            window.map[".(json_encode($this->name))."].createMarkerCluster();
                
           }); ";

        OW::getDocument()->addOnloadScript($script);

        $this->attributes['style'] = (!empty($this->attributes['style']) ? $this->attributes['style'] : "") . "width:".$this->width.";height:".$this->height.";";
        $tag = UTIL_HtmlTag::generateTag('div', $this->attributes, true);

        $this->assign('map', $tag);

        $bounds = OW::getSession()->get('googlemaps_search_bounds');
        if ( GOOGLELOCATION_BOL_LocationService::DEGUG_MODE && $bounds ) {
            printVar($bounds);
            $sw = " new google.maps.LatLng(".(float)$bounds['southWestLat'].",".(float)$bounds['southWestLng'].") ";
            $ne = " new google.maps.LatLng(".(float)$bounds['northEastLat'].",".(float)$bounds['northEastLng'].") ";

            $bound = " new google.maps.LatLngBounds( $sw , $ne ) ";


            $script = " 
              if (!window.GOOGLELOCATION_INIT_SCOPE) {
                 window.GOOGLELOCATION_INIT_SCOPE = [];
              }
              window.GOOGLELOCATION_INIT_SCOPE.push(function() {

              var marker = new google.maps.Marker({
                position: $sw,
                map: window.map[".(json_encode($this->name))."].map,
                title: 'sw!'
              });

              var marker = new google.maps.Marker({
                position: $ne,
                map: window.map[".(json_encode($this->name))."].map,
                title: 'ne!'
              });

                var rectangle = new google.maps.Rectangle( {
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: window.map[".(json_encode($this->name))."].map,
                    bounds: $bound
                } );
            }); ";
            OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString($script));
        }
    }

    protected function latitudeFilter( $lat )
    {
        if ( $lat >= 85 )
        {
            $lat = 85;
        }

        if ( $lat <= -85 )
        {
            $lat = -85;
        }

        return $lat;
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
        $this->options['disableDefaultUI'] = $value ? "true" : "false";
        $this->options['mapTypeControl'] = !$value ? "true" : "false";
        $this->options['overviewMapControl'] = !$value ? "true" : "false";
        $this->options['scaleControl'] = !$value ? "true" : "false";
        $this->options['streetViewControl'] = !$value ? "true" : "false";
    }

    public function disableInput($value)
    {
        $this->options['draggable'] = !$value ? "true" : "false";
        $this->options['scrollwheel'] = !$value ? "true" : "false";
        $this->options['scaleControl'] = !$value ? "true" : "false";
    }

    public function disablePanning($value)
    {
        $this->options['panControl'] = !$value ? "true" : "false";
    }

    public function disableZooming($value)
    {
        $this->options['zoomControl'] = !$value ? "true" : "false";
    }
}
