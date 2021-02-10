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
if (!window.GOOGLELOCATION_INIT_SCOPE) {
  window.GOOGLELOCATION_INIT_SCOPE = [];
}

var GOOGLELOCATION = {};

function googlemaplocation_api_loading_complete() {
  GOOGLELOCATION.Map = function ($, google) {
    var debugMode = false;

    var initialize = function (options) {
      var params = options;

      if (!params) {
        params = {};
      }

      var opt = $.extend({
        zoom: 2,
        minZoom: 2,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: true,
        draggable: false,
        mapTypeControl: false,
        overviewMapControl: false,
        panControl: false,
        rotateControl: false,
        scaleControl: false,
        scrollwheel: false,
        streetViewControl: false,
        zoomControl: false
      }, params);

      this.map = new google.maps.Map(document.getElementById(this.mapElementId), opt);

      //geocoder = new google.maps.Geocoder();
    }

    var disableDefaultUI = function ($value) {
      var options = {}

      options.disableDefaultUI = !$value;
      options.mapTypeControl = !$value;
      options.overviewMapControl = !$value;
      options.scaleControl = !$value;
      options.streetViewControl = !$value;
      this.map.setOptions(options);
    }

    var disableInput = function ($value) {
      var options = {}

      options.draggable = !$value;
      options.scrollwheel = !$value;
      options.scaleControl = !$value;
      this.map.setOptions(options);
    }

    var disablePanning = function ($value) {
      var options = {}

      options.panControl = !$value;
      this.map.setOptions(options);
    }

    var disableZooming = function ($value) {
      var options = {}

      options.zoomControl = !$value;
      this.map.setOptions(options);
    }

    var setCenter = function (lat, lon) {
      var latlng = new google.maps.LatLng(lat, lon);
      this.map.setCenter(latlng);
    }

    var setZoom = function (zoom) {
      this.map.setZoom(zoom);
    }

    var setOptions = function (options) {
      this.map.setOptions(options);
    }

    var resetLastBounds = function () {
      if (this.lastBounds) {
        this.fitBounds(this.lastBounds);
      }
    }

    var removeAllPoints = function () {
      var self = this;
      $.each(this.marker, (function (key, m) {
        if (m) {
          if (this.markerCluster) {
            this.markerCluster.removeMarker_(m);
          }
          m.setMap(null);
        }
      }).bind(self))
      this.marker = {};
      this.infowindow = {};
      this.infowindowState = {};
    }

    var addPoint = function (lat, lon, title, windowContent, isOpen, customMarkerIconUrl) {
      var self = this;
      var markerParams = {
        //map: map
        //draggable: false
        //optimized: true
      }

      if (customMarkerIconUrl) {
        markerParams.icon = customMarkerIconUrl;
      }

      var hash = lat + ' ' + lon;

      this.marker[lat + ' ' + lon] = new google.maps.Marker(markerParams);

      var latlng = new google.maps.LatLng(lat, lon);
      this.marker[lat + ' ' + lon].setPosition(latlng);

      if (title) {
        this.marker[lat + ' ' + lon].setTitle(title);
      }

      if (windowContent) {
        this.infowindow[lat + ' ' + lon] = new InfoBubble({
          content: windowContent,
          shadowStyle: 0,
          padding: 9,
          backgroundColor: '#fff',
          borderRadius: 4,
          arrowSize: 10,
          maxHeight: 350,
          borderWidth: '4px',
          borderColor: '#fff',
          disableAutoPan: false,
          hideCloseButton: false,
          arrowPosition: 25,
          arrowStyle: 0
        });

        //infowindow[lat + ' ' + lon].setContent(windowContent);

        this.infowindowState[lat + ' ' + lon] = false;

        if (isOpen) {
          this.infowindow[lat + ' ' + lon].open(this.map, this.marker[lat + ' ' + lon]);
          this.infowindowState[lat + ' ' + lon] = true;
        }

        google.maps.event.addListener(this.marker[lat + ' ' + lon], 'click', (function () {

          var infowindow = this.infowindow;
          var infowindowState = this.infowindowState;
          var map = this.map;

          if (infowindowState[lat + ' ' + lon]) {
            infowindow[lat + ' ' + lon].close();
            infowindowState[lat + ' ' + lon] = false;
          }
          else {
            infowindow[lat + ' ' + lon].open(map, this.marker[lat + ' ' + lon]);
            infowindowState[lat + ' ' + lon] = true;

            $.each(this.infowindow, function (key, value) {
              if (value) {
                if (key != lat + ' ' + lon) {
                  value.close();
                  infowindowState[key] = false;
                }
              }
            });
          }
        }).bind(self));

        google.maps.event.addListener(this.infowindow[lat + ' ' + lon], 'closeclick', (function () {
          if (this.infowindowState[lat + ' ' + lon]) {
            this.infowindow[lat + ' ' + lon].close();
            this.infowindowState[lat + ' ' + lon] = false;
          }
        }).bind(self));
      }
    }

    var resize = function () {
      google.maps.event.trigger(this.map, 'resize');
    }

    var createMarkerCluster = function () {
      var options = {
        imagePath: marckerClusterImagesUrl + '/m',
      };

      this.markerCluster = new MarkerClusterer(this.map, this.marker, options);
    }

    var displaySearchInput = function () {
      var self = this;
      var centerControlDiv = document.createElement('div');
      var input = $("<div class='googlelocation_map_search_input_icon'>" +
        "<span class='googlelocation_map_search_pin ic_googlelocation_map_search_pin'></span>" +
        "</div>" +
        "<input type='text' class='googlelocation_map_search_input googlelocation_map_search_input_hide' />");

      var searchDiv = $(centerControlDiv);

      searchDiv.addClass("googlelocation_map_search_input_div").append(input);
      this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv);

      var autocomplite = $(centerControlDiv).find("input.googlelocation_map_search_input");
      var icon_div = $(centerControlDiv).find(".googlelocation_map_search_input_icon");
      var icon_span = icon_div.find("span");

      var geocoder = new google.maps.Geocoder();

      var data = autocomplite.autocomplete({
        delay: 250,
        matchContains: true,

        source: function (request, response) {
          icon_span.removeClass('ic_googlelocation_map_search_pin');
          icon_span.addClass('ow_inprogress');

          var geocoderParams = {
            'address': request.term
          }

          geocoder.geocode(geocoderParams, function (results, status) {

            icon_span.removeClass('ow_inprogress');
            icon_span.addClass('ic_googlelocation_map_search_pin');

            response($.map(results.slice(0, 5), function (item) {
              return {
                label: item.formatted_address,
                value: item.formatted_address,
                item: item
              }
            }));
          })
        },
        select: function (event, ui) {
          self.map.fitBounds(ui.item.item.geometry.viewport);
          if (debugMode) {
            var rectangle = new google.maps.Rectangle({
              strokeColor: '#FF0000',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#FF0000',
              fillOpacity: 0.35,
              map: self.map,
              bounds: ui.item.item.geometry.bounds
            });
          }
        }
      }).data("ui-autocomplete");

      data._resizeMenu = function () {
        this.menu.element.outerWidth(autocomplite.outerWidth());
        $(this.menu.element).addClass("googlelocation_map_search_menu");
      };

      var func = data._renderItem;
      data._renderItem = function (ul, item) {
        var element = func(ul, item);
        element.find("a").prepend("<span class='ic_googlelocation_menu_item_pin'>");
        return element;
      };
    }


    var proxyObject = function (elementId) {
      this.geocoder;
      this.map;
      this.marker = {};
      this.infowindow = {};
      this.infowindowState = {};
      this.markerCluster = null;
      this.lastBounds = null;

      this.mapElementId = elementId;
    }

    proxyObject.prototype = {
      initialize: initialize,
      setCenter: setCenter,
      setZoom: setZoom,
      setOptions: setOptions,
      fitBounds: function (bounds) {
        if (!(bounds && $.isArray(bounds) && bounds.length == 4
          && bounds[0] != undefined && bounds[1] != undefined && bounds[2] != undefined && bounds[3] != undefined)) {
          return;
        }

        var sw = new google.maps.LatLng(bounds[0], bounds[1]);
        var ne = new google.maps.LatLng(bounds[2], bounds[3]);

        var boundsObject = new google.maps.LatLngBounds(sw, ne);

        this.lastBounds = bounds;
        this.map.fitBounds(boundsObject);

        if (debugMode) {
          var rectangle = new google.maps.Rectangle({
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            map: this.map,
            bounds: boundsObject
          });
        }

      },
      resetLastBounds: resetLastBounds,
      getBounds: function () {
        var bounds = this.map.getBounds();

        if (!bounds) {
          return [];
        }

        return [bounds.getSouthWest().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lat(), bounds.getNorthEast().lng()];
      },
      removeAllPoints: removeAllPoints,
      addPoint: addPoint,
      resize: resize,
      createMarkerCluster: createMarkerCluster,
      displaySearchInput: displaySearchInput,
      disableDefaultUI: disableDefaultUI,
      disableInput: disableInput,
      disablePanning: disablePanning,
      disableZooming: disableZooming,
      getMapProvider: function () {
        return 'google';
      }
    }

    return proxyObject;
  }(locationJquey, google);

  GOOGLELOCATION.AutocompleteParams = function ($, google) {
    /*
     * @params OW_GoogleMapLocation locationFieldObject
     * @params Object mapParams
     * {
     *     map: GOOGLELOCATION.Map
     *     mapElementId: string
     * }
     * @return function
     */
    return function (locationFieldObject) {
      var geocoder = new google.maps.Geocoder();

      var latitudeField = locationFieldObject.latitudeField;
      var longitudeField = locationFieldObject.longitudeField;
      var northEastLat = locationFieldObject.northEastLat;
      var northEastLng = locationFieldObject.northEastLng;
      var southWestLat = locationFieldObject.southWestLat;
      var southWestLng = locationFieldObject.southWestLng;
      var addressField = locationFieldObject.addressField;
      var jsonField = locationFieldObject.jsonField;
      var removeValue = locationFieldObject.removeValue;

      var countryRestriction = locationFieldObject.countryRestriction;
      var region = locationFieldObject.region;

      var mapElementId = locationFieldObject.mapElementId;
      var map = locationFieldObject.map;

      var addressFieldId = locationFieldObject.fieldId;
      var fieldId = locationFieldObject.fieldId;

      function setValue(item) {
        var location = item.geometry.location;

        locationFieldObject.showDeleteIcon();

        if (locationFieldObject.dispalyMap) {
          if (!map) {
            map = new window.GOOGLELOCATION.Map(mapElementId);
            map.initialize({});
          }

          var sw = new google.maps.LatLng(item.geometry.viewport.getSouthWest().lat(), item.geometry.viewport.getSouthWest().lng());
          var ne = new google.maps.LatLng(item.geometry.viewport.getNorthEast().lat(), item.geometry.viewport.getNorthEast().lng());

          var bounds = new google.maps.LatLngBounds(sw, ne);

          map.removeAllPoints();
          map.setCenter(item.geometry.location.lat(), item.geometry.location.lng());
          map.addPoint(item.geometry.location.lat(), item.geometry.location.lng(), null, null, null, locationFieldObject.customMarkerIcon);
          map.fitBounds([bounds.getSouthWest().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lat(), bounds.getNorthEast().lng()]);
          map.createMarkerCluster();
          map.resize();
          map.resetLastBounds();
        }

        addressField.val(item.formatted_address);
        longitudeField.val(item.geometry.location.lng());
        latitudeField.val(item.geometry.location.lat());
        northEastLat.val(item.geometry.viewport.getNorthEast().lat())
        northEastLng.val(item.geometry.viewport.getNorthEast().lng())
        southWestLat.val(item.geometry.viewport.getSouthWest().lat())
        southWestLng.val(item.geometry.viewport.getSouthWest().lng())
        removeValue.val(false);

        jsonField.val(JSON.stringify(item).replace('"', '\"'));
      }

      function showDeleteIcon(item) {
        if (locationFieldObject.dispalyMap) {
          $("#" + mapElementId).show();
        }

        $("#" + addressFieldId + "_icon").hide();
        $("#" + addressFieldId + "_delete_icon").css("display", "inline");
      }

      var params = {
        delay: 250,
        matchContains: true,
        source: function (request, response) {
          var icon = $('#' + addressFieldId + '_icon');
          icon.removeClass('ic_googlemap_pin');
          icon.addClass('ow_inprogress');

          var geocoderParams = {
            'address': request.term
          }

          if (countryRestriction) {
            geocoderParams.componentRestrictions = { country: countryRestriction };
          }

          // incorrect usage region parameter
          // if (region)
          // {
          //     geocoderParams.region = region;
          // }

          geocoder.geocode(geocoderParams, function (results, status) {

            icon.removeClass('ow_inprogress');
            icon.addClass('ic_googlemap_pin');

            response($.map(results, function (item) {
              return {
                label: item.formatted_address,
                value: item.formatted_address,
                latitude: item.geometry.location.lat(),
                longitude: item.geometry.location.lng(),
                result: item
              }
            }));
          })
        },
        select: function (event, ui) {
          setValue(ui.item.result)
        }
      };

      return params;
    };
  }(locationJquey, google);

  GOOGLELOCATION_INIT_SCOPE.map(function (pointFunc) {
    pointFunc();
  });
}
