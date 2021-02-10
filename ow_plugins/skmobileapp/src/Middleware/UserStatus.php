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
namespace Skadate\Mobile\Middleware;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BOL_UserService;
use OW;
use BOL_User;
use BOL_PreferenceService;
use BOL_QuestionService;
use SKMOBILEAPP_BOL_SmsverificationService;

class UserStatus extends Base
{
    /**
     * Allowed user controllers
     *
     * @var array
     */
    protected $allowedUserControllers = [
        [
            'controller' => 'questions-data',
            'methods' => [
                Request::METHOD_POST,
                Request::METHOD_PUT
            ]
        ],
        [
            'controller' => 'users',
            'methods' => [
                Request::METHOD_POST,
                Request::METHOD_PUT
            ]
        ],
        [
            'controller' => 'avatars',
            'methods' => [
                Request::METHOD_PUT
            ]
        ],
        [
            'controller' => 'complete-profile-questions',
            'methods' => [
                Request::METHOD_GET
            ]
        ],
        [
            'controller' => 'user-genders',
            'methods' => [
                Request::METHOD_GET
            ]
        ],
        [
            'controller' => 'verify-email',
            'methods' => [
                Request::METHOD_POST
            ]
        ],
        [
            'controller' => 'sms-verifications',
            'methods' => [
                Request::METHOD_POST,
                Request::METHOD_GET
            ]
        ]
    ];

    /**
     * User service
     *
     * @var BOL_UserService
     */
    protected $userService;

    /**
     * Preference service
     *
     * @var BOL_PreferenceService
     */
    protected $preferenceService;

    /**
     * Question service
     *
     * @var BOL_PreferenceService
     */
    protected $questionService;

    /**
     * User
     *
     * @var BOL_User
     */
    protected $user;

    /**
     * Constructor
     *
     * @param SilexApplication $app
     */
    public function __construct(SilexApplication $app)
    {
        parent::__construct($app);

        $this->userService = BOL_UserService::getInstance();
        $this->preferenceService = BOL_PreferenceService::getInstance();
        $this->questionService = BOL_QuestionService::getInstance();
    }

    /**
     * Get middleware
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return function (Request $request)  {
            if ( $request->getMethod() != Request::METHOD_OPTIONS) {
                $startUpControllers = $this->app['startup.controllers'];

                // skip all startUp controllers
                foreach ($startUpControllers as $controller) {
                    if (substr($request->getRequestUri(), 0, strlen($controller) + 1) == '/' . $controller) {

                        return;
                    }
                }

                // skip all user allowed controllers
                foreach ($this->allowedUserControllers as $controller) {
                    if (substr($request->getRequestUri(), 0, strlen($controller['controller']) + 1) == '/' . $controller['controller']
                            && in_array( $request->getMethod(), $controller['methods'])) {

                        return;
                    }
                }

                if ($this->isAccountTypeNotCompleted()) {
                    return new Response(json_encode([
                        'type' => 'accountTypeNotCompleted',
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'account_type_not_completed_error'),
                    ]), Response::HTTP_FORBIDDEN, [
                        'Content-Type' => 'application/json'
                    ]);
                }

                if ($this->isProfileNotCompleted()) {
                    return new Response(json_encode([
                        'type' => 'profileNotCompleted',
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'profile_not_completed_error'),
                    ]), Response::HTTP_FORBIDDEN, [
                        'Content-Type' => 'application/json'
                    ]);
                }

                list($isNotVerified, $errorType) = $this->isSmsNotVerified();

                if ($isNotVerified) {
                    return new Response(json_encode([
                        'type' => $errorType,
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'phone_not_confirmed_error'),
                    ]), Response::HTTP_FORBIDDEN, [
                        'Content-Type' => 'application/json'
                    ]);
                }

                if ($this->isEmailNotConfirmed()) {
                    return new Response(json_encode([
                        'type' => 'emailNotVerified',
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'email_not_confirmed_error'),
                    ]), Response::HTTP_FORBIDDEN, [
                        'Content-Type' => 'application/json'
                    ]);
                }

                if ($this->isUserDisapproved()) {
                    return new Response(json_encode([
                        'type' => 'disapproved',
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'profile_disapproved_error'),
                    ]), Response::HTTP_FORBIDDEN, [
                        'Content-Type' => 'application/json'
                    ]);
                }

                if ($this->isUserSuspended()) {
                    return new Response(json_encode([
                        'type' => 'suspended',
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'profile_suspended_error'),
                        'description' => $this->userService->getSuspendReason($this->getUser()->getId())
                    ]), Response::HTTP_FORBIDDEN, [
                        'Content-Type' => 'application/json'
                    ]);
                }
            }
        };
    }

    /**
     * Is account type not completed
     *
     * @return boolean
     */
    protected function isAccountTypeNotCompleted()
    {
        if ($this->getUser()) {
            return empty($this->questionService->findAccountTypeByName($this->getUser()->getAccountType()));
        }

        return false;
    }

    /**
     * Is user's profile not completed
     *
     * @return boolean
     */
    protected function isProfileNotCompleted()
    {
        if ($this->getUser()) {
            $questionsEditStamp = OW::getConfig()->getValue('base', 'profile_question_edit_stamp');
            $updateDetailsStamp = $this->preferenceService->
                getPreferenceValue('profile_details_update_stamp', $this->getUser()->getId());

            if ($questionsEditStamp >= (int)$updateDetailsStamp) {
                if ($this->questionService->getEmptyRequiredQuestionsList($this->getUser()->getId())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Is user suspended
     *
     * @return boolean
     */
    protected function isUserSuspended()
    {
        if ($this->getUser()) {
            return $this->userService->isSuspended($this->getUser()->getId());
        }

        return false;
    }

    /**
     * Is user disapproved
     *
     * @return boolean
     */
    protected function isUserDisapproved()
    {
        if ($this->getUser()) {
            return !$this->userService->isApproved($this->getUser()->getId());
        }

        return false;
    }

    /**
     * Is email confirmed
     *
     * @return boolean
     */
    protected function isEmailNotConfirmed()
    {
        if ($this->getUser()) {
            return (bool) OW::getConfig()->getValue('base', 'confirm_email') && !$this->getUser()->getEmailVerify();
        }

        return false;
    }

    /**
     * Get user
     *
     * @return BOL_User
     */
    protected function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        $loggedUserId = $this->app['users']->getLoggedUserId();

        if ($loggedUserId) {
            $this->user = $this->userService->findUserById($loggedUserId);
        }

        return $this->user;
    }

    /**
     * @return array
     */
    protected function isSmsNotVerified()
    {
        if ( OW::getPluginManager()->isPluginActive('smsverification') ) {
            if ( $this->getUser()) {

                $service = SKMOBILEAPP_BOL_SmsverificationService::getInstance();
                /* @var SKMOBILEAPP_BOL_SmsverificationService $service */

                return $service->isSmsNotVerified($this->getUser()->getId());
            }
        }

        return [
            false,
            ''
        ];
    }
}
