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

class SKMOBILEAPP_BOL_UserLocationDao extends OW_BaseDao
{
    use OW_Singleton;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets DTO class name
     *
     * @return string
     */
    public function getDtoClassName()
    {
        return 'SKMOBILEAPP_BOL_UserLocation';
    }

    /**
     * Gets table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skmobileapp_user_location';
    }

    /**
     * Find user location
     *
     * @param integer $userId
     * @return array
     */
    public function findUserLocation($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * Delete user location
     *
     * @param integer $userId
     */
    public function deleteUserLocation( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }

    /**
     * Find users location
     *
     * @param $ids
     * @return array
     */
    public function findUsersLocation($ids)
    {
        if ( empty($ids) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('userId', $ids);

        return $this->findListByExample($example);
    }

    /**
     * Update user location
     *
     * @param integer $userId
     * @param float $latitude
     * @param float $longitude
     * @param array $southWest
     * @param array $northEast
     * @return array
     */
    public function updateUserLocation($userId, $latitude, $longitude, $southWest, $northEast)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $location = $this->findObjectByExample($example);

        // update location
        if ( $location )
        {
            $location->latitude = $latitude;
            $location->longitude = $longitude;
            $location->northEastLatitude = $northEast['latitude'];
            $location->northEastLongitude = $northEast['longitude'];
            $location->southWestLatitude = $southWest['latitude'];
            $location->southWestLongitude = $southWest['longitude'];

            $this->save($location);

            return [
                'id' => (int) $location->id,
                'userId' => (int) $location->userId,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'northEastLatitude' => (float) $location->northEastLatitude,
                'northEastLongitude' => (float) $location->northEastLongitude,
                'southWestLatitude' => (float) $location->southWestLatitude,
                'southWestLongitude' => (float) $location->southWestLongitude
            ];
        }

        // create a new one
        $location = new SKMOBILEAPP_BOL_UserLocation;
        $location->userId = $userId;
        $location->latitude = $latitude;
        $location->longitude = $longitude;
        $location->northEastLatitude = $northEast['latitude'];
        $location->northEastLongitude = $northEast['longitude'];
        $location->southWestLatitude = $southWest['latitude'];
        $location->southWestLongitude = $southWest['longitude'];

        $this->save($location);

        return [
            'id' => (int) $location->id,
            'userId' => (int) $location->userId,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'northEastLatitude' => (float) $location->northEastLatitude,
            'northEastLongitude' => (float) $location->northEastLongitude,
            'southWestLatitude' => (float) $location->southWestLatitude,
            'southWestLongitude' => (float) $location->southWestLongitude
        ];
    }
}
