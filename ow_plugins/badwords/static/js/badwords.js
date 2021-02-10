/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var badwords = (function( $ )
{
    var instance;
    
    function _construct()
    {
        return {
            bindQuestion: function()
            {
                $(".questions-answers").ajaxSuccess(function()
                {
                    if ( arguments[2] )
                    {
                        if ( arguments[2].context && arguments[2].context.node )
                        {
                            $( arguments[2].context.node ).find( '.qa-text' ).each( function()
                            {
                                $( this ).html( $(this).html().replace(window.badwordsParams.pattern, window.badwordsParams.replacement) );
                            });
                        }
                        
                        if ( (arguments[2].url.indexOf('/base/ajax-loader/component/?cmpClass=QUESTIONS_CMP_Question') !== -1) ||
                            (arguments[2].url.indexOf('/base/ajax-loader/component/?cmpClass=EQUESTIONS_CMP_Question') !== -1) )
                        {
                            var content = $( '.questions-question:visible' );
                            
                            if ( content.length !== 0 )
                            {
                                var title = content.find( '.q-text' );
                                title.html( title.html().replace(window.badwordsParams.pattern, window.badwordsParams.replacement) );
                                
                                $( '.qa-text', content ).each( function()
                                {
                                    $( this ).html( $(this).html().replace(window.badwordsParams.pattern, window.badwordsParams.replacement) );
                                });
                            }
                        }
                    }
                });
            }
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
        },
        getCleareContent: function( content )
        {
            if ( !content ) return;
            
            return content.toString().replace(window.badwordsParams.pattern, window.badwordsParams.replacement);
        }
    };
})( jQuery );
