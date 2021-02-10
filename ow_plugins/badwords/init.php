<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
OW::getRouter()->addRoute(new OW_Route('badwords.admin', 'badwords/admin', 'BADWORDS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('badwords.admin-rsp', 'badwords/admin/rsp', 'BADWORDS_CTRL_Admin', 'rsp'));

function badwords_core_after_dispatch( OW_Event $event )
{
    $handler = OW::getRequestHandler()->getHandlerAttributes();
    $route = OW::getRouter()->getUsedRoute();
    $exclude = array('mailbox_messages_default');

    if ( !in_array(!empty($route) ? $route->getRouteName() : '', $exclude) && !array_key_exists('ADMIN_CTRL_Abstract', class_parents($handler[OW_RequestHandler::ATTRS_KEY_CTRL])) )
    {
        BADWORDS_CLASS_HtmlDocument::getInstance()->cleareContent();
    }

    $words = BADWORDS_BOL_Service::getInstance()->findAllBadwords();
    OW::getDocument()->addScriptDeclarationBeforeIncludes(';window.badwordsParams = {
        "pattern": ' . (!empty($words['js_pattern']) ? $words['js_pattern'] : '"ё"') . ',
        "replacement": \'' . '<span style="color:' . OW::getConfig()->getValue('badwords', 'censorColor') .'">' . OW::getConfig()->getValue('badwords', 'censorText') . '</span>' . '\'
    };');

    OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('badwords')->getStaticJsUrl() . 'badwords.js');
    OW::getDocument()->addOnloadScript('OW.bind("mailbox.update_message", function( data )
    {
        $("#" + "main_tab_contact_" + data.opponentId + " .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });
    
    OW.bind("mailbox.after_write_mail_message", function( data )
    {
        $("#conversationLog .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });

        $("#conversationLog .ow_mailbox_message_content").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
        
        var subject = $("#conversationSubject");
        
        subject.html(badwords.getCleareContent(subject.html()));
    });
    
    OW.bind("mailbox.dialogLogLoaded", function( data )
    {
        $("#" + "main_tab_contact_" + data.opponentId + " .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });
    
    OW.bind("mailbox.update_chat_message", function( data )
    {
        $("#" + "main_tab_contact_" + data.recipientId + " .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });
    
    OW.bind("mailbox.message", function( data )
    {
        $("#" + "main_tab_contact_" + data.senderId + " .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });
    
    OW.bind("mailbox.update_message", function()
    {
        $("#conversationLog .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });

        $("#conversationLog .ow_mailbox_message_content").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });

    OW.bind("mailbox.conversation_marked_read", function()
    {
        $("#conversationLog .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });

    OW.bind("mailbox.mark_message_read", function()
    {
        $("#conversationLog .ow_dialog_in_item p").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });

    OW.bind("mailbox.history_loaded", function()
    {
        $(".ow_mailbox_message_content,.ow_dialog_in_item p", "#conversationLog").each(function()
        {
            $(this).html(badwords.getCleareContent($(this).html()));
        });
    });

    OW.bind("mailbox.render_conversation_item", function( data )
    {
        var item = data.$el.find(".ow_mailbox_convers_preview");

        if ( item.length )
        {
            item.html(badwords.getCleareContent(item.html()));
        }
    });
    
    OW.bind("onChatAppendMessage",function(message)
    {
        var p=message.find("p");
        p.html(badwords.getCleareContent(p.html()));
    });
    
    OW.bind("consoleAddItem",function(items)
    {
        for(var item in items)
        {
            items[item].html = badwords.getCleareContent(items[item].html);
        }
    });', 9999);
}
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, 'badwords_core_after_dispatch');

function badwords_base_ping_consoleUpdate( OW_Event $event )
{
    if ( OW::getRequest()->isAjax() )
    {
        return;
    }

    $javaScripts = OW::getDocument()->getJavaScripts();

    foreach ( $javaScripts['items'][1000]['text/javascript'] as &$script )
    {
        if ( strpos($script, 'ow_static/plugins/base/js/console.js') !== false )
        {
            $script = OW::getPluginManager()->getPlugin('badwords')->getStaticJsUrl() . 'console.js';

            OW::getDocument()->setJavaScripts($javaScripts);

            break;
        }
    }
}
OW::getEventManager()->bind('base.ping.consoleUpdate', 'badwords_base_ping_consoleUpdate');
OW::getEventManager()->bind('feed.on_item_render', array(BADWORDS_CLASS_HtmlDocument::getInstance(), 'bindOnItemRender'));
