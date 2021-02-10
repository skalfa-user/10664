<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks.components
 * @since 1.0
 */
class WINKS_CMP_ConsoleWinkRequests extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        parent::__construct( OW::getLanguage()->text('winks', 'console_winks_title'), WINKS_CLASS_ConsoleEventHandler::CONSOLE_ITEM_KEY );

        $this->addClass('ow_friend_request_list');
    }

    public function initJs()
    {
        parent::initJs();

        OW::getLanguage()->addKeyForJs('winks', 'console_wink_accept_item');

        OW::getDocument()->addStyleDeclaration('.wink_btn_box{height:15px}');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('winks')->getStaticJsUrl() . 'winks.js');
        OW::getDocument()->addOnloadScript(
            ';Winks = new OW_Winks(' . json_encode(array('key' => $this->getKey(), 'rsp' => OW::getRouter()->urlForRoute('winks.rsp'))) . ');'
        );
    }
}
