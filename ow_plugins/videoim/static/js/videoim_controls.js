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
 * VideoIM chat controls
 *
 * @param array object options
 */
VideoImChatControls = function(options)
{
    /**
     * Hide controls
     *
     * @var boolean
     */
    var hideControls = false;

    /**
     * Controls timer
     *
     * @var object
     */
    var controlsTimer;

    /**
     * Disable controls times
     *
     * @var boolean
     */
    var disableControlsTimer = false;

    /**
     * Controls options
     *
     * @var array object
     */
    var controlsOptions =
    {
        "hide_controls_time" : 2000,
        "items_display" : "inline-block",
        "recipient_name" : null,
        "audio" : null,
        "video" : null,
        "fullscreen" : null,
        "hangup" : null,
        "callbacks" : {
            "mute_audio" : null,
            "enable_audio" : null,
            "mute_video" : null,
            "enable_video" : null,
            "fullscreen_out" : null,
            "fullscreen_in" : null,
            "hangup" : null
        }
    };

    // extend options
    controlsOptions = $.extend({}, controlsOptions, options);

    /**
     * Start controls timer
     *
     * @return void
     */
    var startControlsTimer = function()
    {
        controlsTimer = setTimeout(function() {
            // hide controls
            var $controls = getControls();
            $controls.add(controlsOptions.recipient_name).fadeOut("slow");
        }, controlsOptions.hide_controls_time);
    }

    /**
     * Init controls timer
     *
     * @return void
     */
    var initControlsTimer = function()
    {
        // hide controls
        startControlsTimer();

        // don't hide controls while the cursor on them
        var $controls = getControls();
        $controls.hover(function(){
            disableControlsTimer = true;
        },
        function(){
            disableControlsTimer = false;
        });

        // stop hiding controls
        $(document).on("mousemove click", function()
        {
            if ( !hideControls )
            {
                clearInterval(controlsTimer);

                // show all controls
                var $controls = getControls();

                $controls.filter(function() {
                    return $(this).attr("data-init") == 1;
                })
                .css("display", controlsOptions.items_display);

                // show a user name
                $(controlsOptions.recipient_name).show();

                // start it again
                if (!disableControlsTimer) {
                    startControlsTimer();
                }
            }
        });
    }

    /**
     * Init control
     *
     * @param string control
     * @param object muteCallback
     * @param object enableCallback
     * @return void
     */
    var initControl = function(control, muteCallback, enableCallback)
    {
        $(control).css("display", controlsOptions.items_display).attr("data-init", 1).click(function(){
            if ( !$(this).hasClass("on") )
            {
                $(this).addClass("on");

                if ( muteCallback )
                {
                    muteCallback.call();
                }
            }
            else
            {
                // enable
                $(this).removeClass("on");

                if ( enableCallback )
                {
                    enableCallback.call();
                }
            }
        });
    }

    /**
     * Get controls
     *
     * @return object Jquery
     */
    var getControls = function()
    {
        var $controls = $(controlsOptions.video)
            .add(controlsOptions.audio)
            .add(controlsOptions.fullscreen)
            .add(controlsOptions.hangup);

        return $controls;
    }

    //-- public methods --//

    /**
     * Init
     *
     * @param object params
     * @return void
     */
    this.init = function(params)
    {
        // show recipient name
        $(controlsOptions.recipient_name).show();

        // init audio control
        if ( params.audio_enabled )
        {
            initControl(controlsOptions.audio,
                    controlsOptions.callbacks.mute_audio, controlsOptions.callbacks.enable_audio);
        }

        // init video control
        if ( params.video_enabled )
        {
            initControl(controlsOptions.video,
                controlsOptions.callbacks.mute_video, controlsOptions.callbacks.enable_video);
        }

        // init hangup control
        initControl(controlsOptions.hangup, controlsOptions.callbacks.hangup, controlsOptions.callbacks.hangup);

        // init fullscreen control
        initControl(controlsOptions.fullscreen,
                controlsOptions.callbacks.fullscreen_in, controlsOptions.callbacks.fullscreen_out);

        // listen to fullscreen change event
        $(document).bind("fullscreenchange", function()
        {
            if ( !$(document).fullScreen() && $(controlsOptions.fullscreen).attr("class") == "on" )
            {
                $(controlsOptions.fullscreen).removeAttr("class");

                if ( controlsOptions.callbacks.fullscreen_out )
                {
                    controlsOptions.callbacks.fullscreen_out.call();
                }
            }
        });

        // init controls timer
        initControlsTimer();
    }

    /**
     * Hide all controls
     *
     * @return void
     */
    this.hideAllControls = function()
    {
        clearInterval(controlsTimer);
        hideControls = true;

        var $controls = getControls();
        $controls.hide();
    }
}