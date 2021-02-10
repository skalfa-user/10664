/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
(function( $, logic )
{
    this.OW.bind('protectedphotos.start', logic.bind(this, $));
}.call(window, jQuery, function( $, formName, content )
{
    content = content || this.document.getElementById('protectedphotos-content');

    var UserItem = (function()
    {
        function UserItem( node )
        {
            this.node = $(node);
        }

        UserItem.prototype.getId = function()
        {
            return this.node.data('id');
        };

        UserItem.prototype.getDisplayName = function()
        {
            return this.node.data('display-name');
        };

        UserItem.prototype.getSrc = function()
        {
            return this.node.data('src');
        };

        return UserItem;
    }());

    var BaseList = (function()
    {
        function BaseList( listManager, content )
        {
            this.manager = listManager;
            this.content = content;
            this.storage = {};
        }

        BaseList.prototype.has = function( id )
        {
            return this.storage.hasOwnProperty(id);
        };

        BaseList.prototype.get = function( id )
        {
            return this.storage[id] || null;
        };

        BaseList.prototype.add = function( user )
        {
            this.storage[user.getId()] = user;
        };

        BaseList.prototype.delete = function( id )
        {
            delete this.storage[id];
        };

        BaseList.prototype.storeFromNodeList = function( nodeList )
        {
            nodeList.each(function( index, node )
            {
                this.add(new UserItem(node));
            }.bind(this));
        };

        BaseList.prototype.bindHandler = function( selector )
        {
            $(this.content).on('click', selector, function( event )
            {
                this.itemClickHandler(event);
            }.bind(this));
        };

        BaseList.prototype.itemClickHandler = function()
        {
            throw new Error('Not implemented', 'Class must implement method "itemClickHandler"');
        };

        BaseList.prototype.changePrivacyHandler = function()
        {
            throw new Error('Not implemented', 'Class must implement method "changePrivacyHandler"');
        };

        return BaseList;
    }());

    var FriendList = (function( _super )
    {
        extend(FriendList, _super);

        function FriendList()
        {
            FriendList._super.apply(this, arguments);

            this.storeFromNodeList($('.ow_privacy_userlist_main_item', this.content));
            this.bindHandler('.ow_privacy_userlist_main_item');
        }

        FriendList.prototype.itemClickHandler = function( event )
        {
            this.manager.toggleFriendList(
                this.get($(event.currentTarget).data('id'))
            );
        };

        FriendList.prototype.toggleCheckBox = function( id, checked )
        {
            if ( !id || !this.has(id) ) return;

            $('.ow_privacy_userlist_checkbox', this.get(id).node).prop('checked', !!checked);
        };

        FriendList.prototype.changePrivacyHandler = function( privacy )
        {
            switch ( privacy )
            {
                case 'individual_friends':
                    this.addScroll();
                    break;
                default:
                    this.removeScroll();
                    break;
            }
        };

        FriendList.prototype.addScroll = function()
        {
            OW.addScroll(this.content.find('.ow_privacy_userlist_main'));
        };

        FriendList.prototype.updateScroll= function()
        {
            OW.updateScroll(this.content.find('.ow_privacy_userlist_main'));
        };

        FriendList.prototype.removeScroll = function()
        {
            OW.removeScroll(this.content.find('.ow_privacy_userlist_main'));
        };

        return FriendList;
    }(BaseList));

    var SelectedFriendList = (function( _super )
    {
        extend(SelectedFriendList, _super);

        function SelectedFriendList()
        {
            SelectedFriendList._super.apply(this, arguments);

            this.storeFromNodeList($('.ow_privacy_userlist_items .ow_privacy_userlist_selected_item', this.content));
            this.bindHandler('.ow_privacy_userlist_selected_item');
        }

        SelectedFriendList.prototype.itemClickHandler = function( event )
        {
            this.manager.removeSelectedFriend(
                this.get($(event.currentTarget).data('id'))
            );
        };

        SelectedFriendList.prototype.toggleUser = function( user )
        {
            if ( user == null ) return;

            this.has(user.getId()) ? this.removeFromList(user) : this.addToList(user);
        };

        SelectedFriendList.prototype.addToList = function( user )
        {
            var newUser = new UserItem(this.createClone(user));

            $('.ow_privacy_userlist_selected_content', this.content).append(newUser.node);
            this.add(newUser);
        };

        SelectedFriendList.prototype.removeFromList = function( user )
        {
            var id = user.getId();

            this.get(id).node.detach();
            this.delete(id);
        };

        SelectedFriendList.prototype.createClone = function( user )
        {
            var clone = $('.ow_hidden .ow_privacy_userlist_selected_item', this.content).clone();

            clone.data({
                'id': user.getId(),
                'display-name': user.getDisplayName(),
                'src': user.getSrc()
            });
            clone.find('.ow_privacy_userlist_item_avatar img').attr({
                src: user.getSrc(),
                alt: user.getDisplayName(),
                title: user.getDisplayName()
            });
            clone.find('.ow_privacy_userlist_item_info_string b').text(user.getDisplayName());

            return clone;
        };

        SelectedFriendList.prototype.getIds = function()
        {
            return Object.keys(this.storage);
        };

        SelectedFriendList.prototype.changePrivacyHandler = function( privacy )
        {
            switch ( privacy )
            {
                case 'individual_friends':
                    this.addScroll();
                    break;
                default:
                    this.removeScroll();
                    break;
            }
        };

        SelectedFriendList.prototype.addScroll = function()
        {
            OW.addScroll(this.content.find('.ow_privacy_userlist_items'));
        };

        SelectedFriendList.prototype.updateScroll = function()
        {
            OW.updateScroll(this.content.find('.ow_privacy_userlist_items'));
        };

        SelectedFriendList.prototype.removeScroll = function()
        {
            OW.removeScroll(this.content.find('.ow_privacy_userlist_items'));
        };

        return SelectedFriendList;
    }(BaseList));

    var individualFriendListManager = (function( content )
    {
        function IndividualFriendListManager( content, formName )
        {
            this.content = $(content);
            this.formName = formName;
            this.friendList = new FriendList(this, $('.ow_pass_protected_userlist', this.content));
            this.selectedFriendList = new SelectedFriendList(this, $('.ow_privacy_userlist_selected', this.content));

            this.visibilityByPrivacy(owForms[this.formName].getElement('ppp-privacy').getValue());
        }

        IndividualFriendListManager.prototype.toggleFriendList = function( user )
        {
            this.selectedFriendList.toggleUser(user);
            this.selectedFriendList.updateScroll();
            this.toggleFriendListCheckBox(user);
        };

        IndividualFriendListManager.prototype.addSelectedFriend = function( user )
        {
            if ( !user || this.selectedFriendList.has(user.getId()) ) return;

            this.selectedFriendList.addToList(user);
            this.selectedFriendList.updateScroll();
            this.toggleFriendListCheckBox(user);
        };

        IndividualFriendListManager.prototype.removeSelectedFriend = function( user )
        {
            this.selectedFriendList.removeFromList(user);
            this.selectedFriendList.updateScroll();
            this.toggleFriendListCheckBox(user);
        };

        IndividualFriendListManager.prototype.toggleFriendListCheckBox = function( user )
        {
            var id = user.getId();

            this.friendList.toggleCheckBox(id, this.selectedFriendList.has(id));
            owForms[this.formName].getElement('ppp-selected-list').setValue(this.selectedFriendList.getIds().join());
        };

        IndividualFriendListManager.prototype.getFriend = function( id )
        {
            return this.friendList.get(id);
        };

        IndividualFriendListManager.prototype.isFriendSelected = function( id )
        {
            return this.selectedFriendList.has(id);
        };

        IndividualFriendListManager.prototype.visibilityByPrivacy = function( privacy )
        {
            switch ( privacy )
            {
                case 'individual_friends':
                    $('.ow_privacy_context', this.content).show();
                    $('#ppp-password', this.content).hide();
                    $('#photo-album-form', this.content).addClass('ow_privacy_mode_width');
                    break;
                case 'password':
                    $('.ow_privacy_context', this.content).hide();
                    $('#ppp-password', this.content).show();
                    $('#photo-album-form', this.content).addClass('ow_privacy_mode_width');
                    break;
                default:
                    $('.ow_privacy_context', this.content).hide();
                    $('#ppp-password', this.content).hide();
                    $('#photo-album-form', this.content).removeClass('ow_privacy_mode_width');
                    break;
            }

            this.friendList.changePrivacyHandler(privacy);
            this.selectedFriendList.changePrivacyHandler(privacy);
        };

        return new IndividualFriendListManager(content, formName);
    }(content, formName));

    this.OW.bind('search_item_click', function( item )
    {
        individualFriendListManager.addSelectedFriend(
            individualFriendListManager.getFriend($(item).data('id'))
        );
    });

    this.OW.bind('protectedphotos_change_privacy', function( node )
    {
        this.visibilityByPrivacy(node.getAttribute('data-privacy'));
    }.bind(individualFriendListManager));

    this.OW.bind('photo.albumEditClick', function()
    {
        individualFriendListManager.friendList.removeScroll();
        individualFriendListManager.selectedFriendList.removeScroll();
        individualFriendListManager.visibilityByPrivacy(owForms[formName].getElement('ppp-privacy').getValue());
    });

    this.OW.bind('photo.ready_fake_album', function( values )
    {
        if ( !$.isPlainObject(values) || $.isEmptyObject(values) ) return;

        var form = $('form[name=' + formName + ']');

        if ( form.length === 0 ) return;

        $('[data-privacy=' + values['ppp-privacy'] + ']', form).trigger('click');

        switch ( values['ppp-privacy'] )
        {
            case 'individual_friends':
                $('.ow_privacy_userlist_selected_content .ow_privacy_userlist_selected_item', form).trigger('click');
                values['ppp-selected-list'].split(',').forEach(function( userId )
                {
                    $('.ow_privacy_userlist_main_item[data-id=' + userId +']', form).trigger('click');
                });
                break;
            case 'password':
                $('[name=ppp-password]', form).val(values['ppp-password']);
                break;
        }
    });

    function extend( ctor, superCtor )
    {
        ctor._super = superCtor;
        ctor.prototype = Object.create(superCtor.prototype, {
            constructor: {
                value: ctor,
                enumerable: false,
                writable: true,
                configurable: true
            }
        });
    }
}));
