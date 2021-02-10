/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.static
 */
HINT = (function() {
    
    var _prototype, _delegate = {}, _CORNER_OFFSET = 7, _hintShown = null;

    _prototype = $('.hint-container', '#hint-prototype');

    var _bind, _clearTimeout, _setTimeout;

    _clearTimeout = function( timeOut ) {
        if ( timeOut ) window.clearTimeout(timeOut);
    };

    _setTimeout = function( fnc, time ) {
        return window.setTimeout(fnc, time);
    };

    _bind= function( fnc, obj ) {
        fnc = fnc || function(){};
        obj = obj || window;

        return function() {
            return fnc.apply(obj, arguments);
        };
    };

    _delegate.start = function() {
        this.show();
    };

    _delegate.stop = function() {
        this.hide();
    };

    _delegate.enter = function() {
        _clearTimeout(this.timeouts.stop);
    };

    _delegate.leave = function() {
        this.stop();
    };

    var Hint = function( delegate, target ) {

        this.hint = _prototype.clone().hide();
        this.body = this.hint.find('.hint-body');
        this.topCornerBody = this.hint.find('.hint-top-corner-wrap .hint-corner');
        this.bottomCornerBody = this.hint.find('.hint-bottom-corner-wrap .hint-corner');
        this.rightCornerBody = this.hint.find('.hint-right-corner-wrap .hint-corner');
        
        this.visible = false;
        this._targetChanged = false;

        $('body').append(this.hint);

        this.orientationClass = 'hint-top-left';

        this.timeouts = {
            start: null,
            stop: null,
            enter: null,
            leave: null
        };

        this.delegate = {};

        if ( delegate ) this.setDelegate(delegate);
        if ( target ) this.setTarget(target);

        this.hint.on('mouseenter.hint', _bind(_delegate.enter, this));
        this.hint.on('mouseleave.hint', _bind(_delegate.leave, this));

        _bind(this.delegate.construct, this)(); // Delegate method call
    };

    Hint.prototype.START_TIMEOUT = 700;
    Hint.prototype.STOP_TIMEOUT = 200;
    Hint.prototype.SWITCH_TIMEOUT = 400;

    Hint.prototype.setTarget = function( target ) {
        var oldTarget = this.target;

        this.target = $(target);
        this._targetChanged = true;

        this.target.data("hint", this);

        _bind(this.delegate.targetChange, this)(oldTarget); // Delegate method call
        
        if ( this.target.data("hint-zindex") ) {
            this.hint.css("z-index", this.target.data("hint-zindex"));
        }

        return this;
    };

    Hint.prototype.setDelegate = function( delegate ) {
        this.delegate = delegate;

        return this;
    };
    
    Hint.prototype.getDelegate = function() {
        return this.delegate;
    };

    Hint.prototype.getSize = function() {
        return {
            width: this.hint.width(),
            height: this.hint.height()
        };
    };

    Hint.prototype.getPosition = function( target ) {
        var offset, $window;

        target = target || this.target;
        offset = target.offset();
        $window = $(window);

        if ( !offset )
            return null;

        return {
            top: offset.top - $window.scrollTop(),
            left: offset.left - $window.scrollLeft(),
            right: $window.width() - offset.left - $window.scrollLeft(),
            bottom: $window.height() + $window.scrollTop() - offset.top
        };
    };

    Hint.prototype.getOrientation = function( target ) {
        var position, size, orientation;

        target = target || this.target;

        size = this.getSize();
        position = this.getPosition(target);
        if ( !position ) 
            return null;

        orientation = {};
        orientation.top = size.height < position.top - _CORNER_OFFSET;
        orientation.bottom = !orientation.top;
        orientation.right = size.width < position.right;
        orientation.left = !orientation.right;

        return orientation;
    };
    
    Hint.prototype.refreshOrientation = function() {
        var offset, position, targetHeight, targetWidth, cornerOffset, cornerPosition, currentCorner,
            topCorner, bottomCorner, orientation, size, target, innerNodes;

        if ( _bind(this.delegate.beforeRefreshOrientation, this)() === false ) // Delegate method call
            return this;
            
        if ( !this.target ) return this;

        target = this.target;

        this.target.addClass('hint-target');

        if ( this.target.css("display") === "inline" ) {
            innerNodes = this.target.children().filter(function() {
                return $(this).is('img') || $(this).css("display") !== "inline";
            });

            if ( innerNodes.length > 0 ) {
                this.target.addClass('hint-target-block');

                target = innerNodes.first();
            }
        }

        topCorner = this.hint.find('.hint-top-corner-wrap');
        bottomCorner = this.hint.find('.hint-bottom-corner-wrap');

        this.hint.removeClass(this.orientationClass);

        if ( !this.delegate.refreshOrientation ) // Generic behaviour
        {
            targetHeight = target.outerHeight();
            targetWidth = target.outerWidth();

            offset = _bind(this.delegate.getOffset, this)(target); // Delegate method call
            offset = offset || target.offset();

            position = {
                top: offset.top + targetHeight,
                left: offset.left
            };

            size = this.getSize();
            orientation = this.getOrientation(target);
            if (!orientation)
                return this;

            cornerOffset = targetWidth / 2 - 5;
            cornerOffset = cornerOffset < 2 ? 2 : cornerOffset;
            cornerOffset = cornerOffset > size.width / 2 ? size.width / 2 : cornerOffset;

            if ( orientation.top && orientation.left ) {
                this.orientationClass = 'hint-top-left';
                position.top = offset.top - size.height - _CORNER_OFFSET;
                position.left = offset.left - size.width + targetWidth;
                bottomCorner.css('right', cornerOffset);
                currentCorner = bottomCorner;
            } else if ( orientation.top && orientation.right ) {
                this.orientationClass = 'hint-top-right';
                position.top = offset.top - size.height - _CORNER_OFFSET;
                bottomCorner.css('left', cornerOffset);
                currentCorner = bottomCorner;
            } else if ( orientation.bottom && orientation.left ) {
                this.orientationClass = 'hint-bottom-left';
                position.left = offset.left - size.width + targetWidth;
                topCorner.css('right', cornerOffset);
                currentCorner = topCorner;
            } else if ( orientation.bottom && orientation.right ) {
                this.orientationClass = 'hint-bottom-right';
                topCorner.css('left', cornerOffset);
                currentCorner = topCorner;
            }
            
            this.hint.css(position);
        }
        else
        {
            currentCorner = _bind(this.delegate.refreshOrientation, this)(target);
        }

        this.hint.addClass(this.orientationClass);
        this.hint.removeClass('hint-invisible');
        
        if ( currentCorner ) {
            cornerPosition = currentCorner.position();
        }

        this._targetChanged = false;

        _bind(this.delegate.afterRefreshOrientation, this)(orientation, position, cornerPosition, {
            width: targetWidth, height: targetHeight
        }); // Delegate method call

        return this;
    };
    
    
    
    Hint.prototype.show = function() {
        if ( this.visible ) {
            
            if ( this._targetChanged ) {
                this.refreshOrientation();
            }

            return this;
        }

        if ( _bind(this.delegate.beforeShow, this)() === false ) // Delegate method call
            return this;
        
        if ( !this.target ) return this;

        this.hint.show();
        this.refreshOrientation();

        _bind(this.delegate.afterShow, this)(); // Delegate method call
        _hintShown = this;
        this.visible = true;

        return this;
    };

    Hint.prototype.hide = function() {
        if ( !this.visible ) return this;

        if ( _bind(this.delegate.beforeHide, this)() === false ) // Delegate method call
            return this;
        
        _clearTimeout(this.timeouts.stop);

        this.hint.hide();
        _hintShown = null;
        this.visible = false;

        _bind(this.delegate.afterHide, this)(); // Delegate method call

        return this;
    };

    Hint.prototype.start = function() {
        _bind(this.delegate.beforeStart, this)(); // Delegate method call

        if ( !this.target ) return this;

        _clearTimeout(this.timeouts.stop);
        _clearTimeout(this.timeouts.start);

        this.timeouts.start = _setTimeout(_bind(_delegate.start, this), !!_hintShown ? this.SWITCH_TIMEOUT : this.START_TIMEOUT);

        _bind(this.delegate.afterStart, this)(); // Delegate method call

        return this;
    };

    Hint.prototype.stop = function() {
        _bind(this.delegate.beforeStop, this)(); // Delegate method call

        _clearTimeout(this.timeouts.start);
        _clearTimeout(this.timeouts.stop);
        this.timeouts.stop = _setTimeout(_bind(_delegate.stop, this), this.STOP_TIMEOUT);

        _bind(this.delegate.afterStop, this)(); // Delegate method call

        return this;
    };

    Hint.prototype.setContent = function( content ) {
        this.body.empty().append(content);

        _bind(this.delegate.contentChange, this)(); // Delegate method

        this.hint.addClass('hint-invisible');
        _setTimeout(_bind(this.refreshOrientation, this), 0);

        return this;
    };

    Hint.prototype.setTopCorner = function( content ) {
        this.topCornerBody.empty().append(content);

        return this;
    };

    Hint.prototype.setBottomCorner = function( content ) {
        this.bottomCornerBody.empty().append(content);

        return this;
    };
    
    Hint.prototype.setRightCorner = function( content ) {
        this.rightCornerBody.empty().append(content);

        return this;
    };

    return {
        createHint: function( delegate, node ) {
            if ( !node ) {
                return new Hint(delegate);
            }

            var hint = this.getHint(node);

            if ( hint ) {
                hint.hide();
            }

            return new Hint(delegate, $(node));
        },

        getHint: function( node ) {
            var target = $(node);

            if ( target.data("hint") ) {
                target.data("hint").setTarget(target);

                return target.data("hint");
            }

            return null;
        },

        getHintOrCreate: function( delegate, node ) {
            var hint = this.getHint(node);
            if ( hint ) {
                if ( delegate ) {
                    hint.setDelegate(delegate);
                }

                return hint;
            }

            return this.createHint(delegate, node);
        },

        isAnyShown: function() {
            return !!_hintShown;
        },

        getShown: function() {
            return _hintShown;
        },

        init: function() {

        }
    };
})();

