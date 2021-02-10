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
 * VideoIM
 *
 * @param array object options
 */
function VideoIm(options)
{
    /**
     * Self instance
     *
     * @var {object}
     */
    var self = this;

    /**
     * Processed notifications
     *
     * @var {array}
     */
    var processedNotifications = new Array();

    /**
     * Is session started
     *
     * @var {boolean}
     */
    var sessionStarted = false;

    /**
     * Peer connection
     *
     * @var {object}
     */
    var peerConnection;

    /**
     * Local stream
     *
     * @var {object}
     */
    var localStream;

    /**
     * Remote stream
     *
     * @var {object}
     */
    var remoteStream;

    /**
     * Chat options
     *
     * @var {object}
     */
    var chatOptions =
    {
        credits_mode: false,
        mobile_context: false,
        notifications_lifetime: 0,
        is_initiator: false,
        recipient_id: null,
        session_id: null,
        remote_video: null,
        local_video: null,
        ice_servers: null,
        urls: {
            mark_accepted_url: null,
            notification_url: null,
            track_credits_url: null
        },
        langs: {
            recipient_browser_doesnt_support_webrtc: null,
            user_browser_doesnt_support_webrtc: null,
            connection_expired_message: null,
            share_media_devices_error: null,
            request_blocked: null,
            request_declined: null,
            session_close_confirm: null,
            session_closed: null,
            chat_session_is_over: null,
            does_not_accept_incoming_calls: null,
            you_ran_out_credits: null,
            user_ran_out_credits: null
        },
        callbacks: {
            remote_session_started: null,
            local_session_started: null,
            declined: null,
            blocked: null,
            session_closed: null,
            notification_error: null,
            window_closed: null
        }
    };

    // extend options
    chatOptions = $.extend({}, chatOptions, options);

    /**
     * Offer oprions
     *
     * @var {object}
     */
    var offerOptions = { // new offer options described here:  https://groups.google.com/forum/#!topic/mozilla.dev.media/QxxpNxsfLuY
    };

    /**
     * Message delay time
     *
     * @var {number}
     */
    var messageDelayTime = 10000000000;

    /**
     * Connection status timeout
     *
     * @var {object}
     */
    var connectionStatusTimeout = null;

    /**
     * Track credits timing call timeout
     *
     * @var {object}
     */
    var trackCreditsTimingCallTimeout = null;

    /**
     * Hangup clicked
     *
     * @var {boolean}
     */
    var hangupClicked = false;

    /**
     * Show error message
     *
     * @param {string} message
     * @return void
     */
    var showErrorMessage = function(message)
    {
        OW.message(message, 'error', messageDelayTime);
    }

    /**
     * Show info message
     *
     * @param {string} message
     * @return void
     */
    var showInfoMessage = function(message)
    {
        OW.message(message, 'info', messageDelayTime);
    }

    /**
     * Generic init
     *
     * @return void
     */
    var genericInit = function()
    {
        // Is the  WebRtc enabled in the browser?
        if ( !navigator.getUserMedia )
        {
            showErrorMessage(chatOptions.langs.user_browser_doesnt_support_webrtc);

            sendNotification({
                type: 'not_supported'
            }, false);

            return;
        }

        // close the chat session confirmation
        $(window).on('beforeunload', function()
        {
            if ( !hangupClicked )
            {
                return chatOptions.langs.session_close_confirm;
            }
        });

        $(window).on('unload', function()
        {
            if ( !hangupClicked )
            {
                hangupClicked = true;

                sendNotification({
                    type: 'bye'
                }, false);
            }

            if ( chatOptions.callbacks.window_closed )
            {
                chatOptions.callbacks.window_closed.call(self);
            }
        });

        // get user media both audio and video
        navigator.getUserMedia({audio: true, video: true}, gotStream, function ()
        {
            // get only audio
            navigator.getUserMedia({audio: true, video: false}, gotStream, function ()
            {
                showErrorMessage(chatOptions.langs.share_media_devices_error);
            });
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
    }

    /**
     * Got stream
     *
     * @return void
     */
    var gotStream = function(stream)
    {
        localStream = stream;

        if ( !chatOptions.mobile_context )
        {
            // show a local video
            $(chatOptions.local_video)[0].srcObject = localStream;
        }

        // call registered callback
        if ( chatOptions.callbacks.local_session_started )
        {
            chatOptions.callbacks.local_session_started.call(self);
        }

        makePeerConnection();

        if ( chatOptions.is_initiator )
        {
           sendOffer();
        }

        // check connection status
        if ( chatOptions.notifications_lifetime )
        {
            checkConnectionStatus();
        }

        // process notifications
        OW.bind('videoim.notifications', function(data)
        {
            processNotifications(data);
        });
    }

    /**
     * Check connection status
     *
     * @return void
     */
    var checkConnectionStatus = function()
    {
        // get count of milliseconds from seconds
        var connectionFailedTime = chatOptions.notifications_lifetime * 1000;

        connectionStatusTimeout = setTimeout(function()
        {
            if ( !sessionStarted )
            {
                showErrorMessage(chatOptions.langs.connection_expired_message);
                closeConnection();
            }
        }, connectionFailedTime);
    }

    /**
     * Close connection
     *
     * @return void
     */
    var closeConnection = function()
    {
        // stop the local stream
        if ( localStream )
        {
            try
            {
                // chrome
                localStream.getAudioTracks()[0].stop();
                localStream.getVideoTracks()[0].stop();
            }
            catch (e)
            {}

            try
            {
                // firefox
                localStream.stop();
            }
            catch (e)
            {};
        }

        // clear connection status timer
        if ( connectionStatusTimeout != null )
        {
            clearTimeout(connectionStatusTimeout);
        }

        // clear credits timer
        if ( trackCreditsTimingCallTimeout != null )
        {
            clearTimeout(trackCreditsTimingCallTimeout);
        }

        // close the peer connection
        if ( peerConnection )
        {
            peerConnection.close();
            peerConnection = null;
        }

        hangupClicked = true;

        if ( chatOptions.callbacks.session_closed )
        {
            chatOptions.callbacks.session_closed.call(self);
        }
    }

    /**
     * Make peer connection
     *
     * @return void
     */
    var makePeerConnection = function()
    {
        // create a peer connection
        var peerConnectionConstraints = {
            optional: [
                {
                    DtlsSrtpKeyAgreement: true
                }
            ]
        };

        peerConnection = chatOptions.ice_servers !== null
            ? new window.RTCPeerConnection({iceServers: chatOptions.ice_servers}, peerConnectionConstraints)
            : new window.RTCPeerConnection(null, peerConnectionConstraints);

        peerConnection.onicecandidate = gotIceCandidate;
        peerConnection.ontrack = gotRemoteTracks;

        if ( chatOptions.credits_mode )
        {
            peerConnection.oniceconnectionstatechange = onIceConnectionStateChange;
        }

        localStream.getTracks().forEach(function(track) {
            peerConnection.addTrack(track, localStream);
        });
    }

    /**
     * Process notifications
     *
     * @param {object} data
     * @return void
     */
    var processNotifications = function(data)
    {
        // don't need to process any notifications
        if ( hangupClicked )
        {
            return;
        }

        $.each(data, function(i, item)
        {
            // don't process already processed notifications
            var $notificationKey = processedNotifications.indexOf(item.id);

            // skip all notifications from different users
            if ( item.userId == chatOptions.recipient_id && $notificationKey == -1 )
            {
                processedNotifications.push(item.id);
                var notification = JSON.parse(item.notification);

                if ( item.sessionId != chatOptions.session_id && notification.type != 'offer' )
                {
                    return;
                }

                switch ( notification.type )
                {
                    case 'not_supported' :
                        showErrorMessage(chatOptions.langs.recipient_browser_doesnt_support_webrtc);
                        closeConnection();
                        break;

                    case 'not_permitted' :
                        showErrorMessage(chatOptions.langs.does_not_accept_incoming_calls);
                        closeConnection();
                        break;

                    case 'bye' :
                        showInfoMessage(chatOptions.langs.session_closed);
                        closeConnection();
                        break;

                    case 'credits_out' :
                        showErrorMessage(chatOptions.langs.user_ran_out_credits);
                        closeConnection();
                        break;

                    case 'blocked'  :
                        showErrorMessage(chatOptions.langs.request_blocked);
                        closeConnection();

                        if ( chatOptions.callbacks.blocked )
                        {
                            chatOptions.callbacks.blocked.call(self);
                        }
                        break;

                    case 'declined' :
                        showErrorMessage(chatOptions.langs.request_declined);
                        closeConnection();

                        if ( chatOptions.callbacks.declined )
                        {
                            chatOptions.callbacks.declined.call(self);
                        }
                        break;

                    case 'offer' :
                        if ( !peerConnection )
                        {
                            break;
                        }

                        var iceConnectionState = peerConnection.iceConnectionState.toLowerCase();

                        // check connection state in order resolve fast reconnection issue
                        if (iceConnectionState === 'new' || iceConnectionState === 'checking')
                        {
                            //  now we aren't initiator
                            if ( chatOptions.is_initiator && videoImRequest.getLoggedUserId() > item.userId )
                            {
                                chatOptions.is_initiator = false;

                                if ( peerConnection )
                                {
                                    peerConnection.close();
                                }

                                // change session id
                                chatOptions.session_id = item.sessionId;

                                // mark notifications as accepted
                                $.post(chatOptions.urls.mark_accepted_url, {
                                    'user_id' : chatOptions.recipient_id,
                                    'session_id' : chatOptions.session_id
                                });

                                // reconnect
                                makePeerConnection();
                            }
                            else
                            {
                                // mark interlocutor's incoming offer by its session id
                                // because interlocutor will accept my offer and I don't need his one
                                $.post(chatOptions.urls.mark_accepted_url, {
                                    'user_id' : chatOptions.recipient_id,
                                    'session_id' : item.sessionId
                                });
                            }
                        }

                        if ( !chatOptions.is_initiator )
                        {
                            peerConnection.setRemoteDescription(new window.RTCSessionDescription(notification), function()
                            {
                                peerConnection.createAnswer(gotLocalDescription, function (error)
                                {
                                    showErrorMessage(error);
                                }, offerOptions);
                            }, function(error) {
                                showErrorMessage(error);
                            });
                        }

                        break;

                    case 'answer' :
                        peerConnection.setRemoteDescription(new window.RTCSessionDescription(notification), function(){}, function(error)
                        {
                            showErrorMessage(error);
                        });
                        break;

                    case 'candidate' :
                        if ( !sessionStarted )
                        {
                            var candidate = new window.RTCIceCandidate({
                                sdpMLineIndex: notification.label,
                                candidate: notification.candidate
                            });

                            peerConnection.addIceCandidate(candidate);
                        }
                        break;

                    default :
                }
            }
        });
    }

    /**
     * Send offer
     *
     * @return void
     */
    var sendOffer = function()
    {
        peerConnection.createOffer(gotLocalDescription, function(error)
        {
            showErrorMessage(error);
        }, offerOptions);
    }

    /**
     * Got local description
     *
     * @param {object} description
     * @return void
     */
    var gotLocalDescription = function(description)
    {
        peerConnection.setLocalDescription(description, function(){}, function(error)
        {
            showErrorMessage(error);
        });

        // google chrome fix (make compatible with old versions)
        // http://stackoverflow.com/questions/33284549/nw-js-failed-to-parse-sessiondescription-failed-to-parse-audio-codecs-correctl
        // https://twitter.com/HCornflower/status/656215827813826561
        if ( typeof description.sdp != 'undefined' )
        {
            description.sdp = description.sdp.replace(new RegExp('UDP/TLS/RTP/SAVPF', 'g'), 'RTP/SAVPF');
        }

        sendNotification(description);
    }

    /**
     * Got ice candidate
     *
     * @param {object} event
     * @return void
     */
    var gotIceCandidate = function(event)
    {
        if ( !event.candidate || !peerConnection )
        {
            return;
        }

        if ( event.candidate )
        {
            sendNotification({
                type: 'candidate',
                label: event.candidate.sdpMLineIndex,
                id: event.candidate.sdpMid,
                candidate: event.candidate.candidate
            });
        }
    }

    /**
     * Get remote stream
     *
     * @param {object} event
     * @return void
     */
    var gotRemoteStream = function(event)
    {
        remoteStream = event.stream;

        // show a remote video
        $(chatOptions.remote_video)[0].srcObject = remoteStream;
        sessionStarted = true;

        // call registered callback
        if ( chatOptions.callbacks.remote_session_started )
        {
            chatOptions.callbacks.remote_session_started.call(self);
        }

         if ( chatOptions.credits_mode )
         {
             trackCreditsTimingCall();
         }
    }

    /**
     * Get remote tracks
     *
     * @param {object} event
     * @return void
     */
    var gotRemoteTracks = function(event)
    {
        if ( event.streams.length ) {
            remoteStream = event.streams[0];

            // show a remote video
            $(chatOptions.remote_video)[0].srcObject = remoteStream;
            sessionStarted = true;

            // call registered callback
            if ( chatOptions.callbacks.remote_session_started )
            {
                chatOptions.callbacks.remote_session_started.call(self);
            }
        }
    }

    /**
     * On ice connection state change
     *
     * @return void
     */
    var onIceConnectionStateChange = function()
    {
        if ( peerConnection && peerConnection.iceConnectionState.toLowerCase() === 'connected' )
        {
            trackCreditsTimingCall();
        }
    }

    /**
     * Track credits timing call
     *
     *  @return void
     */
    var trackCreditsTimingCall = function()
    {
        $.post(chatOptions.urls.track_credits_url, {
            'is_initiator' : chatOptions.is_initiator
        }, function(data) {
            var data = JSON.parse(data);

            if ( !data.allowed )
            {
                showErrorMessage(chatOptions.langs.you_ran_out_credits);
                closeConnection();
                sendNotification({
                    type: 'credits_out'
                });

                return;
            }

            // send request each minute
            trackCreditsTimingCallTimeout = setTimeout(trackCreditsTimingCall, 60000);
        });
    }

    /**
     * Send notification
     *
     * @param {object} notification
     * @param {boolean} isAsync
     * @return void
     */
    var sendNotification = function(notification, isAsync)
    {
        var asyncQuery = typeof isAsync != 'undefined' ? isAsync : true;

        $.ajax({
            url: chatOptions.urls.notification_url,
            async: asyncQuery,
            method : 'post',
            dataType : 'json',
            data : {
                'session_id' : chatOptions.session_id,
                'recipient_id' : chatOptions.recipient_id,
                'notification' : JSON.stringify(notification)
            }
        }).done(function(data) {
            if ( !data.result && data.message )
            {
                showErrorMessage(data.message);

                if ( chatOptions.callbacks.notification_error )
                {
                    chatOptions.callbacks.notification_error.call(self);
                }
            };
        });
    }

    chatOptions.mobile_context ? mobileInit() : desktopInit();

    //-- public methods --//

    /**
     * Hangup
     *
     * @return void
     */
    this.hangup = function()
    {
        if ( !hangupClicked )
        {
            showInfoMessage(chatOptions.langs.chat_session_is_over);
            closeConnection();
            sendNotification({
                type: 'bye'
            });
        }
    }

    /**
     * Mute local audio
     *
     * @return void
     */
    this.muteLocalAudio = function()
    {
        if ( localStream && typeof localStream.getAudioTracks()[0] != 'undefined' )
        {
            localStream.getAudioTracks()[0].enabled = false;
        }
    }

    /**
     * Enable local audio
     *
     * @return void
     */
    this.enableLocalAudio = function()
    {
        if ( localStream && typeof localStream.getAudioTracks()[0] != 'undefined' )
        {
            localStream.getAudioTracks()[0].enabled = true;
        }
    }

    /**
     * Mute local video
     *
     * @return void
     */
    this.muteLocalVideo = function()
    {
        if ( localStream && typeof localStream.getVideoTracks()[0] != 'undefined' )
        {
            localStream.getVideoTracks()[0].enabled = false;
        }
    }

    /**
     * Is local audio enabled
     *
     * @return boolean
     */
    this.isLocalAudioEnabled = function()
    {
        if ( localStream && typeof localStream.getAudioTracks()[0] != 'undefined' )
        {
            return localStream.getAudioTracks()[0].enabled;
        }

        return  false;
    }

    /**
     * Enable local video
     *
     * @return void
     */
    this.enableLocalVideo = function()
    {
        if ( localStream && typeof localStream.getVideoTracks()[0] != 'undefined' )
        {
            localStream.getVideoTracks()[0].enabled = true;
        }
    }

    /**
     * Is local video enabled
     *
     * @return boolean
     */
    this.isLocalVideoEnabled = function()
    {
        if ( localStream && typeof localStream.getVideoTracks()[0] != 'undefined' )
        {
            return localStream.getVideoTracks()[0].enabled;
        }

        return  false;
    }
}
