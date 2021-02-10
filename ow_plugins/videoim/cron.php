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
 * VideoIm cron job.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow.ow_plugins.videoim
 * @since 8.1
 */
class VIDEOIM_Cron extends OW_Cron
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteExpiredNotifications', 60);
    }

    /**
     * Do nothing
     */
    public function run()
    {}

    /**
     * Delete expired notifications
     */
    public function deleteExpiredNotifications()
    {
        VIDEOIM_BOL_VideoImService::getInstance()->deleteExpiredNotifications();
    }
}