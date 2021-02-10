/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * User search ajax actions controller.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.static.js
 * @since 1.6.1
 */


var USEARCH_QuickSearchModel = function( $items, positions )
{
    this.positions = {
        'position1' : undefined,
        'position2' : undefined,
        'position3' : undefined,
        'position4' : undefined
    };
    
    this.items = {};
    this.construct($items, positions);
}

USEARCH_QuickSearchModel.prototype = {
    construct : function ( $items, positions )
    {
        var self = this;

        self.positionList = []

        $.each(self.positions, function(key, element) {
            self.positionList.push(key);
        });
        
        if ( $items )
        {
            self.items = $items
        }

        if ( positions )
        {
            $.each( positions, function( position , $value ) {
                if ( position && $value )
                {
                    self.positions[position] = $value;
                }
            } );
        }
    },

    addItem : function ( $key , $value, $position )
    {
        var self = this;

        if ( !$key || !$value )
        {
            return;
        }

        if( $.inArray($position, self.positionList) == -1 )
        {
            return;
        }

        var $itemName = self.positions[$position];

        if ( $itemName )
        {
            self.deleteItem($itemName, true);
        }

        self.items[$key] = $value;
        self.positions[$position] = $key;

        OW.trigger('usearch.quick_search_model_render', self);
    },

    deleteItem : function ( key, notRenderModel )
    {
        var self = this;
        var itemValue = self.items[key];
        
        self.items[key] = undefined;

        $.each( self.positions, function( position, value ) {

            if ( value == key )
            {
                self.positions[position] = undefined;
            }
            
        } );

        OW.trigger('usearch.quick_search_model_delete_item', {key: key, value: itemValue});

        if ( !notRenderModel )
        {
            OW.trigger('usearch.quick_search_model_render', self);
        }
    },

    changePosition : function ( position1, position2 )
    {
        var self = this;

        if( $.inArray(position1, self.positionList) == -1 || !self.positions[position1] )
        {
            return;
        }

        if( $.inArray(position2, self.positionList) == -1 )
        {
            return;
        }

        if ( position1 == position2 )
        {
            return;
        }

        var tmp = self.positions[position1];
        self.positions[position1] = self.positions[position2];
        self.positions[position2] = tmp;

        OW.trigger('usearch.quick_search_model_render', self);
    }
}

var USEARCH_QuickSearchView = function( responderUrl )
{
    var self = this;
    this.template = $('#item-quick_search_template');
    this.emptyTemplate = $('#item-quick_empty_position');
    this.node = $('.ow_quicksearch_layout_list');
    this.responderUrl = responderUrl;

    this.init = function( $model )
    {
        OW.bind('usearch.quick_search_model_render', function() {
            self.renderModel( $model );
            self.saveModel($model);
        } );

        OW.bind('usearch.quick_search_add_item_to_position', function(params) {
            if ( params.key && params.value && params.position )
            {
                $model.addItem(params.key, params.value, params.position);
            }
        });

         /* $(".ow_quicksearch_list").droppable({
            accept: ".ow_quicksearch_layout_list .quicksearch_dnd_item",
            //activeClass: "placeholder_hover",
            drop: function( event, ui ) {
                if ( ui.draggable )
                {
                    var item = ui.draggable;
                    var question_name = item.attr('question-name');
                    //var position = item.parents("div:eq(0)").attr('position');

                    $model.deleteItem(question_name);
                }
            }
        }); */

        self.initDrugAndDrop($model);
    }
    
    this.saveModel = function( $model )
    {        
        var ajaxOptions = {
            url: self.responderUrl,
            dataType: 'json',
            type: 'POST',
            data: {positions: $model.positions},
            success: function(result) {
                
            }
        };

        $.ajax(ajaxOptions);
    }
    
    this.initDrugAndDrop = function( $model )
    {
        $(".ow_quicksearch_position .quicksearch_dnd_item", self.node).draggable( {
            appendTo: self.node,

            start: function(event, ui) {
                $(this).removeClass('ow_quicksearch_item_in_layout').addClass('ow_quicksearch_item_placeholder');
            },

           stop: function(event, ui) {

                
                var node = $('.ow_quicksearch_layout_wrap');
                

                var layout = self.node;
                var position = layout.position();

                position.left -= (node.width() - layout.width())/2;
                position.top -= (node.height() - layout.height())/2;

                var width = layout.width() + (node.width() - layout.width());
                var height = layout.height() + (node.height() - layout.height());

                var helperPosition = ui.position;
                var helperWidth = ui.helper.width();
                var helperHeight = ui.helper.height();

                /* console.log(helperPosition.left + helperWidth < position.left);
                console.log(helperPosition.left > position.left + width);
                console.log(helperPosition.top + helperHeight < position.top);
                console.log(helperPosition.top > position.top + height); */

                if ( helperPosition.left + helperWidth < position.left || helperPosition.left > position.left + width
                        || helperPosition.top + helperHeight < position.top || helperPosition.top > position.top + height )
                {
                    var item = $(this);
                    var question_name = item.attr('question-name');
                    //var position = item.parents("div:eq(0)").attr('position');

                    $model.deleteItem(question_name);
                }
                else
                {
                    $(this).removeClass('ow_quicksearch_item_placeholder').addClass('ow_quicksearch_item_in_layout');
                }
            },

            helper: function(event, ui) {
                var width = $(this).width();
                return $("<div></div>").append($(this).clone().addClass('ow_quicksearch_item_placeholder').addClass("placeholder_hover").removeClass('ow_quicksearch_item_in_layout')
                .css("width", width + "px"));
            }
        } );
        
        $('.ow_quicksearch_layout  .ow_quicksearch_position').droppable( {
            accept: ".quicksearch_dnd_item, .available_field_item",
            
            drop: function( event, ui ) {
                if ( ui.draggable )
                {
                    if ( ui.draggable.hasClass('available_field_item') )
                    {
                        OW.trigger('usearch.on_item_drop_to_position', {positionNode: this, item: ui.draggable });
                    }
                    else
                    {
                        var item1 = $(ui.draggable);
                        var item2 = $(this);

                        var item1Position = item1.parents("div:eq(0)").attr('position');
                        var item2Position = item2.attr('position');

                        $model.changePosition(item1Position, item2Position);
                    }

                }
            }
        } );

        /* $('body').droppable( {
            accept: ".quicksearch_dnd_item",

            drop: function( event, ui ) {
                if ( ui.draggable )
                {
                    console.log(ui);
                }
            }
        } ); */
    }

    this.renderModel = function(model)
    {
        var template = self.template;
        var node = self.node;

        if ( model.items && template && node )
        {
            node.children().detach();
            
            $.each( model.positions, function( position, key ) {

                var html = $(self.emptyTemplate.html());
                var value = undefined;

                html.attr( 'position', position );

                if ( key && model.items[key] )
                {
                    html = $(template.html());
                    value = model.items[key];
                    html.attr( 'position', position );
                    html.find('.layout_item').attr( 'question-name', key );

                    if ( key != 'sex' &&  key != 'match_sex' )
                    {
                        html.find('.layout_item').addClass('quicksearch_dnd_item');
                    }
                    else
                    {
                        html.removeClass('ow_quicksearch_position').addClass('ow_quicksearch_position_disabled');
                        html.find('.layout_item').addClass('quicksearch_dnd_disbale_item');
                    }
                    
                    html.find('.ow_quicksearch_label').html( value );
                }

                node.append(html);
            } );

            self.initDrugAndDrop(model);
        }
    }
}

