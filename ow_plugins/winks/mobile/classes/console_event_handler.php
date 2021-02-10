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
 * @author Kairat Bakytov <kainisoft@gmail.com>
 * @package ow.ow_plugins.winks.mobile.classes
 * @since 1.7.6
 */
class WINKS_MCLASS_ConsoleEventHandler
{
    private static $classInstance;

    const CONSOLE_PAGE_KEY = 'notifications';
    const CONSOLE_SECTION_KEY = 'winks';

    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function collectSections( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_PAGE_KEY )
        {
            $event->add(array(
                'key' => self::CONSOLE_SECTION_KEY,
                'component' => new WINKS_MCMP_ConsoleSection(),
                'order' => 1
            ));
        }
    }

    public function countNewItems( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_PAGE_KEY )
        {
            $service = WINKS_BOL_Service::getInstance();
            $activeModes = json_decode(OW::getConfig()->getValue('mailbox', 'active_modes'));
            $event->add(
                array(self::CONSOLE_SECTION_KEY => $service->countWinksForUser(OW::getUser()->getId(), array(WINKS_BOL_WinksDao::STATUS_WAIT), 0, $activeModes))
            );
        }
    }
    
    public function init()
    {
        $em = OW::getEventManager();
        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_SECTIONS,
            array($this, 'collectSections')
        );

        $em->bind(
            MBOL_ConsoleService::EVENT_COUNT_CONSOLE_PAGE_NEW_ITEMS,
            array($this, 'countNewItems')
        );
    }
}
