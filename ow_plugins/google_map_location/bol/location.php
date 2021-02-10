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
 * @package ow_plugins.google_maps_location.bol
 * @since 1.0
 */
class GOOGLELOCATION_BOL_Location extends OW_Entity {

    public $entityId;
    /**
     * @var string
     */  
    public $entityType = 'user';
    /**
     * @var string
     */
    public $countryCode;
    /**
     * @var int
     */
    public $address;
    /**
     * @var int
     */
    public $lat = 0;
    /**
     * @var int
     */
    public $lng = 0;
    /**
     * @var int
     */
    public $northEastLat = 0;
    /**
     * @var int
     */
    public $northEastLng = 0;
    /**
     * @var int
     */
    public $southWestLat = 0;
    /**
     * @var int
     */
    public $southWestLng = 0;
    /**
     * @var string
     */
    public $json = '';
}