HINT.UTILS = (function() {
    var _settings = {};

    var _queryCallBack;

    _queryCallBack = function( response ) {
        if ( response.error ) {
            OW.error(response.error);
        }

        if ( response.info ) {
            OW.info(response.info);
        }

        if ( response.reload ) {
            window.location.reload();
        }

        if ( response.redirect ) {
            window.location.href = response.redirect;
        }
    };

    return {
        init: function( settings ) {
            _settings = settings;
        },

        query: function( command, params, callBack) {
            $.getJSON(_settings.queryRsp, {
                "command": command,
                "params": JSON.stringify(params)
            }, function( r ) {
                if ( $.isFunction(callBack) ) {
                    callBack(r);
                }

                _queryCallBack(r);
            });
        },

        toggleText: function( node, t1, t2 ) {
            node = $(node);
            node.text(node.text() == t1 ? t2 : t1);
        }
    };
})();

HINT.Launcher = (function() {

    var _options = {}, _masks = [], _cache = {}; // Private variables
    var Delegate, ConsoleDelegate; // Private constructors
    var _query, _testUrl; // Private functions

    _testUrl = function( url ) {
        var i;
        for ( i = 0; i < _masks.length; i++ ) {
            var mask = new RegExp(_masks[i]);
            if ( mask.test(url) ) {
                return true;
            }
        }

        return false;
    };

    _query = function( url, success, error ) {
        $.getJSON(_options.rsp, {"url": url}, function( r ) {
            if ( !r || !r.markup ) {
                error();

                return;
            }

            var markup = r.markup;
            var contentHtml = markup.content.body, $contentHtml = $(contentHtml),
                $topCorner = null, $bottomCorner = null, $rightCorner = null;

            if ( !$contentHtml.length )
            {
                contentHtml = '<span>' + contentHtml + '</span>';
                $contentHtml = $(contentHtml);
            }

            $bottomCorner = markup.content.bottomCorner
                ? $(markup.content.bottomCorner)
                : "";

            $topCorner = markup.content.topCorner
                ? $(markup.content.topCorner)
                : "";
                
            $rightCorner = markup.content.rightCorner
                ? $(markup.content.rightCorner)
                : "";
                
            success($contentHtml, $topCorner, $bottomCorner, $rightCorner);

            OW.bindAutoClicks($contentHtml);
            OW.bindTips($contentHtml);

            if (markup.styleSheets)
            {
                $.each(markup.styleSheets, function(i, o)
                {
                    OW.addCssFile(o);
                });
            }

            if (markup.styleDeclarations)
            {
                OW.addCss(markup.styleDeclarations);
            }

            if (markup.beforeIncludes)
            {
                OW.addScript(markup.beforeIncludes);
            }

            if (markup.scriptFiles)
            {
                OW.addScriptFiles(markup.scriptFiles, function()
                {
                    if (markup.onloadScript)
                    {
                        OW.addScript(markup.onloadScript);
                    }
                });
            }
            else
            {
                if (markup.onloadScript)
                {
                    OW.addScript(markup.onloadScript);
                }
            }
        });
    };


    Delegate = function( url ) {
        this.url = url;
    };

    Delegate.prototype.construct = function() {
        var self = this;

        _query(this.delegate.url, function( content, topCorner, bottomCorner, rightCorner ) {
            self.setContent(content);
            if ( topCorner ) self.setTopCorner(topCorner);
            if ( bottomCorner ) self.setBottomCorner(bottomCorner);
            if ( rightCorner ) self.setRightCorner(rightCorner);
        }, function() {
            this.stop();
        });
    };

    Delegate.prototype.afterRefreshOrientation = function( orientation, position, cornerPosition ) {
        if ( cornerPosition ) {
            this.topCornerBody.find(".uhint-corner-cover").css("margin-left", -(cornerPosition.left + 0.5));
        }
    };

    Delegate.prototype.beforeShow = function() {
        var self = this;
        
        if ( HINT.isAnyShown() && HINT.getShown() !== this ) HINT.getShown().hide();
        
        if ( OW.getActiveFloatBox() ) {
            this.hint.removeClass("hint-from-floatbox").addClass("hint-from-floatbox");
            
            OW.getActiveFloatBox().bind("close", function() {
                self.hint.removeClass("hint-from-floatbox");
                self.hide();
            });
        }
    };

    Delegate.prototype.beforeStart = function() {
        if ( HINT.isAnyShown() && HINT.getShown().timeouts.stop ) {
            window.clearTimeout(HINT.getShown().timeouts.stop);
        }
    };

    Delegate.prototype.beforeStop = function() {
        if ( HINT.isAnyShown() && HINT.getShown() !== this ) {
            HINT.getShown().stop();
        }
    };
    
    // Custom delegate for console tooltips
    ConsoleDelegate = function( url ) {
        Delegate.call(this, url);
        
        /*this.afterRefreshOrientation = function( orientation, position, cornerPosition ) {
            Delegate.prototype.afterRefreshOrientation.call(this, orientation, position, cornerPosition);
            
            this.hint.addClass("hint-console-hint");
        };*/
        
        /*this.getOffset = function( target ) {
            return this.getPosition(target);
        };*/
        
        this.refreshOrientation = function() {
            var size, offset, position = {}, consoleItem;

            //this.orientationClass = 'hint-right-top';
            this.orientationClass = 'hint-console-hint';
            
            consoleItem = this.target.parents(".ow_console_list_item:eq(0)");
            size = this.getSize();
            offset = this.getPosition(consoleItem);
            
            if ( !offset )
                return null;
            
            position.top = offset.top;
            position.left = offset.left - size.width - 12;
            
            this.hint.css(position);
        };
    };
    ConsoleDelegate.prototype = Delegate.prototype;
    
    return {

        getHint: function( target ) {
            var delegateConstructor;
            
            var hint = HINT.getHint(target);

            if ( !hint && _cache[target.href] ) {
                hint = _cache[target.href];
                hint.setTarget(target);
            }
            
            if ( hint )
            {
                delegateConstructor = $(target).is(".ow_console a") 
                    ? ConsoleDelegate
                    : Delegate;
                
                hint.setDelegate(new delegateConstructor(target.href));
            }

            return hint;
        },

        createHint: function( target ) {
            var delegateConstructor;
            
            delegateConstructor = $(target).is(".ow_console a") 
                ? ConsoleDelegate
                : Delegate;
    
            var hint = HINT.createHint(new delegateConstructor(target.href), target);
            _cache[target.href] = hint;

            return hint;
        },

        init: function(options, masks, selectors) {
            var selector = selectors.join(', ');
            _options = options;
            _masks = masks;

            HINT.init();

            var queryTimeOut;

            $(document).on('mouseenter.hint', 'a:not(' + selectors.join(", ") + ')', function( event ) {
                var self = this;
                
                if ( !_testUrl(this.href) ) return;

                var hint = HINT.Launcher.getHint(this);

                if ( hint ) {
                    hint.start();
                } else {
                    queryTimeOut = window.setTimeout(function() {
                        HINT.Launcher.createHint(self).start();
                    }, HINT.isAnyShown() ? 50 : 150);
                }

                // Prevents an appearing of the standard tooltip
                var target = $(event.target);
                if ( target.data().tod ) {
                    window.clearTimeout(target.data().tod);
                }
            });

            $(document).on('mouseleave.hint', 'a:not(.hint-container a)', function() {
                if ( !_testUrl(this.href) ) return;

                window.clearTimeout(queryTimeOut);

                var hint = HINT.getHint(this);
                if ( hint ) hint.stop();
            });

            $(document).on('click.hint', 'a:not(.hint-container a)', function() {
                if ( !_testUrl(this.href) ) return;

                var hint = HINT.getHint(this);
                if ( hint ) hint.stop();
            });
        }
    };
})();

