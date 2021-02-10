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

var OW_GoogleMapLocationAutocomplete = function ($)
{
    return function(fieldId, params) {
        var input = $('#'+fieldId);
        
        if ( input.lenght == 0 )
        {
            return;
        }
        
        var data = $('#'+fieldId).autocomplete(params).data("ui-autocomplete");
        
        if ( !data )
        {
            return;
        }        
        
        data._resizeMenu = function() {
            this.menu.element.outerWidth( $('#' + fieldId).outerWidth() );
            $(this.menu.element).addClass("googlelocation_autocomplite_menu");
        };

        var func = data._renderItem;
        data._renderItem = function( ul, item ) {
            var element = func(ul, item);
            element.find("a").prepend("<span class='ic_googlelocation_menu_item_pin'>");
            return element;
        };
        
        return data;
    }
}(locationJquey);
