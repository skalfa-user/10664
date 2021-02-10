<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
function badwords_core_after_dispatch( OW_Event $event )
{
    $handler = OW::getRequestHandler()->getHandlerAttributes();
    
    if ( !array_key_exists('ADMIN_CTRL_Abstract', class_parents($handler[OW_RequestHandler::ATTRS_KEY_CTRL])) )
    {
        BADWORDS_CLASS_HtmlDocument::getInstance()->cleareContent();
    }
}
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, 'badwords_core_after_dispatch');

OW::getEventManager()->bind('mobile.notifications.on_item_render', array(BADWORDS_CLASS_HtmlDocument::getInstance(), 'cleareMobilenotificationsItem'));

OW::getEventManager()->bind('feed.on_item_render', array(BADWORDS_CLASS_HtmlDocument::getInstance(), 'bindOnItemRender'));