HINT.Inviter = function( params ) {
       
    var DefaultEventInviter = function() {
        var self = this;
        
        this.floatBox = null;

        OW.bind('base.avatar_user_list_select',
            function( list ) {
                if ( !self.floatBox ) return;
                
                self.floatBox.close();
                self.floatBox = null;
                
                $.ajax({
                    type: 'POST',
                    url: params.inviteRsp,
                    data: {
                        eventId: params.eventId,
                        userIdList: JSON.stringify(list)
                    },
                    dataType: 'json',
                    success : function(data) {
                        if( data.messageType === 'error' ) {
                            OW.error(data.message);
                        }
                        else {
                            OW.info(data.message);
                        }
                    }
                });
            }
        );
        
        this.show = function() {
            this.floatBox = OW.ajaxFloatBox('EVENT_CMP_InviteUserListSelect', [params.eventId], {
                width:600,
                iconClass: 'ow_ic_user',
                title: params.title
            });
        };
    };
    
    var DefaultGroupInviter = function() {
        
        var self = this;
        this.floatBox = null;
        this.userIdList = params.userList;
        
        OW.bind('base.avatar_user_list_select',
            function(list)
            {
                if ( !self.floatBox ) return;
                
                self.floatBox.close();
                self.floatBox = null;

                $.ajax({
                    type: 'POST',
                    url: params.inviteRsp,
                    data: {"groupId": params.groupId, "userIdList": JSON.stringify(list), "allIdList": JSON.stringify(params.userList)},
                    dataType: 'json',
                    success : function(data)
                    {
                        if( data.messageType === 'error' )
                        {
                            OW.error(data.message);
                        }
                        else
                        {
                            OW.info(data.message);
                            self.userIdList = data.allIdList;
                        }
                    }
                });
            }
        );
        
        this.show = function() {
            this.floatBox = OW.ajaxFloatBox('BASE_CMP_AvatarUserListSelect', [this.userIdList],
            {
                width:600,
                height:350,
                iconClass: 'ow_ic_user',
                title: params.title
            });
        };
    };
    
    var inviter;
    
    if ( params["for"] === "event" ) {
        inviter = params.eheader ? new EHEADER.Inviter(params) : new DefaultEventInviter(params);
    } else if ( params["for"] === "group" ) {
        inviter = params.gheader ? new GHEADER.Inviter(params) : new DefaultGroupInviter(params);
    }
    
    return {
        show: function() {
            inviter.show();
        }
    };
};


