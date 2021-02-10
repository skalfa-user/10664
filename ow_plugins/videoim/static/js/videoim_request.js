/**
 * Copyright (c) 2015, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugins.videoim
 * @since 1.8.1
 */

/**
 * VideoIM request
 *
 * @param array object options
 */
function VideoImRequest(options)
{
    /**
     * Logged user cookie name
     *
     * @var string
     */
    var loggedUserCookieName = 'videoImLoggedUserId';

    /**
     * Active user cookie name
     *
     * @var string
     */
    var activeUserCookieName = 'videoImActiveUserId';

    /**
     * Chat window
     *
     * @var Window
     */
    var chatWindow = null;

    /**
     * Video im sound
     *
     * @var Howl
     */
    var videoImSound;

    /**
     * Video im sound muted
     *
     * @var boolean
     */
    var videoImSoundMuted = false;

    /**
     * Request options
     *
     * @var array object
     */
    var requestOptions =
    {
        "mobile_context" : false,
        "chat_window_width"  : 600,
        "chat_window_height" : 480,
        "urls" : {
            "base_sounds_url" : null,
            "mark_accepted_url": null,
            "chat_link_url" : null,
            "chat_url" : null,
            "block_url" : null,
            "decline_url" : null
        }
    };

    requestOptions = $.extend({}, requestOptions, options);

    /**
     * Processing notifications
     *
     * @var boolean
     */
    var processingNotifications = true;

    /**
     * Generic init
     *
     * @return void
     */
    var genericInit = function()
    {
        // init a sound signaling
        videoImSound = new Howl({
            src: [
                requestOptions.urls.base_sounds_url + '/ring.ogg',
                requestOptions.urls.base_sounds_url + '/ring.mp3'
            ],
            loop: true,
            autoplay: false,
            buffer: true
        });

        // process notifications
        OW.bind('videoim.notifications', function(data)
        {
            processNotifications(data);
        });
    }

    /**
     * Mobile init
     *
     * @return void
     */
    var mobileInit = function()
    {
        genericInit();
    }

    /**
     * Desktop init
     *
     * @return void
     */
    var desktopInit = function()
    {
        genericInit();

        // mailbox integration - render a chat link
        OW.bind('mailbox.after_dialog_render', function(params)
        {
            initDesktopMailboxChatLink(params);
        });
    }

    /**
     * Get logged user id
     *
     * @return integer
     */
    this.getLoggedUserId = function()
    {
        return getUserId(loggedUserCookieName);
    }

    /**
     * Get active user id
     *
     * @return integer
     */
    this.getActiveUserId = function()
    {
        return getUserId(activeUserCookieName);
    }

    /**
     * Get user id
     *
     * @return integer
     */
    var getUserId = function(cookieName)
    {
        var name = cookieName + '=';
        var ca = document.cookie.split(';');

        for ( var i = 0; i < ca.length; i++ )
        {
            var c = ca[i];

            while (c.charAt(0) == ' ')
            {
                c = c.substring(1);
            }

            if ( c.indexOf(name) == 0 )
            {
                return parseInt(c.substring(name.length, c.length));
            }
        }

        return;
    }

    /**
     * Process notifications
     *
     * @param data
     */
    var processNotifications = function(data)
    {
        if ( processingNotifications )
        {
            var sessionId = null;
            var acceptNotifications = [];

            $.each(data, function (i, item)
            {
                // is the notification accepted?
                if ( !parseInt(item.accepted) )
                {
                    var $notificationKey = acceptNotifications.indexOf(item.userId);

                    // is notification already accepted
                    if ( $notificationKey == -1 )
                    {
                        sessionId = item.sessionId;
                        acceptNotifications.push(item.userId);
                    }

                    var notification = JSON.parse(item.notification);

                    // trigger the dialog end event
                    if ( notification.type == 'bye' )
                    {
                        OW.trigger('videoim.request_dialog_end', [{'user' : item.userId}]);
                    }

                    // show a confirmation window
                    if ( notification.type == 'offer' && videoImRequest.getActiveUserId() != item.userId && $notificationKey == -1 )
                    {
                        // show a chat confirmation window
                        if ( !requestOptions.mobile_context )
                        {
                            OW.ajaxFloatBox("VIDEOIM_CMP_ChatConfirmationWindow", [item.userId, item.sessionId], {
                                "layout": "empty"
                            });
                        }
                        else
                        {
                            OW.ajaxFloatBox("VIDEOIM_MCMP_ChatConfirmationWindow", [item.userId, item.sessionId]);
                        }

                        processingNotifications = false;
                    }
                }
            });

            if ( acceptNotifications && sessionId )
            {
                acceptNotifications.every(function(element, index, array) {
                    // mark all notifications as accepted
                    $.post(requestOptions.urls.mark_accepted_url, {
                        'user_id' : element,
                        'session_id' : sessionId
                    });
                });
            }
        }
    }

    /**
     * Init desktop mailbox chat link
     *
     * @param array object params
     * @return void
     */
    var initDesktopMailboxChatLink = function(params)
    {
        $.getJSON(requestOptions.urls.chat_link_url, {'recipientId' : params.opponentId}, function(data)
        {
            if ( data.content )
            {
                // append the chat link
                $(params.control).find("[data-type='media_buttons']").append(data.content);
            }
        });
    }

    requestOptions.mobile_context ? mobileInit() : desktopInit();

    //-- public methods --//

    /**
     * Set logged user id
     *
     * @param integer userId
     * @return void
     */
    this.setLoggedUserId = function(userId)
    {
        setUserId(loggedUserCookieName, userId);
    }

    /**
     * Set active user id
     *
     * @param integer userId
     * @return void
     */
    this.setActiveUserId = function(userId)
    {
        setUserId(activeUserCookieName, userId);
    }

    /**
     * Set active user id
     *
     * @param string cookieName
     * @param integer userId
     * @return void
     */
    setUserId = function(cookieName, userId)
    {
        document.cookie = cookieName + '=' + userId + '; expires=0; path=/';
    }

    /**
     * Get chat window
     *
     * @param integer recipientId
     * @param boolean initiator
     * @param string sessionId
     * @return void
     */
    this.getChatWindow = function(recipientId, initiator, sessionId)
    {
        initiator = typeof initiator != "undefined" ? initiator : true;
        sessionId = typeof sessionId != "undefined" ? sessionId : Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 20);

        this.setActiveUserId(recipientId);

        // show a chat confirmation window
        if ( !requestOptions.mobile_context )
        {
            // open chat in a popup window
            var chatWindow = window.open(requestOptions.urls.chat_url + '?recipientId=' + recipientId + '&sessionId=' + sessionId + (initiator ? '&initiator=1' : ""),
                "chat",
                "width=" + requestOptions.chat_window_width +
                ",height=" + requestOptions.chat_window_height + ",resizable=no,scrollbars=no,status=no"
            )

            chatWindow.focus();
        }
        else
        {
            location.href = requestOptions.urls.chat_url +
                    '?recipientId=' + recipientId + '&sessionId=' + sessionId + (initiator ? '&initiator=1' : "");
        }
    }

    /**
     * Set processing notifications
     *
     * @param boolean process
     * @return void
     */
    this.setProcessingNotifications = function(process)
    {
        processingNotifications = process;
    }

    /**
     * Block user
     *
     * @param integer userId
     * @param string sessionId
     * @return void
     */
    this.blockUser = function(userId, sessionId)
    {
        $.post(requestOptions.urls.block_url, {'user_id': userId, 'session_id': sessionId});
    }

    /**
     * Decline request
     *
     * @param integer userId
     * @param string sessionId
     * @return void
     */
    this.declineRequest = function(userId, sessionId)
    {
        $.post(requestOptions.urls.decline_url, {'user_id' :userId, 'session_id': sessionId});
    }

    /**
     * Start sound
     *
     * @return  void
     */
    this.startSound = function()
    {
        videoImSound.play();
    }

    /**
     * Stop sound
     *
     * @return  void
     */
    this.stopSound = function()
    {
        videoImSound.stop();
    }

    /**
     * Mute sound
     *
     * @return  void
     */
    this.muteSound = function()
    {

        videoImSoundMuted = true;
    }

    /**
     * Unmute sound
     *
     * @return  void
     */
    this.unmuteSound = function()
    {

        videoImSoundMuted = false;
    }

    /**
     * Is sound manual muted
     *
     * @return boolean
     */
    this.isSoundManualMuted = function()
    {
        return videoImSoundMuted;
    }
}