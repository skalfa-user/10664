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
 * Photo cron job.
 *
 * @authors Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.winks
 * @since 1.0
 */
class WINKS_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteExpiredData', 180);
    }

    public function run()
    {
        
    }

    public function deleteExpiredData()
    {
        $service = WINKS_BOL_Service::getInstance();
        $winks = $service->findExpiredDate(time() - WINKS_BOL_Service::LIMIT_TIMESTAMP);
        $idList = array();
        
        foreach ( $winks as $wink )
        {
            $idList[] = $wink->id;
            $service->deleteWinkById($wink->id);
        }
        
        if ( count($idList) !== 0 )
        {
            OW::getEventManager()->trigger(
                new OW_Event(WINKS_CLASS_EventHandler::EVENT_DELETE_EXPIRED_WINKS, array('idList' => $idList))
            );
        }
    }
}
