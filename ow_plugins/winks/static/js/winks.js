/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks
 * @since 1.0
 */
OW_Winks = function( params )
{
    var listLoaded = false;

    var model = OW.Console.getData(params.key);
    var list = OW.Console.getItem(params.key);
    var counter = new OW_DataModel();

    counter.addObserver(this);

    this.onDataChange = function( data )
    {
        var newCount = data.get('new');
        var counterNumber = newCount > 0 ? newCount : data.get('all');

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 )
        {
            list.showItem();
        }
    };

    list.onHide = function()
    {
        counter.set('new', 0);
        list.getItems().removeClass('ow_console_new_message');

        model.set('counter', counter.get());
    };

    list.onShow = function()
    {
        if ( !listLoaded )
        {
            this.loadList();
            listLoaded = true;
        }
    };

    model.addObserver(function()
    {
        if ( !list.opened )
        {
            counter.set(model.get('counter'));
        }
    });


    this.accept = function( key, userId, partnerId )
    {
        var item = list.getItem(key);
        var c = {};

        this.send('accept', userId, partnerId, function(data)
        {
            if ( !data || data.result === false )
            {
                OW.error(data.msg);
                
                return;
            }
            
            item.find('.ow_console_invt_toolbar').removeClass('ow_preloader');

            if ( data.onclick )
            {
                $(document.getElementById('send-message-' + key)).attr('onclick', data.onclick).show();
                OW.addScript(data.onclick);
            }
            else if ( data.url ) 
            {
                $(document.getElementById('send-message-' + key)).attr('href', data.url).show();
            }
        });
        
        $('.ow_console_invt_toolbar > a', item).hide();
        item.find('.ow_console_invt_toolbar').addClass('wink_btn_box ow_preloader');
        
        OW.trigger('winks.onChangeStatus', ['accept', userId, partnerId]);
        
        if ( item.hasClass('ow_console_new_message') )
        {
            c["new"] = counter.get("new") - 1;
        }
        
        c["all"] = counter.get("all") - 1;
        counter.set(c);

        return this;
    };

    this.ignore = function( key, userId, partnerId )
    {
        var item = list.getItem(key);
        var c = {};

        this.send('ignore', userId, partnerId, function(data)
        {
            if ( data && data.result === false )
            {
                OW.error(data.msg);
            }
        });
        
        OW.trigger('winks.onChangeStatus', ['ignore', userId, partnerId]);
        
        if ( item.hasClass('ow_console_new_message') )
        {
            c["new"] = counter.get("new") - 1;
        }
        
        c["all"] = counter.get("all") - 1;
        counter.set(c);

        list.removeItem(item);
        OW.info(OW.getLanguageText('winks', 'msg_ignore_request'));
        
        return this;
    };

    this.sendMessage = function( userId, partnerId, conversationId)
    {
        if ( userId === undefined || partnerId === undefined || conversationId === undefined )
        {
            return false;
        }
        
        OW.trigger('winks.onSendMessage', [userId, partnerId, conversationId]);
    };

    this.send = function( status, userId, partnerId, callback )
    {
        $.ajax({
            url: params.rsp,
            type: 'POST',
            cache: false,
            dataType: 'json',
            data:
            {
                funcName: 'changeStatus',
                status: status,
                userId: userId,
                partnerId: partnerId
            },
            success: callback
        });
    };
};
