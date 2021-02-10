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

use SKMOBILEAPP_BOL_CompatibleUsersService;
use OW;

class CompatibleUsers extends Base
{
    /**
     * Users limit
     */
    const USERS_LIMIT = 200;

    /**
     * Detect changes
     *
     * @param integer $userId
     * @return mixed|null
     */
    public function detectChanges($userId = null) {
        if ($userId && OW::getPluginManager()->isPluginActive('matchmaking')) {
            $users = SKMOBILEAPP_BOL_CompatibleUsersService::getInstance()->findUsers($userId, self::USERS_LIMIT);

            if (is_null($this->prevValues) || $this->prevValues !== $users) {
                $this->prevValues = $users;

                return $users;
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
        return 'compatibleUsers';
    }
}
