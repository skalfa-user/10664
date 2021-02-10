/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var BOOKMARKS = (function($)
{
    var actionUrl = window.bookmarksActionUrl;
    
    return {
        markState: function(userId, callback)
        {
            if ( !userId || !callback )
            {
                return;
            }
            
            $.ajax({
                url: actionUrl,
                cache: false,
                data: {userId: userId},
                dataType: "json",
                type: "POST",
                success: callback,
                error: function( jqXHR, textStatus, errorThrown )
                {
                    OW.error(textStatus);
                    throw textStatus;
                }
            });
        }
    };
})(jQuery);