var USEARCH_ListModel = function( $items, $template )
{
    this.items = {};
    this.construct($items, $template);
}

USEARCH_ListModel.prototype = {
    construct : function ( $items, $template )
    {
        var self = this;

        self.template = $template;

        if ( $items )
        {
            self.items = $items;
        }
    },

    addItem : function ( $key , $value )
    {
       if ( $key && $value )
       {
            var self = this;
            self.items[$key] = $value;
            OW.trigger('usearch.list_model_render',self);
       }
    },

    deleteItem : function ( key )
    {
        var self = this;
        self.items[key] = undefined;
        OW.trigger('usearch.list_model_render',self);
    }
}

var USEARCH_ListView = function()
{
    var self = this;

    this.template = $('#allowed_questions_template');
    this.node = $('.ow_quicksearch_list');
    this.avalableItemsList = {}

    this.init = function( $model, $avalableItemsList )
    {
        if ( $avalableItemsList )
        {
            self.avalableItemsList = $avalableItemsList;
        }

        OW.bind('usearch.list_model_render', function() { 
            self.renderModel( $model );
        } );

        OW.bind('usearch.quick_search_model_delete_item', function(params) {
                $model.addItem( params.key, params.value );
        });

        OW.bind('usearch.on_item_drop_to_position', function(params) {
            if ( params.item && params.positionNode )
            {
                var item = $(params.item);
                var positionNode = $(params.positionNode);
                var question_name = item.attr('question-name');
                var value = item.find('.ow_quicksearch_label').html();
                var position = positionNode.attr('position');

                $model.deleteItem(question_name);
                OW.trigger('usearch.quick_search_add_item_to_position', {'key': question_name, 'value': value, 'position': position });
            }
        });

        self.initDrugAndDrop( $model );

        /* $('.ow_quicksearch_layout  .ow_quicksearch_position').droppable({
            accept: ".available_field_item",
            //activeClass: "placeholder_hover",
            drop: function( event, ui ) {
                if ( ui.draggable )
                {
                    var item = ui.draggable;
                    var positionNode = $(this);
                    var question_name = item.attr('question-name');
                    var value = item.find('.ow_quicksearch_label').html();
                    var position = positionNode.attr('position');

                    $model.deleteItem(question_name);
                    OW.trigger('usearch.quick_search_add_item_to_position', {key: question_name, value: value, position: position });
                }
            }
        }); */
    }

    this.initDrugAndDrop = function( $model )
    {
        $(".ow_quicksearch_item", self.node).draggable({
            appendTo: self.node,

            zIndex:999999,

            start: function(event, ui) {
                $(this).removeClass('ow_quicksearch_item').addClass('ow_quicksearch_item_placeholder');
            },

           stop: function(event, ui) {
                $(this).removeClass('ow_quicksearch_item_placeholder').addClass('ow_quicksearch_item');
            },

            helper: function(event, ui) {
                var width = $(this).width();
                return $("<div></div>").append($(this).clone().addClass('ow_quicksearch_item_placeholder').addClass("placeholder_hover").removeClass('ow_quicksearch_item')
                .css("width", width + "px") );
            }
        });
    }
    
    this.renderModel = function(model)
    {
        var items = model.items;
        var template = self.template;
        var node = self.node;

        if ( items && template && node )
        {
            node.children().detach();

            $.each( self.avalableItemsList, function( name, value ) {
                if ( items[name] )
                {
                    var html = $(template.html());

                    html.find('.ow_quicksearch_label').html( value );
                    html.attr( 'question-name', name );

                    node.append(html);
                }
            } );
        }

        self.initDrugAndDrop(model);
    }
}