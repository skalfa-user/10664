<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Chat link
 *
 * @author Alex Ermashev <alexermashev@@gmail.com>
 * @package ow_plugins.videoim.components
 * @since 1.8.1
 */
class VIDEOIM_CMP_ChatLink extends OW_Component
{
    /**
     * Recipient
     *
     * @var integer $recipientId
     */
    protected $recipientId;

    /**
     * Class constructor
     *
     * @param integer $recipientId
     */
    public function __construct($recipientId)
    {
        parent::__construct();

        $this->recipientId = $recipientId;
        $service = VIDEOIM_BOL_VideoImService::getInstance();

        list($isRequestSendAllowed, $errorMessage) =
                $service->isAllowedSendVideoImRequest($this->recipientId, true);

        if ( !$isRequestSendAllowed )
        {
            $this->setVisible(false);
        }
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // init view variables
        $this->assign('recipientId', $this->recipientId);
        $this->assign('baseImagesUrl', OW::getPluginManager()->getPlugin('videoim')->getStaticUrl() . 'images');
    }
}