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
 * Video IM controller
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugin.videoim.mobile.controllers
 * @since 1.8.1
 */
class VIDEOIM_MCTRL_VideoIm extends VIDEOIM_CTRL_VideoIm
{
    /**
     * Chat window
     */
    public function chatWindow()
    {
        // get vars
        $recipientId = !empty($_GET['recipientId']) ? (int) $_GET['recipientId'] : -1;
        $sessionId = !empty($_GET['sessionId']) ? (string) $_GET['sessionId'] :null;
        $isInitiator = !empty($_GET['initiator']) ? 1 : 0;

        // check user existing
        if ( null === BOL_UserService::getInstance()->findUserById($recipientId) )
        {
            throw new Redirect404Exception();
        }

        // clear all old notifications
        if ( $isInitiator )
        {
            $this->service->deleteUserNotifications(OW::getUser()->getId(), $recipientId);
        }

        // include necessary js and css files
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
                getPlugin('videoim')->getStaticCssUrl() . 'videoim_mobile.css?build=' . $this->service->getPluginBuild());

        $this->includeBaseResources($recipientId, $isInitiator, $sessionId, 1);

        // change the view
        $this->setTemplate(OW::getPluginManager()->getPlugin('videoim')->getMobileCtrlViewDir() . 'video_im_chat_window.html');
    }
}