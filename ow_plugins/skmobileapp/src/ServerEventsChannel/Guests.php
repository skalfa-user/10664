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

use SKMOBILEAPP_BOL_GuestsService;
use OW;

class Guests extends Base
{
    /**
     * Guests limit
     */
    const GUESTS_LIMIT = 200;

    /**
     * Detect changes
     *
     * @param integer $userId
     * @return mixed|null
     */
    public function detectChanges($userId = null) {
        if ($userId && OW::getPluginManager()->isPluginActive('ocsguests')) {
            $guests = SKMOBILEAPP_BOL_GuestsService::getInstance()->findGuests($userId, self::GUESTS_LIMIT);

            if (is_null($this->prevValues) || $this->prevValues !== $guests) {
                $this->prevValues = $guests;

                return $guests;
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
        return 'guests';
    }
}
