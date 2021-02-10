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
use OW;
use BOL_UserService;
use BOL_AvatarService;
use OW_Event;
use OW_EventManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Videos extends Base
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

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('cvideoupload');
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

        $controllers->get('/user-video/', function() use ($app) {

            if ($this->isPluginActive)
            {
                $loggedUserId = $app['users']->getLoggedUserId();

                $service = \CVIDEOUPLOAD_BOL_Service::getInstance();
                /* @var \CVIDEOUPLOAD_BOL_Service $service */

                $video = $service->findUserVideo($loggedUserId, false);

                $videoForPreview = [];
                $userIdList = [];

                if ( !empty($video) )
                {
                    $videoForPreview = get_object_vars($service->getVideoForPreview($video));
                    $userIdList = $service->getFriendListByUserId($loggedUserId);
                }

                $proceed = [];

                $proceed['params'] = [
                    'uploadUrl' => OW::getRouter()->urlForRoute('skmobileapp.api') . '/user-videos/',
                    'maxSizeBytes' => $service->getUploadFileSizeInBytes(),
                    'duration' => intval($service->getConfigValue('maxDuration'))
                ];

                $proceed['privacyVal'] = [
                    \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_EVERYBODY,
                    \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_ONLY_OWNER,
                    \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS
                ];

                $proceed['videoForPreview'] = $videoForPreview;
                $proceed['userIdList'] = $this->formatUserSearchListData($userIdList);

                return $app->json($proceed);
            }

            throw new BadRequestHttpException('Simple video upload plugin not activated');
        });

        $controllers->post('/user-search/', function(Request $request) use ($app) {

            if ($this->isPluginActive)
            {
                $loggedUserId = $app['users']->getLoggedUserId();

                $data = json_decode($request->getContent(), true);

                $searchVal = $data['q'];
                $ignore = (!empty($data['addedUser'])) ? $data['addedUser'] : [];

                if ( strlen($searchVal = trim($searchVal)) === 0 )
                {
                    return $app->json([]);
                }

                $service = \CVIDEOUPLOAD_BOL_Service::getInstance();
                /* @var \CVIDEOUPLOAD_BOL_Service $service */

                $userIdList =  $service->getSearchResult($searchVal, $ignore);

                $proceed = [];

                if ( !empty($userIdList) )
                {
                    $proceed = $this->formatUserSearchListData($userIdList);
                }

                return $app->json($proceed);
            }

            throw new BadRequestHttpException('Simple video upload plugin not activated');
        });

        // upload user videos
        $controllers->post('/', function() use ($app) {
            if ($this->isPluginActive) {

                $service = \CVIDEOUPLOAD_BOL_Service::getInstance();
                /* @var \CVIDEOUPLOAD_BOL_Service $service */

                // check uploaded file
                if (empty($_FILES['file']['tmp_name']) || $_FILES['file']['error'] ||
                    !in_array(mime_content_type($_FILES['file']['tmp_name']), $service->getAllowedFileTypes())) {

                    throw new BadRequestHttpException('File was not uploaded');
                }

                // check file size
                if ($_FILES['file']['size'] > $service->getUploadFileSizeInBytes()) {
                    throw new BadRequestHttpException('The file size is larger than the maximum file size');
                }

                $loggedUserId = $app['users']->getLoggedUserId();

                // upload file
                $video = $service->uploadVideoFile($_FILES['file'], $loggedUserId);

                if ( !empty($video) ) {
                    return $app->json($service->getVideoForPreview($video));
                }

                throw new BadRequestHttpException('File was not uploaded');
            }

            throw new BadRequestHttpException('Simple video upload plugin not activated');
        });

        $controllers->put('/', function(Request $request) use ($app) {
            if ($this->isPluginActive) {

                $loggedUserId = $app['users']->getLoggedUserId();
                $data = json_decode($request->getContent(), true);

                if ( !empty($data) )
                {
                    $service = \CVIDEOUPLOAD_BOL_Service::getInstance();
                    /* @var \CVIDEOUPLOAD_BOL_Service $service */

                    $privacySettings = \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_EVERYBODY;
                    $fileName = null;
                    $userSearch = [];

                    foreach ( $data as $value )
                    {
                        switch ( $value['name'] )
                        {
                            case 'privacy_settings':

                                if ( in_array($value['value'], [\CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_EVERYBODY,
                                    \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_ONLY_OWNER,
                                    \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS]) )
                                {
                                    $privacySettings = $value['value'];
                                }

                                break;

                            case 'file_name':

                                if ( !empty($value['value']) )
                                {
                                    $fileName = trim($value['value']);
                                }

                                break;

                            case 'user_search':

                                if ( !empty($value['value']) && is_array($value['value']) )
                                {
                                    foreach ( $value['value'] as $userId )
                                    {
                                        $userId = intval($userId);

                                        $userSearch[$userId] = $userId;
                                    }
                                }

                                break;
                        }
                    };

                    $video = $service->findVideoByName($fileName);

                    if ( empty($video) )
                    {
                        $video = $service->findUserVideo($loggedUserId, false);
                    }

                    if ( !empty($video) && $video->userId == $loggedUserId )
                    {
                        /* @var \CVIDEOUPLOAD_BOL_Video $video */

                        $status = $video->status;

                        $video->setPrivacy($privacySettings);

                        if ( $video->status == \CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_CONFIRMED )
                        {
                            $video->setStatus(\CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_PROCESSED);
                        }

                        $video->setTimestamp();

                        if ( !$service->isRequireApproval($video->getUserId()) )
                        {
                            $video->setAuthorization(\CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVED);
                        }
                        else
                        {
                            $video->setAuthorization(\CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVAL);
                        }

                        if ( $service->saveVideo($video) )
                        {
                            $service->deleteAllUserFriends($video->getUserId());

                            if ( $privacySettings == \CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS )
                            {
                                $service->saveFriendByUserId($video->getUserId(), $userSearch);
                            }

                            // TODO Считаем что видео только создано
                            if ( $status == \CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_CONFIRMED )
                            {
                                $service->trackActionAdd($video->getId());
                            }

                            if ( $video->getAuthorization() == \CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVAL )
                            {
                                OW::getEventManager()->trigger(new OW_Event(\CVIDEOUPLOAD_BOL_Service::EVENT_VIDEO_EDIT, ['id' => $video->getId()]));
                            }
                        }

                        if ( !empty($video) ) {
                            return $app->json([
                                'success' => true,
                                'message' => OW::getLanguage()->text('skmobileapp', 'video_success_message')
                            ]);
                        }
                    }
                }

                return $app->json([
                    'success' => false,
                    'message' => OW::getLanguage()->text('skmobileapp', 'error_occurred')
                ]);
            }

            throw new BadRequestHttpException('Simple video upload plugin not activated');
        });

        return $controllers;
    }

    public function formatUserListData(array $listUsers)
    {
        $processedUsers = [];
        $ids = [];

        // process users
        foreach( $listUsers as $list )
        {
            $userDto = BOL_UserService::getInstance()->findUserById($list->friendId);

            // skip deleted users
            if ( empty($userDto) )
            {
                continue;
            }

            $ids[] = $list->friendId;

            $processedUsers[$list->friendId] = [
                'userId' => (int) $list->friendId,
                'avatar' => null,
                'user' => [
                    'userName' => null
                ]
            ];
        }

        // load avatars
        $avatarList = BOL_AvatarService::getInstance()->findByUserIdList($ids);

        foreach ( $avatarList as $avatar )
        {
            $processedUsers[$avatar->userId]['avatar'] = $this->service->getAvatarData($avatar, false);
        }

        // load user names
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($ids);

        foreach ( $userNames as $userId => $userName )
        {
            $processedUsers[$userId]['user']['userName'] = $userName;
        }

        // load display names
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($ids);

        foreach ( $displayNames as $userId => $displayName )
        {
            if ( $displayName )
            {
                $processedUsers[$userId]['user']['userName'] = $displayName;
            }
        }

        $data = [];
        foreach ( $processedUsers as $userData )
        {
            $data[] = $userData;
        }

        $event = new OW_Event('skmobileapp.formatted_video_users_data', [], $data);
        OW_EventManager::getInstance()->trigger($event);

        return $event->getData();
    }

    public function formatUserSearchListData(array $listUsers)
    {
        $processedUsers = [];
        $ids = [];

        // process users
        foreach( $listUsers as $userId )
        {
            $userDto = BOL_UserService::getInstance()->findUserById($userId);

            // skip deleted users
            if ( empty($userDto) )
            {
                continue;
            }

            $ids[] = $userId;

            $processedUsers[$userId] = [
                'userId' => (int) $userId,
                'avatar' => null,
                'user' => [
                    'userName' => null
                ]
            ];
        }

        // load avatars
        $avatarList = BOL_AvatarService::getInstance()->findByUserIdList($ids);

        foreach ( $avatarList as $avatar )
        {
            $processedUsers[$avatar->userId]['avatar'] = $this->service->getAvatarData($avatar, false);
        }

        // load user names
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($ids);

        foreach ( $userNames as $userId => $userName )
        {
            $processedUsers[$userId]['user']['userName'] = $userName;
        }

        // load display names
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($ids);

        foreach ( $displayNames as $userId => $displayName )
        {
            if ( $displayName )
            {
                $processedUsers[$userId]['user']['userName'] = $displayName;
            }
        }

        $data = [];
        foreach ( $processedUsers as $userData )
        {
            $data[] = $userData;
        }

        $event = new OW_Event('skmobileapp.formatted_video_users_data', [], $data);
        OW_EventManager::getInstance()->trigger($event);

        return $event->getData();
    }
}
