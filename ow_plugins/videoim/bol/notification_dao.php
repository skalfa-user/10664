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
 * Data Access Object for `videoim_notification` table
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow.ow_plugins.videoim.bol
 * @since 8.1
 */
class VIDEOIM_BOL_NotificationDao extends OW_BaseDao
{
    /**
     * Notification type not permitted
     */
    const NOTIFICATION_TYPE_NOT_PERMITTED = 'not_permitted';

    /**
     * Notification type credits outs
     */
    const NOTIFICATION_TYPE_CREDITS_OUT = 'credits_out';

    /**
     * Notification type bye
     */
    const NOTIFICATION_TYPE_BYE = 'bye';

    /**
     * Notification type not supported
     */
    const NOTIFICATION_TYPE_NOT_SUPPORTED = 'not_supported';

    /**
     * Notification type offer
     */
    const NOTIFICATION_TYPE_OFFER = 'offer';

    /**
     * Notification type candidate
     */
    const NOTIFICATION_TYPE_CANDIDATE = 'candidate';

    /**
     * Notification type answer
     */
    const NOTIFICATION_TYPE_ANSWER = 'answer';

    /**
     * Notification type blocked
     */
    const NOTIFICATION_TYPE_BLOCKED = 'blocked';

    /**
     * Notification type declined
     */
    const NOTIFICATION_TYPE_DECLINED = 'declined';

    /**
     * Accepted notification
     */
    const NOTIFICATION_ACCEPTED = 1;

    /**
     * Not accepted notification
     */
    const NOTIFICATION_NOT_ACCEPTED = 0;

    /**
     * Notifications lifetime in seconds
     */
    const NOTIFICATIONS_LIFETIME = 120; // 2 minutes

    /**
     * Class instance
     *
     * @var VIDEOIM_BOL_NotificationDao
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns class instance
     *
     * @return VIDEOIM_BOL_NotificationDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Gets DTO class name
     *
     * @return string
     */
    public function getDtoClassName()
    {
        return 'VIDEOIM_BOL_Notification';
    }

    /**
     * Gets table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'videoim_notification';
    }

    /**
     * Returns notifications
     *
     * @param integer $userId
     * @return array
     */
    public function findNotifications( $userId )
    {
        $query = "
    		SELECT
    		    `id`,
    		    `userId`,
    		    `sessionId`,
    		    `notification`,
    		    `accepted`,
    		    `createStamp`
    		FROM
    		    `" . $this->getTableName() . "`
    		WHERE
    		    `recipientId`=?
    		        AND
    		    `createStamp` >= ?
    		ORDER BY
    		    `id` ASC
    	";

        return $this->dbo->queryForList($query, array(
            $userId,
            (time() - self::NOTIFICATIONS_LIFETIME)
        ));
    }

    /**
     * Mark accepted notifications
     *
     * @param $userId
     * @param $recipientId
     * @param $sessionId
     * @return void
     */
    public function markAcceptedNotifications($userId, $recipientId, $sessionId)
    {
        $query = "
    		UPDATE
    		    `" . $this->getTableName() . "`
    		SET
    		    `accepted` = ?
    		WHERE
    		    (
    		        `userId` = ?
    		            AND
    		        `recipientId` = ?
    		            AND
    		        `sessionId` = ?
    		    )
    	";

        $this->dbo->query($query, array(
            self::NOTIFICATION_ACCEPTED,
            $userId,
            $recipientId,
            $sessionId
        ));
    }
    
    /**
     * Delete user notifications
     *
     * @param integer $userId
     * @param integer $recipientId
     * @return void
     */
    public function deleteUserNotifications($userId, $recipientId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('recipientId', $recipientId);
        $this->deleteByExample($example);
    }

    /**
     * Delete expired notifications
     *
     * @return void
     */
    public function deleteExpiredNotifications()
    {
        $example = new OW_Example();
        $example->andFieldLessThan('createStamp', (time() - self::NOTIFICATIONS_LIFETIME));
        $this->deleteByExample($example);

        $this->dbo->query('OPTIMIZE TABLE ' . $this->getTableName());
    }
}
