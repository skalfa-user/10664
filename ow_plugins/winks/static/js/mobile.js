/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
var OWM_WinksConsole = function( params )
{
    var self = this;
    self.params = params;

    this.consoleAcceptRequest = function( $node )
    {
        var userId = $node.attr("data-user-id");

        $.ajax({
            url: self.params.acceptUrl,
            type: "POST",
            data: { userId: userId},
            dataType: "json",
            success: function( data )
            {
                if ( data && data.result )
                {
                    location = data.url;
                }
                else if ( data.msg )
                {
                    OWM.error(data.msg);
                }
            }
        });
    };

    this.consoleIgnoreRequest = function( $node )
    {
        var userId = $node.attr("data-user-id");
        var $row = $node.closest(".owm_sidebar_msg_item");

        $.ajax({
            url: self.params.ignoreUrl,
            type: "POST",
            data: {userId: userId },
            dataType: "json",
            success : function(data) {
                if ( data ) {
                    $row.remove();
                    OWM.trigger('mobile.console_item_removed', {section : 'wink-requests'});
                }
                else if ( data.msg )
                {
                    OWM.error(data.msg);
                }
            }
        });
    };

    $(window.document)
        .on('click', '#winks_toolbar a.owm_friend_request_accept', function(){
            self.consoleAcceptRequest($(this));
        })
        .on('click', '#winks_toolbar a.owm_friend_request_ignore', function(){
            self.consoleIgnoreRequest($(this));
        })
        .on('click', '#winks_toolbar .owm_sidebar_msg_string', function(){
            var url = this.getAttribute('data-url');

            if ( url != null ) window.location = url;
        });
};