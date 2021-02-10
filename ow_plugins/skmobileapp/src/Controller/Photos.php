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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use PHOTO_BOL_PhotoService;
use PHOTO_BOL_PhotoAlbumService;
use OW;
use OW_Event;
use BOL_AvatarService;
use SKMOBILEAPP_BOL_PhotoService;

class Photos extends Base
{
    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isPluginActive = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('photo');
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

        // set as avatar
        $controllers->put('/{id}/setAsAvatar/', function (SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $photoService = PHOTO_BOL_PhotoService::getInstance();

                if ($photoService->findPhotoOwner($id) == $loggedUserId) {
                    $photoDto = $photoService->findPhotoById($id);

                    if ($photoDto) {
                        $photoPath = $photoService->getPhotoPath($id, $photoDto->hash, PHOTO_BOL_PhotoService::TYPE_ORIGINAL);

                        return $app->json($this->service->updateUserAvatar($loggedUserId, $photoPath)); // ok
                    }
                }

                throw new BadRequestHttpException('Photo cannot be marked as avatar');
            }

            throw new BadRequestHttpException('Photo plugin is not activated');
        });

        // create photo
        $controllers->post('/', function (SilexApplication $app) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();

                if (!$this->service->isPermissionAllowed($loggedUserId, 'photo', 'upload')) {
                    throw new AccessDeniedHttpException;
                }

                // check uploaded file
                if (empty($_FILES['file']['tmp_name'])) {
                    throw new BadRequestHttpException('File was not uploaded');
                }

                $event = new OW_Event('photo.getMainAlbum', array('userId' => $loggedUserId));
                OW::getEventManager()->trigger($event);
                $album = $event->getData();

                $selectedAlbumId = !empty($album['album']) 
                    ? $album['album']['id'] 
                    : null;

                if (!$selectedAlbumId) {
                    throw new BadRequestHttpException('Undefined album');
                }

                $photoInAlbumCount = SKMOBILEAPP_BOL_PhotoService::getInstance()->countAlbumPhotos($selectedAlbumId, ['approved', 'approval']);
                $photoInAlbumSetting = OW::getConfig()->getValue('photo', 'album_quota');

                if ( ++$photoInAlbumCount > $photoInAlbumSetting )
                {
                    return new Response(json_encode([
                        'type' => 'photoLimitReached',
                        'shortDescription' => OW::getLanguage()->text('photo', 'album_quota_exceeded', array('limit' => $photoInAlbumSetting)),
                    ]), Response::HTTP_BAD_REQUEST, [
                        'Content-Type' => 'application/json'
                    ]);
                }

                // upload photo
                $photo = OW::getEventManager()->call('photo.add', [
                    'albumId' => $selectedAlbumId,
                    'path' => $_FILES['file']['tmp_name']
                ]);

                // increase action counter
                if (!empty($photo['photoId'])) {
                    $this->authService->trackActionForUser($loggedUserId, 'photo', 'upload');

                    return $app->json(SKMOBILEAPP_BOL_PhotoService::getInstance()->getPhotoData(
                        (array) PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photo['photoId']), $loggedUserId));
                }

                throw new BadRequestHttpException('Photo cannot be uploaded');
            }

            throw new BadRequestHttpException('Photo plugin is not activated');
        });

        // delete photo
        $controllers->delete('/{id}/', function (SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $photoService = PHOTO_BOL_PhotoService::getInstance();
                $loggedUserId = $app['users']->getLoggedUserId();

                // check the ownership
                $owner = $photoService->findPhotoOwner($id);

                if (!$owner || $owner != $loggedUserId) {
                    throw new BadRequestHttpException('Photo cannot be deleted');
                }

                $photoService->deletePhoto($id);

                return $app->json(); // ok
            }

            throw new BadRequestHttpException('Photo plugin is not activated');
        });

        return $controllers;
    }
}
