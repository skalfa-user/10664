/**
 * Copyright (c) 2013-2015, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag.static.js
 * @since 1.0
 */
"use strict";
var OW_GoogleMapLocationSearch = function($, google)
{
    return function(fieldName, addressFieldId, restrictedCountry)
    {
        var self = this;

        var geocoder;
        var map;
        var marker;
        var bounds;
        
        var latitudeField;
        var longitudeField;
        var northEastLat;
        var northEastLng;
        var southWestLat;
        var southWestLng;
        var addressField;
        var jsonField;
        
        var country = restrictedCountry;

        var fieldId = addressFieldId;
        this.isValidValue = false;

        this.initialize = function()
        {        
            geocoder = new google.maps.Geocoder();

            $(function() {
                latitudeField = $('input[name="'+fieldName+'[latitude]"]');
                longitudeField = $('input[name="'+fieldName+'[longitude]"]');
                northEastLat = $('input[name="'+fieldName+'[northEastLat]"]');
                northEastLng = $('input[name="'+fieldName+'[northEastLng]"]');
                southWestLat = $('input[name="'+fieldName+'[southWestLat]"]');
                southWestLng = $('input[name="'+fieldName+'[southWestLng]"]');
                addressField = $('input[name="'+fieldName+'[address]"]');
                jsonField = $('input[name="'+fieldName+'[json]"]');

                if ( jsonField.val() )
                {
                    self.isValidValue = true;
                }

                $("#"+fieldId + "_delete_icon").click(function() { 
                    self.deleteValue();
                });

                OW_GoogleMapLocationAutocomplete(fieldId,{
                    delay: 250,

                    focus: function (event, ui) {
                        $(".ui-helper-hidden-accessible").hide();
                        event.preventDefault();
                    },

                    source: function(request, response) {

                        var icon= $('#'+fieldId + '_icon');
                        icon.removeClass('ic_googlemap_pin');
                        icon.addClass('ow_inprogress');

                        var geocoderParams = {
                            'address': request.term
                        };
                        
                        if (country)
                        {
                            geocoderParams.componentRestrictions = {country: country};
                        }

                        geocoder.geocode( geocoderParams, function(results, status) {

                            icon.removeClass('ow_inprogress');
                            icon.addClass('ic_googlemap_pin');

                            response($.map(results, function(item) {
                                return {
                                    label:  item.formatted_address,
                                    latitude: item.geometry.location.lat(),
                                    longitude: item.geometry.location.lng(),
                                    value: item.formatted_address,
                                    result: item
                                }
                            }));
                        })
                    },

                    select: function(event, ui) {
                        
                        self.showDeleteIcon();
                        
                        var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);

                        addressField.val(ui.item.value);
                        longitudeField.val(ui.item.longitude);
                        latitudeField.val(ui.item.latitude);
                        northEastLat.val(ui.item.result.geometry.viewport.getNorthEast().lat())
                        northEastLng.val(ui.item.result.geometry.viewport.getNorthEast().lng())
                        southWestLat.val(ui.item.result.geometry.viewport.getSouthWest().lat())
                        southWestLng.val(ui.item.result.geometry.viewport.getSouthWest().lng())

                        jsonField.val( JSON.stringify(ui.item.result).replace('"','\"'));
                        self.isValidValue = true;
                    }
                });
            });
        }
        
        this.deleteValue = function(item)
        {
            self.hideDeleteIcon();

            $('#'+fieldId).val('');
            addressField.val('');
            longitudeField.val('');
            latitudeField.val('');
            northEastLat.val('');
            northEastLng.val('');
            southWestLat.val('');
            southWestLng.val('');

            jsonField.val('');
            self.isValidValue = false;
        }
        
        this.showDeleteIcon = function(item)
        {
            $("#"+fieldId + "_icon").hide();
            $("#"+fieldId + "_delete_icon").css("display","inline");
        }

        this.hideDeleteIcon = function(item)
        {
            $("#"+fieldId + "_icon").show();
            $("#"+fieldId + "_delete_icon").hide();
        }
    }
} (locationJquey, google)