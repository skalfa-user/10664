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

class SKMOBILEAPP_BOL_PhotoService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Max photos
     */
    const MAX_PHOTOS = 1000;

    /**
     * Get users photo list
     *
     * @param array $userIds
     * @param integer $countPerUser
     * @return array
     */
    public function getUsersPhotoList(array $userIds, $countPerUser = self::MAX_PHOTOS, $isApprovedOnly = false)
    {
        $photos = [];
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $status = $isApprovedOnly ? PHOTO_BOL_PhotoDao::STATUS_APPROVED : '';

        // get all users photos
        foreach ($userIds as $userId) {
            $usersPhotos = $photoService->findPhotoListByUserId($userId, 1, $countPerUser, [], $status);

            foreach ($usersPhotos as $userPhoto) {
                $photos[$userId][] = $this->getPhotoData($userPhoto); // process photo data
            }
        }

        return $photos;
    }

    /**
     * Get photo data
     *
     * @param array $photo
     * @param integer $userId
     * @return array
     */
    public function getPhotoData(array $photo, $userId = null)
    {
        return [
            'id' => (int) $photo['id'],
            'url' => PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_PREVIEW, $photo),
            'bigUrl' => PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_ORIGINAL, $photo),
            'approved' => $photo['status'] == 'approved',
            'userId' => (int) ($userId ? $userId : $photo['userId'])
        ];
    }

    public function countAlbumPhotos($albumId, array $statuses = [PHOTO_BOL_PhotoDao::STATUS_APPROVED])
    {
        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldInArray('status', $statuses);

        return PHOTO_BOL_PhotoDao::getInstance()->countByExample($example);
    }
}
