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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use BOL_AuthorizationService;
use SKADATE_BOL_AccountTypeToGenderService;
use BOL_EmailVerifyService;
use BOL_AvatarService;
use OW_Event;
use OW_EventManager;
use OW;
use BOL_QuestionService;
use USEARCH_BOL_Service;
use UTIL_DateTime;
use MATCHMAKING_BOL_Service;
use SKMOBILEAPP_BOL_BookmarksService;
use SKMOBILEAPP_BOL_PhotoService;
use MEMBERSHIP_BOL_MembershipService;
use SKMOBILEAPP_BOL_Service;

class Users extends Base
{
    /**
     * Default users limit
     */
    const DEFAULT_USERS_LIMIT = 500;

    /**
     * Bookmark relation
     */
    const BOOKMARK_RELATION = 'bookmark';

    /**
     * View questions relation
     */
    const VIEW_QUESTIONS_RELATION = 'viewQuestions';

    /**
     * Match action relation
     */
    const MATCH_ACTION_RELATION = 'matchAction';

    /**
     * Avatars relation
     */
    const AVATAR_RELATION = 'avatar';

    /**
     * Photos relation
     */
    const PHOTOS_RELATION = 'photos';

    const VIDEO_RELATION = 'video';

    /**
     * Permissions relation
     */
    const PERMISSIONS_RELATION = 'permissions';

    /**
     * @var SKADATE_BOL_AccountTypeToGenderService
     */
    protected $skadateService;

    /**
     * @var BOL_EmailVerifyService
     */
    protected $emailVerifyService;

    /**
     * Question service
     *
     * @var BOL_QuestionService
     */
    protected $questionService;

    /**
     * Authorization service
     *
     * @var BOL_AuthorizationService
     */
    protected $authorizationService;

    /**
     * Relations
     *
     * @var array
     */
    protected $relations = [
        self::BOOKMARK_RELATION,
        self::VIEW_QUESTIONS_RELATION,
        self::MATCH_ACTION_RELATION,
        self::AVATAR_RELATION,
        self::PHOTOS_RELATION,
        self::PERMISSIONS_RELATION,
        self::VIDEO_RELATION
    ];

