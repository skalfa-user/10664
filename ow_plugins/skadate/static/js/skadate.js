/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
Skadate = {};

Skadate.UserCarousel = function( uniqId, params ) {
    var HIDE_TIMEOUT = 50;
    var SHOW_TIMEOUT = 300;
    var SHOW_ANIMATION = 200;
    var HIDE_ANIMATION = 50;
    
    var cont = $("#" + uniqId);
    
    if ( Skadate.UserCarousel.ITEM_WIDTH === null ) {
        Skadate.UserCarousel.ITEM_WIDTH = cont.find(".uc-list-item").width();
    }
    
    var getWrapWidth = function() { return cont.width() - 48; };
    var wrapWidth = getWrapWidth();
    
    var calcVisible = function() {
        return parseInt(getWrapWidth() / Skadate.UserCarousel.ITEM_WIDTH);
    };
    
    var calcScroll = function( visible ) {
        var v = parseInt(params.visible / 2);
        return v < 1 ? 1 : v;
    };
    
    var itemsCount = cont.find(".uc-list-item").length;
    var visibleCount = calcVisible();
        
    params.visible = params.visible || visibleCount;
    params.scroll = params.scroll || calcScroll(params.visible);
    
    var itemMargin = Math.floor((wrapWidth / visibleCount - Skadate.UserCarousel.ITEM_WIDTH) / 2);

    cont.find(".uc-list-item").css("padding-left", itemMargin);
    cont.find(".uc-list-item").css("padding-right", itemMargin);

    if ( itemsCount > visibleCount )
    {
        $(".uc-carousel", cont).jCarouselLite($.extend(params, {
            btnNext: $(".uc-next-btn", cont),
            btnPrev: $(".uc-prev-btn", cont)
        }));
    } else {
        $(".uc-next-btn, .uc-prev-btn", cont).hide();
    }
    
    $("#" + uniqId + "_tooltips").appendTo("body").data({
        shown: false,
        showing: false
    });

    var showing = false, hiding = false;
    var currentTooltip = null;
    var showTO = null;
    var hideTO = null;
    
    var clearTO = function( to ) {
        if ( to ) {
            window.clearTimeout(to);
        }
    };
    
    var reset = function() {
        clearTO(showTO);
        clearTO(hideTO);
        showing = hiding = false;
        
        cont.find("*").unbind();
        $(document).off("mousemove.ucarousel");
        params.visible = calcVisible();
        params.scroll = calcScroll(params.visible);
 
        Skadate.UserCarousel(uniqId, params);
    };
    
    window.setTimeout(function prv() {
        if ( wrapWidth !== getWrapWidth() ) {
            reset();
        } else {
            window.setTimeout(prv, 300);
        }
    }, 300);
    
    var showCurrentTooltip  = function( cb ) {
        var animate = true;
        
        if ( !$.isFunction(cb) && cb !== undefined ) {
            animate = cb;
            cb = false;
        }
        
        hideCurrentTooltip(false);
        var tt = $(".uc-tooltip[data-uid=" + currentTooltip + "]");
        showing = currentTooltip;
        
        if ( animate ) {
            tt.stop(true, true).fadeIn(SHOW_ANIMATION, function() {
                if (cb) cb.apply(this, arguments);
                showing = false;
            });
        } else {
            tt.show();
            showing = false;
        }
    };
    
    var hideCurrentTooltip  = function( cb ) {
        var animate = true;
        if ( !$.isFunction(cb) ) {
            animate = cb;
            cb = false;
        }
        
        var tt = $(".uc-tooltip:visible");
        if ( animate ) {
            tt.stop(true, true).fadeOut(HIDE_ANIMATION, function() {
                if (cb) cb.apply(this, arguments);
                hiding = false;
            });
        }
        else {
            tt.hide();
            hiding = false;
        }
    };
    
     var moveCurrentTooltip  = function( x, y ) {
        var tt = $(".uc-tooltip[data-uid=" + currentTooltip + "]");
        tt.css({
            top: y, left: x
        });
    };
    
    $(document).on("mousemove.ucarousel", function(e) {
        $(".uc-tooltip").css({top: e.pageY, left: e.pageX});
        
        if ( $(e.target).is("[data-uid]") ) {
            var uid = $(e.target).data("uid");
            var tt = $(".uc-tooltip[data-uid=" + uid + "]");
            
            if ( currentTooltip !== uid ) {
                clearTO(showTO);
                clearTO(hideTO);

                currentTooltip = uid;
                showing = currentTooltip;
                if (hiding && $(".uc-tooltip:visible").length ) {
                    $(".uc-tooltip").hide();
                    showCurrentTooltip(false);
                } else {
                    showTO = window.setTimeout(function() {
                        showCurrentTooltip();
                    }, SHOW_TIMEOUT);
                }
            }
        }
        else if (currentTooltip && !hiding) {
            clearTO(showTO);
            clearTO(hideTO);
            hiding = currentTooltip;
            hideTO = window.setTimeout(function() {
                hideCurrentTooltip(function() {
                    currentTooltip = null;
                }, hiding);
            }, HIDE_TIMEOUT);
        }
        else if ( showing ) {
            clearTO(showTO);
            clearTO(hideTO);
            showing = false;
            hideCurrentTooltip(false);
            currentTooltip = null;
        }
    });
};

Skadate.UserCarousel.ITEM_WIDTH = null;