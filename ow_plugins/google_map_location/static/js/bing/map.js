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
  GOOGLELOCATION.Map = function ($, Microsoft) {
    var initialize = function (options) {

      var params = {
        zoom: 2,
        mapTypeId: Microsoft.Maps.MapTypeId.road,
        disableKeyboardInput: !true,
        disableMouseInput: !true,
        disablePanning: !true,
        disableTouchInput: !true,
        disableUserInput: !true,
        disableZooming: !true,
        showDashboard: false,
        showMapTypeSelector: false,
        showScalebar: false,
        enableSearchLogo: false
      };
      var opt = $.extend(params, options);

      var element = $('#' + this.mapElementId);
      var width = element[0].style.width;
      var height = element[0].style.height;

      opt.credentials = GOOGLELOCATION.credentials;
      this.map = new Microsoft.Maps.Map(document.getElementById(this.mapElementId), opt);

      element.css('width', width);
      element.css('height', height);
    }

    var disableDefaultUI = function ($value) {
      var options = {}

      options.showDashboard = !$value;
      options.showMapTypeSelector = !$value;
      options.showScalebar = !$value;
    }

    var disableInput = function ($value) {
      var options = {}

      options.disableKeyboardInput = $value;
      options.disableMouseInput = $value;
      options.disableTouchInput = $value;
      options.disableUserInput = $value;
      this.map.setOptions(options);
    }

    var disablePanning = function ($value) {
      var options = {}

      options.disablePanning = $value;
      this.map.setOptions(options);
    }

    var disableZooming = function ($value) {
      var options = {}

      options.disableZooming = $value;
      this.map.setOptions(options);
    }

    var setCenter = function (lat, lon) {
      this.map.setView({ center: new Microsoft.Maps.Location(lat, lon) });
      this.lastBounds = this.map.getBounds();
    }

    var setZoom = function (zoom) {
      this.map.setView({ 'zoom': zoom });
      this.lastBounds = this.map.getBounds();
    }

    var setOptions = function (options) {
      this.map.setView(options);
      //this.lastBounds = this.map.getBounds();
    }

    var resetLastBounds = function () {
      /*if ( this.lastBounds )
       {
       var boundingBox = Microsoft.Maps.LocationRect.fromLocations(
       [new Microsoft.Maps.Location(this.lastBounds.getSoutheast().latitude, this.lastBounds.getSoutheast().longitude),
       new Microsoft.Maps.Location(this.lastBounds.getNorthwest().latitude, this.lastBounds.getNorthwest().longitude)]
       );
       this.map.setView({ 'bounds': boundingBox });
       } */
    }

    var removeAllPoints = function () {
      for (var i = this.map.entities.getLength() - 1; i >= 0; i--) {
        var entity = this.map.entities.get(i);
        if (entity instanceof Microsoft.Maps.Pushpin || entity instanceof Microsoft.Maps.Infobox) {
          this.map.entities.removeAt(i);
        }
        ;
      }
      this.marker = {};
      this.infowindow = {};
    }

    var addPoint = function (lat, lon, title, windowContent, isOpen, customMarkerIconUrl) {
      var self = this;
      var markerParams = {
        draggable: false,
        height: 32,
        width: 32,
        typeName: 'location_pin'
      }

      if (customMarkerIconUrl) {
        markerParams.icon = customMarkerIconUrl;
      }

      var hash = lat + '_' + lon;
      var location = new Microsoft.Maps.Location(lat, lon);
      this.marker[hash] = new Microsoft.Maps.Pushpin(location, markerParams);

      if (windowContent) {
        var options = {
          visible: isOpen,
          offset: new Microsoft.Maps.Point(0, 30),
          description: windowContent
        };

        if (title) {
          options.title = title;
        }

        this.infowindow[hash] = new Microsoft.Maps.Infobox(location, options);

        // Add handler for the pushpin click event.
        Microsoft.Maps.Events.addHandler(this.marker[hash], 'click', function (e) {

          if (self.infowindow[hash].getVisible()) {
            self.infowindow[hash].setOptions({ visible: false });
          }
          else {
            $.each(self.infowindow, function (key, $value) {

              if (key != hash) {
                self.infowindow[key].setOptions({ visible: false });
              }
            });

            self.infowindow[hash].setOptions({ visible: true });
            var location = self.infowindow[hash].getLocation();
            self.setCenter(location.latitude, location.longitude);
          }
        });

        this.map.entities.push(this.infowindow[hash]);
      }

      this.map.entities.push(this.marker[hash]);
    }

    var resize = function () {

    }

    var createMarkerCluster = function () {

    }

    var displaySearchInput = function () {

    }


    var proxyObject = function (elementId) {
      this.geocoder;
      this.map;
      this.marker = {};
      this.infowindow = {};
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
        //console.log(bounds);
        if (!(bounds && $.isArray(bounds) && bounds.length >= 4
          && bounds[0] != undefined && bounds[1] != undefined && bounds[2] != undefined && bounds[3] != undefined)) {
          return;
        }

        this.setCenter(parseFloat(bounds[0]), parseFloat(bounds[1]));

        //monkeyPatchFromLocations();
        var boundingBox = Microsoft.Maps.LocationRect.fromLocations(
          [new Microsoft.Maps.Location(parseFloat(bounds[0]), parseFloat(bounds[1])),
            new Microsoft.Maps.Location(parseFloat(bounds[2]), parseFloat(bounds[3]))]
        );
        //console.log(this.map.getBounds());
        //monkeyPatchMapMath();
        this.map.setView({ 'bounds': boundingBox });
        this.lastBounds = boundingBox;
      },
      resetLastBounds: resetLastBounds,
      getBounds: function () {
        var bounds = this.map.getBounds();

        if (!bounds) {
          return [];
        }

        return [bounds.getSoutheast().latitude, bounds.getNorthwest().longitude, bounds.getNorthwest().latitude, bounds.getSoutheast().longitude];
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
        return 'bing';
      }
    }

    return proxyObject;
  }(locationJquey, Microsoft);

  GOOGLELOCATION.AutocompleteParams = function ($, Microsoft) {

    // Microsoft.Maps.loadModule('Microsoft.Maps.Search');
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
        locationFieldObject.showDeleteIcon();

        var lat = item.point.coordinates[0];
        var lng = item.point.coordinates[1];

        if (locationFieldObject.dispalyMap) {
          if (!map) {
            map = new window.GOOGLELOCATION.Map(mapElementId);
            map.initialize();
          }
          else {
            map.removeAllPoints();
          }

          setTimeout(
            function () {

              map.addPoint(lat, lng, null, null, null, locationFieldObject.customMarkerIcon);
              map.fitBounds(item.bbox);
            }
            , 200);

        }

        addressField.val(item.address.formattedAddress);
        longitudeField.val(lng);
        latitudeField.val(lat);
        northEastLat.val(item.bbox[2]);
        northEastLng.val(item.bbox[3]);
        southWestLat.val(item.bbox[0]);
        southWestLng.val(item.bbox[1]);
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

          $.ajax({
            url: '//dev.virtualearth.net/REST/v1/Locations',
            type: "GET",
            data: {
              "query": request.term,
              "output": "json",
              "key": GOOGLELOCATION.credentials
            },
            dataType: "jsonp",
            jsonp: "jsonp"
          }).done(function (result) {
            icon.removeClass('ow_inprogress');
            icon.addClass('ic_googlemap_pin');

            if (result && result.resourceSets && result.resourceSets[0] && result.resourceSets[0].resources) {
              response($.map(result.resourceSets[0].resources, function (item) {
                return {
                  label: item.address.formattedAddress,
                  result: item
                }
              }));
            }
          }).fail(function (jqXHR, textStatus) {
            console.log(textStatus);
          });
        },
        select: function (event, ui) {
          setValue(ui.item.result);
        }
      };

      return params;
    };
  }(locationJquey, Microsoft);

  GOOGLELOCATION_INIT_SCOPE.map(function (pointFunc) {
    pointFunc();
  });
}
