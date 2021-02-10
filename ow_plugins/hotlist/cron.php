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
 * Hotlist cron job.
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.hotlist
 * @since 1.0
 */
class HOTLIST_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('clearExpiredUsers', 60);

    }

    public function run()
    {

    }

    public function clearExpiredUsers()
    {
        HOTLIST_BOL_Service::getInstance()->clearExpiredUsers();
    }

}