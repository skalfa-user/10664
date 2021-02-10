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
namespace Skadate\Mobile\Controller;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use BOL_AvatarService;
use OW;
use SKMOBILEAPP_BOL_Service;
use UTIL_Image;

class Avatars extends Base
{
    /**
     * Avatar service
     *
     * @param BOL_AvatarService
     */
    protected $avatarService;

    /**
     * Avatars constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->avatarService = BOL_AvatarService::getInstance();
    }

    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // delete avatar
        $controllers->delete('/{id}/', function (SilexApplication $app, $id) {
            $avatarDto = $this->avatarService->findAvatarById($id);
            $loggedUserId = $app['users']->getLoggedUserId();
            $avatarRequired = OW::getConfig()->getValue('base', 'join_display_photo_upload') == 'display_and_required';

            if (!$avatarDto || $avatarDto->getUserId() != $loggedUserId || $avatarRequired) {
                throw new BadRequestHttpException('Avatar cannot be deleted');
            }

            $this->avatarService->deleteUserAvatar($loggedUserId);

            return $app->json(); // ok
        });

        // update avatar
        $controllers->post('/me/', function (SilexApplication $app) {
            // check uploaded file
            if (empty($_FILES['file']['tmp_name'])) {
                throw new BadRequestHttpException('File was not uploaded');
            }

            // validate avatar
            if (!$this->service->isAvatarValid($_FILES['file']['type'], $_FILES['file']['size'])) {
                throw new BadRequestHttpException('File has wrong format or big size');
            }

            return $app->json($this->service->updateUserAvatar($app['users']->getLoggedUserId(), $_FILES['file']['tmp_name']));
        });

        // create avatar
        $controllers->post('/', function (SilexApplication $app) {
            // check uploaded file
            if (empty($_FILES['file']['tmp_name'])) {
                throw new BadRequestHttpException('File was not uploaded');
            }

            // validate avatar
            if (!$this->service->isAvatarValid($_FILES['file']['type'], $_FILES['file']['size'])) {
                throw new BadRequestHttpException('File has wrong format or big size');
            }

            $file = $_FILES['file']['tmp_name'];
            $sessionKey = $this->avatarService->getAvatarChangeSessionKey();

            if (!empty($sessionKey)) {
                $this->avatarService->deleteUserTempAvatar($sessionKey);
            }

            $this->avatarService->setAvatarChangeSessionKey();
            $sessionKey = $this->avatarService->getAvatarChangeSessionKey();

            $avatarPath = $this->avatarService->getTempAvatarPath($sessionKey, SKMOBILEAPP_BOL_Service::DEFAULT_AVATAR_SIZE);

            if ( move_uploaded_file($file, $avatarPath) )
            {
                $img = new UTIL_Image($avatarPath);
                $img->orientateImage()->saveImage();
            }

            return $app->json([
                'url' => $this->avatarService->getTempAvatarUrl($sessionKey, SKMOBILEAPP_BOL_Service::DEFAULT_AVATAR_SIZE) . '?t=' . time(),
                'key' => $sessionKey
            ]);
        });

        return $controllers;
    }
}
