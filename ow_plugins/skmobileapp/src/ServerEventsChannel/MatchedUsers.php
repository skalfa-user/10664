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
use OW;

class MatchedUsers extends Base
{
    /**
     * Detect changes
     *
     * @param integer $userId
     * @return mixed|null
     */
    public function detectChanges($userId = null) {
        if ($userId) {
            $matchedUsers = $this->service->findMatchedUsers($userId);

            if (is_null($this->prevValues) || $this->prevValues !== $matchedUsers) {
                $this->prevValues = $matchedUsers;

                return $matchedUsers;
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
        return 'matchedUsers';
    }
}
