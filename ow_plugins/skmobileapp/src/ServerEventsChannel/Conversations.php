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

use SKMOBILEAPP_BOL_MailboxService;
use OW;

class Conversations extends Base
{
    /**
     * Conversations limit
     */
    const CONVERSATIONS_LIMIT = 80;

    /**
     * Detect changes
     *
     * @param integer $userId
     * @return mixed|null
     */
    public function detectChanges($userId = null) {
        if ($userId && OW::getPluginManager()->isPluginActive('mailbox')) {
            $conversations = SKMOBILEAPP_BOL_MailboxService::getInstance()->getConversations($userId, self::CONVERSATIONS_LIMIT);

            if (is_null($this->prevValues) || $this->prevValues !== $conversations) {
                $this->prevValues = $conversations;

                return $conversations;
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
        return 'conversations';
    }
}
