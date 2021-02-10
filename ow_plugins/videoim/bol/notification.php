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

/**
 * Data Transfer Object for `videoim_notification` table.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow.ow_plugins.videoim.bol
 * @since 8.1
 */
class VIDEOIM_BOL_Notification extends OW_Entity
{
    /**
     * User id
     *
     * @var integer
     */
    public $userId;

    /**
     * Recipient id
     *
     * @var integer
     */
    public $recipientId;

    /**
     * Session id
     *
     * @var string
     */
    public $sessionId;

    /**
     * Notification
     *
     * @var string
     */
    public $notification;

    /**
     * Create stamp
     *
     * @var integer
     */
    public $createStamp;

    /**
     * Accepted
     *
     * @var integer
     */
    public $accepted;
}