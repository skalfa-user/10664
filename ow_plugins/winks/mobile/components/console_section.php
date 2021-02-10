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
 * Console section component
 *
 * @author Kairat Bakytov <kainisoft@gmail.com>
 * @package ow.ow_plugins.winks.mobile.components
 * @since 1.7.6
 */
class WINKS_MCMP_ConsoleSection extends OW_MobileComponent
{
    private $service;
    private $count;

    public function __construct()
    {
        parent::__construct();

        $this->service = WINKS_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();
        $activeModes = $this->service->getActiveModes();

        $count = array(
            $this->service->countWinksForUser($userId, array(WINKS_BOL_WinksDao::STATUS_ACCEPT, WINKS_BOL_WinksDao::STATUS_WAIT), null, $activeModes),
            $this->service->countWinkBackedByUserId($userId, $activeModes)
        );

        $this->count = max($count);

        if ( $this->count <= 0 )
        {
            $this->setVisible(false);
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->addComponent('itemsCmp', new WINKS_MCMP_ConsoleItems($this->count));

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('winks')->getStaticJsUrl() . 'mobile.js');
        OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString(';new OWM_WinksConsole({$params});', array(
            'params' => array(
                'acceptUrl' => OW::getRouter()->urlFor('WINKS_MCTRL_Action', 'accept'),
                'ignoreUrl' => OW::getRouter()->urlFor('WINKS_MCTRL_Action', 'ignore')
            )
        )));
    }
}
