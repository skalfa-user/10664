<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Bookmarks Cron Job
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks
 * @since 1.0
 */
class BOOKMARKS_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();
        
        $this->addJob('sendNotify', 60);
    }

    public function run()
    {

    }

    public function sendNotify()
    {
        $interval = (int)OW::getConfig()->getValue('bookmarks', 'notify_interval');
        
        if ( empty($interval) )
        {
            return;
        }

        $service = BOOKMARKS_BOL_Service::getInstance();
        $activityStamp = ($interval * 86400);
        $timestamp = time() - $activityStamp;
        $service->cleareExpiredNotifyLog($timestamp);
        
        $users = $service->findUserIdListForNotify($timestamp, 0, BOOKMARKS_BOL_Service::COUNT_CRON_USER);
        
        if ( !empty($users) )
        {
            foreach ( $users as $user )
            {
                if ( $service->sendNotifyForUser($user) )
                {
                    $service->notifyLogSave($user);
                }
            }
        }
    }
}
