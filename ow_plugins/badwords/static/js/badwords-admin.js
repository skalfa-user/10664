/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var badwordsAdmin = (function( $ )
{
    var instance;
    
    function _construct()
    {
        var _elements = {}, _methods = {};
        
        _elements.addBadwordsContent = $( document.getElementById('add-badwords-form') );
        _elements.badwordsForm = window.owForms.badwordsForm;
        
        $( 'span.add-badwords-btn', _elements.tabsContent ).bind( 'click', function()
        {            
            _elements.badwordsForm.resetForm();
            
            var floatbox = new OW_FloatBox(
            {
                $title: 'Bad words',
                width: '500px',
                $contents: _elements.addBadwordsContent
            });
        });
        
        $( 'input[name="badwords-select-all"]', _elements.tabsContent ).bind( 'click', function()
        {
            $( 'input[name="word\[\]"]', document.getElementById(this.value) ).attr( 'checked', this.checked );
        });

        $( 'input.editBadwords', _elements.tabsContent ).bind( 'click', function()
        {
            _elements.badwordsForm.resetForm();
            
            var items = $( 'input[name="word\[\]"]:checked', document.getElementById('badwords-tab-' + this.name) );
            
            if ( items.length === 0 )
            {
                return;
            }
            
            var wordId = [];
            var words = [];
            
            items.each( function()
            {
                wordId.push( this.value );
                words.push( $(this).attr('text').trim() );
            });
            
            _elements.badwordsForm.getElement( 'editBadwords' ).setValue( wordId.join() );
            _elements.badwordsForm.getElement( 'badwords' ).setValue( words.join('\n') );
            
            var floatbox = new OW_FloatBox(
            {
                $title: 'Bad words',
                width: '500px',
                $contents: _elements.addBadwordsContent
            });
        });
        
        _methods.getCookie = function( name )
        {
            var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        };
        
        _methods.setCookie = function( name, value, options )
        {
            options = options || {};
            var expires = options.expires;
            
            if ( typeof expires === 'number' && expires )
            {
                var d = new Date();
                d.setTime( d.getTime() + expires * 1000 );
                expires = options.expires = d;
            }
            
            if ( expires && expires.toUTCString )
            {
                options.expires = expires.toUTCString();
            }
            
            value = encodeURIComponent( value );
            var updatedCookie = name + "=" + value;
            
            for ( var propName in options ) 
            {
                updatedCookie += "; " + propName;
                var propValue = options[propName]; 
                
                if ( propValue !== true )
                {
                    updatedCookie += "=" + propValue;
                }
            }
            
            document.cookie = updatedCookie;
        };
    }

    return {
        getInstance: function()
        {
            if ( !instance )
            {
                instance = _construct();
            }
            
            return instance;
        }
    };
})( jQuery );

OwFormElement.prototype.colorPicker = function( color )
{
    color = this._color = color || '#000000';
    
    var self = this;
    
    $( this.input ).children().css('backgroundColor', color);
    
    $( this.input ).ColorPicker(
    {        
        color: color,
        onShow: function ( colpkr )
        {
            $( colpkr ).fadeIn( 500 );
            return false;
        },
        onHide: function ( colpkr )
        {
            $( colpkr ).fadeOut( 500 );
            return false;
        },
        onChange: function ( hsb, hex, rgb )
        {
            $( 'div', self.input ).css( 'backgroundColor', '#' + hex );
            self._color = '#' + hex;
        }
    });
};

var BadwordsColorField = function( id, name, color )
{
    var formElement = new OwFormElement( id, name );
    
    formElement.colorPicker( color );
    
    formElement.getValue = function()
    {
        return this._color || '#000000';
    };

    return formElement;
};
