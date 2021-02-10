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

abstract class GOOGLELOCATION_CMP_Map extends OW_Component implements GOOGLELOCATION_CLASS_MapComponentInterface
{
    protected  $name;
    protected  $points = array();
    protected  $centerLatitude;
    protected  $centerLonditude;
    protected  $attributes = array();
    protected  $options = array();

    protected  $northEastLat;
    protected  $northEastLng;
    protected  $southWestLat;
    protected  $southWestLng;
    protected  $isSetBounds = false;
    protected  $setAutoBounds = false;
    
    protected  $boxLabel = null;
    protected  $boxIcon = "";
    protected  $boxClass = "";
    protected  $displaySearchInput = false;
    
    public function __construct( $params = array() )
    {
        $this->attributes['id'] = uniqid('map_'. rand(0,999999));
        $this->name = $this->attributes['id'];
        $this->setWidth('100%');
        $this->setheight('200px');
        
        if ( !empty($params['mapName']) )
        {
            $this->name = $params['mapName'];
        }

        if ( !empty($params['id']) )
        {
            $this->attributes['id'] = $params['id'];
        }
        
        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('googlelocation')->getCmpViewDir().'map.html');
    }

    abstract public function setZoom( $zoom );
    
    abstract public  function getZoom();
    
    abstract public function setMinZoom( $zoom );
    
    abstract public  function getMinZoom();
    
    abstract public function setMaxZoom( $zoom );
    
    abstract public  function getMaxZoom();
    
    abstract public function disableDefaultUI($value);
    
    abstract public function disableInput($value);
    
    abstract public function disablePanning($value);
    
    abstract public function disableZooming($value);
    
    abstract public function initialize();
    
    public function setWidth( $width )
    {
        $this->width = $width;
    }

    public function setHeight( $height )
    {
        $this->height = $height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMapName()
    {
        return $this->name;
    }


    public function getCenter()
    {
        return array( 'lat' => $this->centerLatitude,
                      'lon' => $this->centerLonditude );
    }

    public function setMapName( $name )
    {
        $this->name = $name;
    }

    public function setCenter( $lat, $lon )
    {
        $this->centerLatitude = (float)$lat;
        $this->centerLonditude = (float)$lon;
    }

    public function getBounds()
    {
        return array(
            'northEastLat' => $this->northEastLat,
            'northEastLng' => $this->northEastLng,
            'southWestLat' => $this->southWestLat,
            'southWestLng' => $this->southWestLng
        );
    }

    public function getMapProvider()
    {
        return 'google';
    }
    
    public function setBounds( $swlat, $swlng, $nelat, $nelng )
    {
        $this->northEastLat = (float)$nelat;
        $this->northEastLng = (float)$nelng;
        $this->southWestLat = (float)$swlat;
        $this->southWestLng = (float)$swlng;
        $this->isSetBounds = true;
    }

    public function setMapOption( $key, $value )
    {
        $this->options[$key] = (string)$value;
    }

    public function getMapOption( $key )
    {
        if (  isset($this->options[$key]) )
        {
            return $this->options[$key];
        }

        return null;
    }

    public function setMapOptions( array $options )
    {
        if ( !empty($options) && is_array($options) )
        {
            $this->options = array_merge( $this->options, $options );
        }
    }

    public function getMapOptions()
    {
        return $this->options;
    }

    public function addPoint( $location, $title = '', $windowContent = '', $isOpen = false )
    {
        if ( !empty($location) )
        {
            $this->points[] = array(
                'location' => $location,
                'title' => UTIL_HtmlTag::stripJs($title),
                'content' => UTIL_HtmlTag::stripJs($windowContent),
                'isOpen' => (boolean)$isOpen,
                'icon' => GOOGLELOCATION_BOL_LocationService::getInstance()->getDefaultMarkerIcon() );
        }
    }

    public function addAttribute( $name, $value )
    {
        if ( !empty($name) )
        {
            $this->attributes[$name] = $value;
        }
    }

    public function getAttribute( $name )
    {
        return !empty($this->attributes[$name]) ? $this->attributes[$name] : null;
    }
    
    public function displaySearchInput( $value )
    {
        $this->displaySearchInput = $value;
    }

    public function onBeforeRender()
    {
        if ( empty($this->attributes['class']) )
        {
            $this->attributes['class'] = 'ow_googlemap_map_cmp';
        }
        else
        {
            $this->attributes['class'] .= ' ow_googlemap_map_cmp';
        }

        parent::onBeforeRender();
        $this->initialize();
        $this->assign('boxLabel', $this->boxLabel);
        $this->assign('boxIcon', $this->boxIcon);
        $this->assign('boxClass', $this->boxClass);
    }
    
    public function setBox( $label, $icon = null, $class = null )
    {
        $this->boxLabel = $label;
        $this->boxIcon = $icon;
        $this->boxClass = $class;
    }

    public function setAutoBounds( $value )
    {
        $this->setAutoBounds = (boolean) $value;
    }
}