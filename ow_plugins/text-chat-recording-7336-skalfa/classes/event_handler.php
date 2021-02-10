<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Class SKTEXTCR_CLASS_EventHandler
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CLASS_EventHandler
{
    /**
     * @var OW_EventManager
     */
    private $eventManager;

    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance instanceof self )
        {
            return self::$classInstance;
        }

        return self::$classInstance = new self();
    }

    public function __construct()
    {

    }

    public function init()
    {
        $this->eventManager = OW::getEventManager();

        $this->eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, [$this, 'afterInit']);
    }

    public function afterInit()
    {
        if ( !OW_DEBUG_MODE )
        {
            $this->eventManager->bind('admin.plugins_list_view', [$this, 'pluginsListView']);
        }
    }

    public function pluginsListView( OW_Event $event )
    {
        $data = $event->getData();

        if ( !empty($data['active']) || !empty($data['inactive']) )
        {
            if ( isset($data['active'][SKTEXTCR_BOL_Service::PLUGIN_KEY]['un_url']) )
            {
                $data['active'][SKTEXTCR_BOL_Service::PLUGIN_KEY]['un_url'] = null;
            }
        }

        $event->setData($data);
    }
}