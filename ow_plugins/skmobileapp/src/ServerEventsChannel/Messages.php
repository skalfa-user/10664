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

class Messages extends Base
{
    /**
     * Latest message id
     *
     * @var integer
     */
    protected $latestMessageId = 0;

    /**
     * Detect changes
     *
     * @param integer $userId
     * @return mixed|null
     */
    public function detectChanges($userId = null) {
        if ($userId && OW::getPluginManager()->isPluginActive('mailbox')) {
            $messages = SKMOBILEAPP_BOL_MailboxService::getInstance()->getLatestMessages($userId, $this->latestMessageId);

            if ($messages) {
                $this->latestMessageId = $messages[count($messages) - 1]['id'];

                if (is_null($this->prevValues) || $this->prevValues !== $messages) {
                    $this->prevValues = $messages;
    
                    return $messages;
                }
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
        return 'messages';
    }
}
