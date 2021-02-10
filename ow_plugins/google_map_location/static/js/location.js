/**
 * Copyright (c) 2013-2015, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_map_location.static.js
 * @since 1.0
 */
"use strict";
window.OW_GoogleMapLocation = function ($)
{
    
    var location = function (fieldName, addressFieldId, mapElementId)
    {
        this.map;
        this.marker;
        this.mapElement = $("#" + mapElementId);
        this.bounds;

        this.fieldId = addressFieldId;
        this.mapElementId = mapElementId;
        this.zoom = 9;

        this.countryRestriction = '';
        this.removeValue;
        this.region;
        this.customMarkerIcon;
        this.fieldName = fieldName;
        
        this.latitudeField;
        this.longitudeField;
        this.northEastLat;
        this.northEastLng;
        this.southWestLat;
        this.southWestLng;
        this.addressField;
        this.jsonField;
        this.removeValue;
        this.dispalyMap = mapElementId;
    }

    location.prototype = {
        initialize: function(params) {

            var self = this;
            var lat = params['lat'];
            var lng = params['lng'];
            var northEastLat = params['northEastLat'];
            var northEastLng = params['northEastLng'];
            var southWestLat = params['southWestLat'];
            var southWestLng = params['southWestLng'];

            if ( params['customMarkerIcon'] )
            {
                this.customMarkerIcon = params['customMarkerIcon'];
            }

            this.region = params['region'];

            if ( this.dispalyMap ) {
                this.map = new window.GOOGLELOCATION.Map(this.mapElementId);
                this.map.initialize();
                this.map.disableDefaultUI(true);
                this.map.disableInput(true);
                this.map.disablePanning(true);
                this.map.disableZooming(true);

                if (lat || lng) {
                    this.mapElement.show();
                    this.map.setCenter(lat, lng);

                    if ((northEastLat || northEastLng) && (southWestLat || southWestLng)) {
                        this.map.fitBounds([southWestLat, southWestLng, northEastLat, northEastLng]);
                    }

                    this.map.addPoint(lat, lng, null, null, null, this.customMarkerIcon);
                    this.map.createMarkerCluster();
                    this.map.resize();
                    this.map.resetLastBounds();
                }
            }

            this.countryRestriction = params['countryRestriction'];

            $(function () {
                $("#" + self.fieldId + "_delete_icon").click(function () {
                    self.deleteValue.bind(self)();
                });
                
                self.latitudeField = $('input[name="' + self.fieldName + '[latitude]"]');
                self.longitudeField = $('input[name="' + self.fieldName + '[longitude]"]');
                self.northEastLat = $('input[name="' + self.fieldName + '[northEastLat]"]');
                self.northEastLng = $('input[name="' + self.fieldName + '[northEastLng]"]');
                self.southWestLat = $('input[name="' + self.fieldName + '[southWestLat]"]');
                self.southWestLng = $('input[name="' + self.fieldName + '[southWestLng]"]');
                self.addressField = $('input[name="' + self.fieldName + '[address]"]');
                self.jsonField = $('input[name="' + self.fieldName + '[json]"]');
                self.removeValue = $('input[name="' + self.fieldName + '[remove]"]');

                OW_GoogleMapLocationAutocomplete(self.fieldId, GOOGLELOCATION.AutocompleteParams(self));
            });
        },
        
        isValid: function() {
            if( this.jsonField.val() || this.removeValue.val() )
            {
                return true;
            }
            
            return false;
        },
        
        deleteValue: function(item)
        {
            this.hideDeleteIcon();

            $('#' + this.fieldId).val('');
            this.addressField.val('');
            this.longitudeField.val('');
            this.latitudeField.val('');
            this.northEastLat.val('');
            this.northEastLng.val('');
            this.southWestLat.val('');
            this.southWestLng.val('');
            this.removeValue.val(true);

            this.jsonField.val('');
        },
        
        hideDeleteIcon: function (item)
        {
            if ( this.dispalyMap )
            {
                $("#" + this.mapElementId).hide();
            }
            
            $("#" + this.fieldId + "_icon").show();
            $("#" + this.fieldId + "_delete_icon").hide();
        },

        showDeleteIcon: function (item)
        {
            if ( this.dispalyMap )
            {
                $("#" + this.mapElementId).show();
            }
            
            $("#" + this.fieldId + "_icon").hide();
            $("#" + this.fieldId + "_delete_icon").css("display", "inline");
        }

    };

    return location;
}(locationJquey)