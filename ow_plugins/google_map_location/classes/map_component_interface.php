<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface GOOGLELOCATION_CLASS_MapComponentInterface
{    
    public function setWidth( $width );

    public function setHeight( $height );
    
    public function getWidth();
    
    public function getHeight();

    public function getMapName();

    public function getCenter();

    public function setMapName( $name );

    public function setZoom( $zoom );
    
    public function getZoom();
    
    public function setMinZoom( $zoom );
    
    public  function getMinZoom();

    public function setCenter( $lat, $lon );

    public function getBounds();
    
    public function getMapProvider();
    
    public function setBounds( $swlat, $swlng, $nelat, $nelng );

    public function setMapOption( $key, $value );

    public function getMapOption( $key );

    public function setMapOptions( array $options );

    public function getMapOptions();

    public function addPoint( $location, $title = '', $windowContent = '', $isOpen = false );

    public function addAttribute( $name, $value );

    public function getAttribute( $name );
    
    public function displaySearchInput( $value );
    
    public function setBox( $label, $icon = null, $class = null );

    public function setAutoBounds( $value );
    
    public function disableDefaultUI($value);
    
    public function disableInput($value);
    
    public function disablePanning($value);
    
    public function disableZooming($value);
}