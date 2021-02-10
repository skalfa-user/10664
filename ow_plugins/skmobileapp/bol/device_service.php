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

class SKMOBILEAPP_BOL_DeviceService
{
    use OW_Singleton;

    /**
     * @var SKMOBILEAPP_BOL_DeviceDao
     */
    private $deviceDao;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->deviceDao = SKMOBILEAPP_BOL_DeviceDao::getInstance();
    }

    /**
     * Is token unique 
     * 
     * @param string $token
     * @return boolean
     */
    public function isTokenUnique($token) 
    {
        return $this->deviceDao->isTokenUnique($token);
    }

    /**
     * Update device
     *
     * @param integer $userId
     * @param array $deviceData
     *      string deviceUuid
     *      string token
     *      string platform
     *      string language
     * @return  SKMOBILEAPP_BOL_Device
     */
    public function updateDevice( $userId, $deviceData )
    {
        $devicePlatform = strtolower($deviceData['platform']);

        $device = $this->
            findUserDeviceByUUidAndPlatform($userId, $deviceData['deviceUuid'], $devicePlatform);

        // register a new device
        if ( !$device )
        {
            $device = new SKMOBILEAPP_BOL_Device;
            $device->userId = $userId;
            $device->deviceUuid = $deviceData['deviceUuid'];
            $device->token = $deviceData['token'];
            $device->platform = $devicePlatform;
            $device->activityTime = time();
            $device->language = SKMOBILEAPP_BOL_Service::getInstance()->conversionLang($deviceData['language']);

            $this->deviceDao->save($device);

            return $device;
        }

        // update some device's data
        $device->token = $deviceData['token'];
        $device->activityTime = time();
        $device->language = SKMOBILEAPP_BOL_Service::getInstance()->conversionLang($deviceData['language']);

        $this->deviceDao->save($device);

        return $device;
    }

    /**
     * Find device by token
     *
     * @param string $token
     * @return SKMOBILEAPP_BOL_Device
     */
    public function findByToken( $token )
    {
        return $this->deviceDao->findByToken($token);
    }

    /**
     * Find device by user id
     *
     * @param string $userId
     * @return SKMOBILEAPP_BOL_Device
     */
    public function findByUserId( $userId )
    {
        return $this->deviceDao->findByUserId($userId);
    }

    /**
     * Find user device by uuid and platform
     *
     * @param integer $userId
     * @param string $deviceUuid
     * @param string $platform
     * @return SKMOBILEAPP_BOL_Device
     */
    public function findUserDeviceByUUidAndPlatform($userId, $deviceUuid, $platform)
    {
        return $this->deviceDao->findUserDeviceByUUidAndPlatform($userId, $deviceUuid, $platform);
    }

    /**
     * Clean expired devices
     *
     * @return void
     */
    public function cleanExpiredDevices()
    {
        return $this->deviceDao->cleanExpiredDevices();
    }

    /**
     * Remove ios devices
     *
     * @return void
     */
    public function removeIOSDevices()
    {
        return $this->deviceDao->removeIOSDevices();
    }
}