HINT.AttendContext = function( uniqId, options, changed ) 
{
    var node = $("#" + uniqId);
    var current = node.find("[data-type=current]");
    var currentType = options.status || null;
    var prevStatus = null;
    var tooltip = node.find(".ow_tooltip");
    
    var actions = {
        yes: node.find("[data-type=yes]"),
        no: node.find("[data-type=no]"),
        maybe: node.find("[data-type=maybe]")
    };
    
    var typeIds = {
        yes: 1,
        no: 3,
        maybe: 2
    };
    
    function ajaxSuccess( r )
    {
        if(r.messageType === 'error') {
            OW.error(r.message);
            rollbackStatus();
        } else {
            OW.info(r.message);
            
            changed && changed(currentType, typeIds[currentType]);
            
            if ( r.eventId )
            {
                OW.loadComponent('EVENT_CMP_EventUsers', {eventId: r.eventId}, function( html ){
                    $('.userList').html(html);
                });
            }
        }
    }
    
    function saveType( type )
    {
        $.ajax({
           type: 'POST',
           url: options.rsp,
           data: {
               "eventId": options.eventId,
               "attend_status": typeIds[type]
           },
           success: ajaxSuccess,
           error: rollbackStatus,
           dataType: 'json'
       });
    }
    
    function rollbackStatus()
    {
        if ( !prevStatus ) return;
        
        currentType && actions[currentType].show();
        
        currentType = prevStatus;
        actions[currentType].hide();
        current.text(actions[currentType].text());
        
        tooltip.hide();
    }
    
    function setCurrent( type, save ) {
        if ( currentType ) {
            actions[currentType].show();
        }
        
        actions[type].hide();
        current.text(actions[type].text());
        prevStatus = currentType;
        currentType = type;
        
        saveType(type);
        
        tooltip.hide();
    }
    
    node.on("click", ".hc-action", function() {
        var self = $(this), type = self.data("type");
        setCurrent(type);
    });
};


