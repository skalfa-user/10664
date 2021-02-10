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

class GOOGLELOCATION_CMP_ProfileViewMap extends GOOGLELOCATION_CMP_Map
{
    public function  __construct( $location = array(), $params = null )
    {
        //$this->setHeight('200px');
        $this->setZoom(9);
        $this->setMapOptions(array(
            'disableDefaultUI' => "false",
            'draggable' => "false",
            'mapTypeControl' => "false",
            'overviewMapControl' => "false",
            'panControl' => "false",
            'rotateControl' => "false",
            'scaleControl' => "false",
            'scrollwheel' => "false",
            'streetViewControl' => "false",
            'zoomControl' => "false"));

        if ( !empty($location) )
        {
            //$this->setCenter($location['latitude'], $location['longitude']);
            $this->setBounds($location['southWestLat'], $location['southWestLng'], $location['northEastLat'], $location['northEastLng']);
            $this->addPoint($location, $location['address']);
        }        
        parent::__construct($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('googlelocation')->getCmpViewDir().'map.html');
    }
}