    /**
     * Users constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->skadateService = SKADATE_BOL_AccountTypeToGenderService::getInstance();
        $this->emailVerifyService = BOL_EmailVerifyService::getInstance();
        $this->avatarService = BOL_AvatarService::getInstance();
        $this->questionService = BOL_QuestionService::getInstance();
        $this->authorizationService = BOL_AuthorizationService::getInstance();
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

        // search
        $controllers->post('/searches/', function (Request $request, SilexApplication $app) {
            if (OW::getPluginManager()->isPluginActive('usearch')) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $data = json_decode($request->getContent(), true);

                // check permissions
                if (!$this->service->isPermissionAllowed($loggedUserId, 'base', 'search_users')) {
                    throw new AccessDeniedHttpException;
                }

                $relations = !empty($data['with[]']) && is_array($data['with[]']) ? $data['with[]'] : [];
                $filters = !empty($data['filters']) && is_array($data['filters']) ? $data['filters'] : [];

                $this->authService->trackActionForUser($loggedUserId, 'base', 'search_users');
                $searchId = $this->service->searchUsers($loggedUserId, $filters);

                $foundUsers = USEARCH_BOL_Service::getInstance()->
                        getSearchResultList($searchId, USEARCH_BOL_Service::LIST_ORDER_NEW, 0, self::DEFAULT_USERS_LIMIT);

                return $app->json($this->getFormattedUsersData($foundUsers, true, $relations, $loggedUserId));
            }

            throw new BadRequestHttpException('User search plugin is not activated');
        });

        // get user
        $controllers->get('/{id}/', function (Request $request, SilexApplication $app, $id) {
            $loggedUserId = $app['users']->getLoggedUserId();

            if ($loggedUserId != $id) {
                if (OW::getPluginManager()->isPluginActive('privacy')) {
                    $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', [
                        'action' => 'base_view_profile',
                        'ownerIdList' => [$id],
                        'viewerId' => $loggedUserId
                    ]);

                    if ($permissions[$id]['blocked']) {
                        throw new AccessDeniedHttpException;
                    }
                }

                if (!$this->service->isPermissionAllowed($loggedUserId, 'base', 'view_profile')) {
                    throw new AccessDeniedHttpException;
                }

                $this->authService->trackActionForUser($loggedUserId, 'base', 'view_profile');
            }

            OW::getEventManager()->call('guests.track_visit', [
                'userId' => $id,
                'guestId' => $loggedUserId
            ]);

            OW::getEventManager()->call('guests.mark_guests_viewed', [
                'userId' => $loggedUserId,
                'guestIds' => [$id]
            ]);

            return $app->json($this->getUser($id, $loggedUserId, $request->query->get('with', [])));
        });

        // update user
        $controllers->put('/{id}/', function (Request $request, SilexApplication $app, $id) {
            $loggedUserId = $app['users']->getLoggedUserId();
            $data = json_decode($request->getContent(), true);

            if ($loggedUserId != $id) {
                throw new BadRequestHttpException('You can update only your self');
            }

            // update mode
            $mode = $request->query->get('mode');

            // trigger events before saving
            switch ($mode) {
                case 'completeAccountType' :
                    $event = new OW_Event( OW_EventManager::ON_BEFORE_USER_COMPLETE_ACCOUNT_TYPE, [
                        'user' => $loggedUserId
                    ]);

                    OW::getEventManager()->trigger($event);

                    break;

                default :
            }

            $userDto = $this->userService->findUserById($loggedUserId);

            $userData = [
                self::USERNAME_QUESTION_NAME => !empty($data[self::USERNAME_QUESTION_NAME])
                    ? $data[self::USERNAME_QUESTION_NAME]
                    : $userDto->getUsername(),

                self::EMAIL_QUESTION_NAME => !empty($data[self::EMAIL_QUESTION_NAME])
                    ? $data[self::EMAIL_QUESTION_NAME]
                    : $userDto->getEmail(),

                self::USER_ACCOUNT_QUESTION_NAME => !empty($data[self::USER_ACCOUNT_QUESTION_NAME])
                    ? $this->skadateService->getAccountType($data[self::USER_ACCOUNT_QUESTION_NAME])
                    : $userDto->getAccountType(),
            ];

            $this->questionService->saveQuestionsData($userData, $loggedUserId);

            // trigger events after saving
            switch ($mode) {
                case 'completeAccountType' :
                    $event = new OW_Event(OW_EventManager::ON_AFTER_USER_COMPLETE_PROFILE, [
                        'userId' => $loggedUserId
                    ]);

                    OW::getEventManager()->trigger($event);

                    break;

                default :
            }

            $token = $app['security.jwt.encoder']->encode(
                $this->service->getUserDataForToken($userDto->getId())
            );

            return $app->json(array_merge(
                $this->getUser($loggedUserId), [
                    'token' => $token
                ]
            ));
        });

        // create user
        $controllers->post('/', function(Request $request) use ($app) {
            $data = json_decode($request->getContent(), true);

            $event = new OW_Event(OW_EventManager::ON_BEFORE_USER_REGISTER, $data);
            OW::getEventManager()->trigger($event);

            $accountType = $this->skadateService->getAccountType($data['sex']);

            $user = $this->userService->createUser(
                $data['userName'],
                $data['password'],
                $data['email'],
                $accountType);

            // assign early uploaded avatar
            if ($data['avatarKey']) {
                OW::getSession()->set(BOL_AvatarService::AVATAR_CHANGE_SESSION_KEY, $data['avatarKey']);
                $this->avatarService->createAvatar($user->id, true, false);
            }
            else {
                OW::getSession()->set(BOL_AvatarService::AVATAR_CHANGE_SESSION_KEY, null);
            }

            $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, [
                'userId' => $user->id,
                'method' => 'native',
                'params' => $data
            ]);

            OW::getEventManager()->trigger($event);

            if (OW::getConfig()->getValue('base', 'confirm_email')) {
                $this->emailVerifyService->sendUserVerificationMail($user);
            }

            // generate auth token
            $token = $app['security.jwt.encoder']->encode(
                $this->service->getUserDataForToken($user->id)
            );

            return $app->json(array_merge($this->getUser($user->id), [
                'token' => $token
            ]));
        });

        // unblock user
        $controllers->delete('/blocks/{id}/', function (SilexApplication $app, $id) {
            $loggedUserId = $app['users']->getLoggedUserId();

            if (!$this->service->isUserBlocked($loggedUserId, $id)) {
                throw new BadRequestHttpException('User is not blocked');
            }

            $this->service->unblockUser($loggedUserId, $id);

            return $app->json([], 204); // ok
        });

        // block user
        $controllers->post('/blocks/{id}/', function (SilexApplication $app, $id) {
            $loggedUserId = $app['users']->getLoggedUserId();

            if ($this->service->isUserBlocked($loggedUserId, $id)) {
                throw new BadRequestHttpException('User already blocked');
            }

            $this->service->blockUser($loggedUserId, $id);

            return $app->json([], 204); // ok
        });

        // delete user
        $controllers->delete('/{id}/', function($id) use ($app) {
            $loggedUserId = $app['users']->getLoggedUserId();

            if ($loggedUserId != $id || OW::getUser()->isAdmin()) {
                throw new AccessDeniedHttpException;
            }

            $this->userService->deleteUser($loggedUserId, true);

            return $app->json([], 204); // ok
        });

        return $controllers;
    }

    /**
     * Get formatted users data
     *
     * @param array $userList
     * @param boolean $hideEmail
     * @param array $relations
     * @param integer $loggedUserId
     * @return array
     */
    protected function getFormattedUsersData(array $userList, $hideEmail = true, array $relations = [], $loggedUserId = null) {
        if (!$userList) {
            return [];
        }

        $ids = [];
        $processedUsers = [];
        $emptyRelations = [];

        // add empty relations
        if ($relations) {
            foreach($relations as $relation) {
                if (in_array($relation, $this->relations)) {
                    $emptyRelations[$relation] = null;
                }
            }
        }

        // add basic info
        foreach ($userList as $userDto) {
            $ids[] = $userDto->id;

            $processedUsers[$userDto->id] = [
                'id' => (int) $userDto->id,
                'userName' => $userDto->username,
                'email' => !$hideEmail ? $userDto->email : null,
                'type' => $userDto->accountType,
                'aboutMe' => null,
                'age' => null,
                'isOnline' => false,
                'isAdmin' => BOL_AuthorizationService::getInstance()->
                isActionAuthorizedForUser($userDto->id, BOL_AuthorizationService::ADMIN_GROUP_NAME),
                'compatibility' => 0,
                'isBlocked' => false,
                'distance' => null
            ] + $emptyRelations;
        }

        // find compatibility
        if (OW::getPluginManager()->isPluginActive('matchmaking')) {
            if ($loggedUserId) {
                $result = MATCHMAKING_BOL_Service::getInstance()->
                        findCompatibilityByUserIdList($loggedUserId, $ids, 0, count($ids));

                foreach ($result as $item) {
                    $processedUsers[$item['userId']]['compatibility'] = (int) $item['compatibility'];
                }
            }
        }

        // find blocks
        if ($loggedUserId) {
            $result = $this->userService->findBlockedListByUserIdList($loggedUserId, $ids);

            foreach ($result as $userId => $isBlocked) {
                $processedUsers[$userId]['isBlocked'] = $isBlocked;
            }
        }

        // find distance
        if ($loggedUserId) {
            $loggedUserLocation = $this->service->findUserLocation($loggedUserId);

            if ($loggedUserLocation) {
                $distanceUnit = $this->service->getDistanceUnits();
                $usersLocation = $this->service->findUsersLocation($ids);

                foreach ($usersLocation as $userLocation) {
                    $distance = $this->service->distance($loggedUserLocation->latitude,
                            $loggedUserLocation->longitude, $userLocation->latitude, $userLocation->longitude, $distanceUnit);

                    $processedUsers[$userLocation->userId]['distance'] = [
                        'distance' => (int) $distance < 1 ? 1 : (int) $distance,
                        'unit' => $distanceUnit
                    ];
                }
            }
        }

        // find online statuses
        $onlineStatuses = $this->userService->findOnlineStatusForUserList($ids);

        foreach($onlineStatuses as $userId => $isOnline) {
            $processedUsers[$userId]['isOnline'] = (bool) $isOnline;
        }

        // find display names
        $displayNames = $this->userService->getDisplayNamesForList($ids);

        foreach($displayNames as $userId => $displayName) {
            if ($displayName) {
                $processedUsers[$userId]['userName'] = $displayName;
            }
        }

        // find some questions
        $questionList = $this->questionService->getQuestionData($ids, ['birthdate', 'aboutme']);

        foreach ($questionList as $userId => $questions) {
            if (isset($questions['birthdate'])) {
                $date = UTIL_DateTime::parseDate($questions['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $processedUsers[$userId]['age'] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $processedUsers[$userId]['aboutMe'] = isset($questions['aboutme']) ? $questions['aboutme'] : null;
        }

        // load relations
        if ($relations) {
            foreach($relations as $relation) {
                if (in_array($relation, $this->relations)) {
                    switch ($relation) {
                        // load match actions
                        case self::MATCH_ACTION_RELATION :
                            if ($loggedUserId) {
                                $mathList = $this->service->findUserMatchActionsByUserIdList($loggedUserId, $ids);

                                foreach($mathList as $matchAction) {
                                    $processedUsers[$matchAction->recipientId][$relation] = [
                                        'id' => (int) $matchAction->id,
                                        'type' => $matchAction->type,
                                        'isMutual' => boolval($matchAction->mutual),
                                        'userId' => (int) $matchAction->recipientId,
                                        'createStamp' => (int) $matchAction->createStamp,
                                        'isRead' => boolval($matchAction->read),
                                        'isNew' => boolval($matchAction->new)                                        
                                    ];
                                }
                            }
                            break;

                        // load permissions
                        case self::PERMISSIONS_RELATION :
                            $permissionList = $this->service->getPermissions($ids);

                            foreach($permissionList as $userId => $permissions) {
                                $processedUsers[$userId][$relation] = $permissions;
                            }
                            break;

                        // load bookmark
                        case self::BOOKMARK_RELATION :
                            if ($loggedUserId) {
                                if (!OW::getPluginManager()->isPluginActive('bookmarks')) {
                                    throw new BadRequestHttpException('Bookmarks plugin is not activated');
                                }

                                $bookmarks = SKMOBILEAPP_BOL_BookmarksService::getInstance()->getMarkedListByUserId($loggedUserId, $ids);

                                foreach($bookmarks as $bookmark) {
                                    $processedUsers[$bookmark['user']][$relation] = $bookmark;
                                }
                            }
                            break;

                        // load avatar
                        case self::AVATAR_RELATION :
                            $avatarList = $this->avatarService->findByUserIdList($ids);

                            foreach($avatarList as $avatar) {
                                $processedUsers[$avatar->userId][$relation] = $this->service->getAvatarData($avatar);
                            }
                            break;

                        // load view questions
                        case self::VIEW_QUESTIONS_RELATION :
                            $excludedQuestions = [
                                'realname',
                                'birthdate',
                                'username'
                            ];

                            // check permissions
                            if ($loggedUserId && count($ids) == 1 && $ids[0] == $loggedUserId) { // don't check permissions for own questions
                                $viewQuestionList = $this->service->getViewQuestions($ids, $excludedQuestions);

                                foreach($viewQuestionList as $userId => $questions) {
                                    $processedUsers[$userId][$relation] = $questions;
                                }
                            }
                            else {
                                if (!$this->service->isPermissionAllowed($loggedUserId, 'base', 'view_profile')) {
                                    throw new AccessDeniedHttpException;
                                }

                                $viewQuestionList = $this->service->getViewQuestions($ids, $excludedQuestions);

                                foreach($viewQuestionList as $userId => $questions) {
                                    $processedUsers[$userId][$relation] = $questions;
                                }
                            }
                            break;

                        // load photos
                        case self::PHOTOS_RELATION :
                            if (!OW::getPluginManager()->isPluginActive('photo')) {
                                throw new BadRequestHttpException('Photo plugin is not activated');
                            }

                            $photoList = [];
                            $userPhotoIds = $ids;

                            if (OW::getPluginManager()->isPluginActive('privacy')) {
                                $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', [
                                    'action' => 'photo_view_album',
                                    'ownerIdList' => $ids,
                                    'viewerId' => $loggedUserId
                                ]);

                                $newUserIds = [];
                                foreach ($permissions as $key => $value) {
                                    if (!$value['blocked']) {
                                        $newUserIds[] = $key;
                                    }
                                }

                                $userPhotoIds = $newUserIds;
                            }

                            // don't check permissions for own photos and retrieve all photos skipping the status
                            if ($loggedUserId && count($userPhotoIds) == 1 && $userPhotoIds[0] == $loggedUserId) {
                                $photoList = SKMOBILEAPP_BOL_PhotoService::getInstance()->getUsersPhotoList($userPhotoIds);
                            }
                            else {
                                if (!$this->service->isPermissionAllowed($loggedUserId, 'photo', 'view')) {
                                    throw new AccessDeniedHttpException;
                                }

                                // find only approved photos
                                $photoList = SKMOBILEAPP_BOL_PhotoService::getInstance()->
                                        getUsersPhotoList($userPhotoIds, SKMOBILEAPP_BOL_PhotoService::MAX_PHOTOS, true);
                            }

                            foreach($photoList as $userId => $photos) {
                                $processedUsers[$userId][$relation] = $photos;
                            }

                            break;

                        case self::VIDEO_RELATION :
                            if (!OW::getPluginManager()->isPluginActive('cvideoupload')) {
                                throw new BadRequestHttpException('Video upload plugin not activated');
                            }

                            $service = \CVIDEOUPLOAD_BOL_Service::getInstance();
                            /* @var \CVIDEOUPLOAD_BOL_Service $service */

                            $videoList = $service->findUsersVideoList($ids);

                            foreach($videoList as $video) {

                                // TODO ПРОВЕРКА НА ПРОСМОТР
                                $isView = $service->isViewVideo($loggedUserId, $video);

                                if ( $isView === true )
                                {
                                    $processedUsers[$video->userId][$relation] = $service->getVideoForPreview($video);
                                }
                            }

                        default :
                    }
                }
            }
        }

        $data = [];
        foreach($processedUsers as $userData) {
            $data[] = $userData;
        }

        $event = new OW_Event('skmobileapp.formatted_users_data', [], $data);
        OW_EventManager::getInstance()->trigger($event);

        return $event->getData();
    }

    /**
     * Get user
     *
     * @param integer $userId
     * @param integer $loggedUserId
     * @param array $relations
     * @throws Exception
     * @return array
     */
    protected function getUser($userId, $loggedUserId = null, array $relations = [])
    {
        $userDto = $this->userService->findUserById($userId);

        if ($userDto === null) {
            throw new NotFoundHttpException('User not found');
        }

        $users = $this->getFormattedUsersData([$userDto], false, $relations, $loggedUserId);

        return array_shift($users);
    }
}
