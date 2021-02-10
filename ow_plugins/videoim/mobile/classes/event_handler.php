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
 * Mobile video IM event handler
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugins.videoim.mobile.classes
 * @since 1.8.1
 */
class VIDEOIM_MCLASS_EventHandler extends VIDEOIM_CLASS_AbstractBaseEventHandler
{
    /**
     * Class instance
     *
     * @var VIDEOIM_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Get instance
     *
     * @return VIDEOIM_MCLASS_EventHandler
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
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->genericInit();

        // force usage of SSL
        OW::getApplication()->addHttpsHandlerAttrs('VIDEOIM_MCTRL_VideoIm', 'chatWindow');

        $em = OW::getEventManager();

        // init videoIm js
        $em->bind(OW_EventManager::ON_FINALIZE, array($this, 'initVideoImRequest'));

        // generate a profile action toolbar
        $em->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileActionToolbar'));
    }

    /**
     * Init video IM request
     *
     * @return void
     */
    public function initVideoImRequest()
    {
        parent::initVideoImRequestJs(true);
    }
}