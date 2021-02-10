<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Pryadkin Sergey <GiperProger@gmai.com>
 * @package ow_plugins.membership.classes
 * @since 1.8.0
 */
class USERCREDITS_MCLASS_EventHandler
{
    /**
     * @var USERCREDITS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return USERCREDITS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }


    public function init()
    {
        OW::getEventManager()->bind('base.collect_subscribe_menu', array($this, 'getPluginForMenu'));
    }

    public function getPluginForMenu( BASE_CLASS_EventCollector $event )
    {
        $event->add(
            array(
                'label' => OW::getLanguage()->text('usercredits', 'credits'),
                'url' => OW::getRouter()->urlForRoute('usercredits.buy_credits'),
                'iconClass' => 'ow_ic_calendar',
                'key' => 'usercredits',
                'order' => 2
            )
        );
    }





}