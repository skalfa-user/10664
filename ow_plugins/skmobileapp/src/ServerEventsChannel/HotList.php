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
namespace Skadate\Mobile\ServerEventsChannel;

use SKMOBILEAPP_BOL_HotListService;
use HOTLIST_BOL_Service;
use OW;

class HotList extends Base
{
    /**
     * Detect changes
     *
     * @param integer $userId
     * @return mixed|null
     */
    public function detectChanges($userId = null) {
        if ($userId && OW::getPluginManager()->isPluginActive('hotlist')) {
            $hotListUsers = HOTLIST_BOL_Service::getInstance()->getHotList();

            if ($hotListUsers) {
                $hotListUsers = SKMOBILEAPP_BOL_HotListService::getInstance()->formatHotListData($hotListUsers);
            }

            if (is_null($this->prevValues) || $this->prevValues !== $hotListUsers) {
                $this->prevValues = $hotListUsers;

                return $hotListUsers;
            }
        }

        return null;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return 'hotList';
    }
}