HINT.GroupHint = (function() {

    function query(rsp, params, callback) {
        return $.ajax({
            type: 'POST',
            url: rsp,
            data: params,
            dataType: 'json',
            success: function(r) {
                if (r.message) {
                    OW.info(r.message);
                }
                
                if (r.error) {
                    OW.info(r.error);
                }
                
                callback && callback(r);
            }
        });
    };

    var proto = {};

    function Constructor( options )
    {
        this.options = {};
        
        this.setOptions(options);
        
        this.groupId = this.options["groupId"];
        this.userId = this.options["userId"];
        
        this.inviter = null;
    }
    
    proto.setOptions = function( options ) {
        $.extend(this.options, options);
    };
    
    proto.flag = function() {
        OW.flagContent(this.options.entityType, this.groupId);
    };
    
    proto.invite = function() {
        this.inviter = this.inviter || new HINT.Inviter({
            inviteRsp: this.options.rsp.invite,
            title: this.options.text.inviteTitle,
            groupId: this.groupId,
            gheader: this.options.gheader ? 1 : 0,
            userList: this.options.inviteUsers,
            "for": "group"
        });
        
        this.inviter.show();
    };
            
    proto.toggleJoin = function() {
        this.options.isGroupUser = !this.options.isGroupUser;
        
        var label = this.options.text[this.options.isGroupUser ? "leave" : "join"];
        $("#" + this.options.joinBtnId).text(label);
        
        query(this.options.rsp.join, {
            groupId: this.groupId,
            userId: this.userId,
            add: this.options.isGroupUser ? 1 : 0
        }, $.proxy(function(r) {
            var inviteBtn = this.options.inviteBtnId 
                    ? $("#" + this.options.inviteBtnId)
                    : null;
                    
            inviteBtn && inviteBtn[r.invite ? "show" : "hide"]();
            
            this.options.followed = r.followed;
            $("#" + this.options.followBtnId)
                    .text(this.options.text[this.options.followed ? "unfollow" : "follow"]);
            
        }, this));
    };
    
    proto.toggleFollow = function() {
        this.options.followed = !this.options.followed;
        
        var label = this.options.text[this.options.followed ? "unfollow" : "follow"];
        $("#" + this.options.followBtnId).text(label);
        
        query(this.options.rsp.follow, {
            groupId: this.groupId,
            userId: this.userId,
            add: this.options.followed ? 1 : 0
        });
    };
    
    Constructor.prototype = proto;
    return Constructor;
})();