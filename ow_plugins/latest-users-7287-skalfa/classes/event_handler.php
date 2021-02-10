<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class LATESTUSERS_CLASS_EventHandler
{
    const NEEDED_ACCOUNT_TYPE = '808aa8ca354f51c5a3868dad5298cd72';

    /**
     * Singleton instance.
     *
     * @var LATESTUSERS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return LATESTUSERS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * LATESTUSERS_CLASS_EventHandler constructor.
     */
    private function __construct()
    {}

    /**
     * Init
     */
    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, [$this, 'afterInit']);
    }

    /**
     * After plugins init event
     */
    public function afterInit()
    {
        OW::getEventManager()->bind('base.query.user_filter', [$this, 'onUserFilter']);
        OW::getEventManager()->bind('base.avatars.get_list', [$this, 'onAvatarsGetList']);
    }

    /**
     * Process user filter
     *
     * @param BASE_CLASS_QueryBuilderEvent $event
     *
     * @throws Redirect404Exception
     */
    public function onUserFilter( BASE_CLASS_QueryBuilderEvent $event )
    {
        $params = $event->getParams();

        if ( $params['method'] !== 'BOL_UserDao::findList' )
        {
            return;
        }

        $route = OW::getRouter()->route();

        if ( $route && $route['controller'] == 'BASE_CTRL_ComponentPanel' && $route['action'] == 'index')
        {
            $data = $event->getData();

            $sqlWhere = "( `u`.`accountType` = '" . self::NEEDED_ACCOUNT_TYPE . "' )";
            $data['where'][] = $sqlWhere;

            $event->setData($data);
        }
    }

    /**
     * Process avatars get list event
     *
     * @param OW_Event $event
     *
     * @throws Redirect404Exception
     */
    public function onAvatarsGetList( OW_Event $event )
    {
        $route = OW::getRouter()->route();

        if ( $route && $route['controller'] == 'BASE_CTRL_ComponentPanel' && $route['action'] == 'index')
        {
            $avatarService = BOL_AvatarService::getInstance();

            $params = $event->getParams();

            $size = 2; // big avatar
            $userIds = $params['userIds'];

            $urlsList = array_fill(0, count($userIds), $avatarService->getDefaultAvatarUrl($size));
            $urlsList = array_combine($userIds, $urlsList);

            $avatars =  BOL_AvatarDao::getInstance()->getAvatarsList($userIds);

            foreach ( $avatars as $avatar )
            {
                $urlsList[$avatar->userId] =  $avatarService->getAvatarUrlByAvatarDto($avatar, $size);
            }

            $event->setData($urlsList);
        }
    }
}
