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

class SKMOBILEAPP_BOL_DeviceDao extends OW_BaseDao
{
    use OW_Singleton;

    /**
     * Max device life time in seconds
     */
    const MAX_DEVICE_LIFE_TIME_SECONDS = 2592000; // 30 days

    /**
     * Platform android
     */
    const PLATFORM_ANDROID = 'android';

    /**
     * Platform ios
     */
    const PLATFORM_IOS = 'ios';

    /**
     * Platform browser
     */
    const PLATFORM_BROWSER = 'browser';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Get dto class name
     * 
     * @return string
     */
    public function getDtoClassName()
    {
        return 'SKMOBILEAPP_BOL_Device';
    }

    /**
     * Get table name
     * 
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skmobileapp_device';
    }

    /**
     * Find user device by uuid and platform
     *
     * @param integer $userId
     * @param integer $deviceUuid
     * @param string $platform
     * @return SKMOBILEAPP_BOL_Device
     */
    public function findUserDeviceByUUidAndPlatform($userId, $deviceUuid, $platform)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('deviceUuid', $deviceUuid);
        $example->andFieldEqual('platform', $platform);

        return $this->findObjectByExample($example);
    }

    /**
     * Find device by token
     *
     * @param string $token
     * @return SKMOBILEAPP_BOL_Device
     */
    public function findByToken( $token )
    {
        $example = new OW_Example();
        $example->andFieldEqual('token', $token);

        return $this->findObjectByExample($example);
    }

    /**
     * Return device by userId
     *
     * @param string $userId 
     * @return SKMOBILEAPP_BOL_Device
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    /**
     * Remove user devices
     *
     * @param integer $userId
     * @return void
     */
    public function removeUserDevices( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }

    /**
     * Clean expired devices
     * 
     * @return void
     */
    public function cleanExpiredDevices()
    {
        $example = new OW_Example();
        $example->andFieldLessThan('activityTime', time() - self::MAX_DEVICE_LIFE_TIME_SECONDS);

        $this->deleteByExample($example);
    }

    /**
     * Remove ios devices
     * 
     * @return void
     */
    public function removeIOSDevices()
    {
        $example = new OW_Example();
        $example->andFieldEqual('platform', self::PLATFORM_IOS);

        $this->deleteByExample($example);
    }

    /**
     * Is token unique 
     * 
     * @param string $token
     * @return boolean
     */
    public function isTokenUnique($token) 
    {
        $example = new OW_Example();
        $example->andFieldEqual('token', $token);

        return !$this->findObjectByExample($example) ? true : false;
    }
}
