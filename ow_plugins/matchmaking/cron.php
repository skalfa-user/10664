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
 * Matchmaking cron job.
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking
 * @since 1.0
 */
class MATCHMAKING_Cron extends OW_Cron
{
    const CRON_USER_COUNT = 100;

    /**
     *
     * @var MATCHMAKING_BOL_Service
     */
    private $service;
    private $send_new_matches_interval;
    private $last_matches_sent_timestamp;

    public function __construct()
    {
        parent::__construct();

        $this->service = MATCHMAKING_BOL_Service::getInstance();

        $this->send_new_matches_interval = 86400 * (int)OW::getConfig()->getValue('matchmaking', 'send_new_matches_interval');
        $this->last_matches_sent_timestamp = OW::getConfig()->getValue('matchmaking', 'last_matches_sent_timestamp');
        $this->addJob('sendNewMatches', 1);
    }

    public function run()
    {

    }

    /**
     * Send new matches to users by email
     */
    public function sendNewMatches()
    {
        $config = OW::getConfig();

        if ( !$config->configExists('matchmaking', 'cron_busy') )
        {
            $config->addConfig('matchmaking', 'cron_busy', 0, 'Mass mailing queue is busy');
        }

        if ( !$config->configExists('matchmaking', 'cron_mailing_user_first') )
        {
            $config->addConfig('matchmaking', 'cron_mailing_user_first', 0, 'Already mailed users count');
        }

        // check if cron queue is not busy
        if ( $config->getValue('matchmaking', 'cron_busy') )
        {
            $cronBusyTime = (int)$config->getValue('matchmaking', 'cron_busy_timestamp');
            if (time()-$cronBusyTime > 60 * 30)
            {
                $config->saveConfig('matchmaking', 'cron_busy', 0);
            }
            return;
        }

        if ($this->send_new_matches_interval == 0)
        {
            return;
        }

        if ( time()-$this->last_matches_sent_timestamp < $this->send_new_matches_interval )
        {
            return;
        }

        $config->saveConfig('matchmaking', 'cron_busy', 1);

        if ( !$config->configExists('matchmaking', 'cron_busy_timestamp') )
        {
            $config->addConfig('matchmaking', 'cron_busy_timestamp', time(), '');
        }
        else
        {
            $config->saveConfig('matchmaking', 'cron_busy_timestamp', time());
        }

        $first = (int) $config->getValue('matchmaking', 'cron_mailing_user_first');

        $count = $this->service->countActiveUsers();

        if ( $first < $count )
        {
            $users = $this->service->findActiveUsersList($first, self::CRON_USER_COUNT);
            $counter = 0;
            /**
             * @var BOL_User $user
             */
            foreach ( $users as $id => $user )
            {
                if ($user->emailVerify == 0)
                {
                    $counter++;
                    continue;
                }

                try
                {
                    $this->service->sendNewMatchesForUser($user->getId());
                    $counter++;
                }
                catch ( Exception $e )
                {
                    $config->saveConfig('matchmaking', 'cron_mailing_user_first', $first + $counter);
                    $config->saveConfig('matchmaking', 'cron_busy', 0);
                    return;
                }
            }

            $config->saveConfig('matchmaking', 'cron_mailing_user_first', $first + $counter);
        }
        else
        {
            OW::getConfig()->saveConfig('matchmaking', 'cron_mailing_user_first', 0);
            OW::getConfig()->saveConfig('matchmaking', 'last_matches_sent_timestamp', time());
        }

        $config->saveConfig('matchmaking', 'cron_busy', 0);
    }
}
