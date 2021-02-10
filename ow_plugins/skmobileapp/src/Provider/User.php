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
namespace Skadate\Mobile\Provider;

use Silex\Application as SilexApplication;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User as UserDto;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use SKMOBILEAPP_CLASS_AuthAdapter;
use SKMOBILEAPP_BOL_Service;
use BOL_UserService;
use OW_EventManager;
use OW_Event;
use OW;

class User implements UserProviderInterface
{
    /**
     * Logged user id
     *
     * @var integer
     */
    protected $loggedUserId = null;

    /**
     * Default role
     */
    const DEFAULT_ROLE = 'ROLE_USER';

    /**
     * Service
     *
     * @var BOL_UserService
     */
    protected $service;

    /**
     * App
     *
     * @var SilexApplication
     */
    protected $app;

    /**
     * User constructor
     *
     * @param SilexApplication $app
     */
    public function __construct(SilexApplication $app)
    {
        $this->app = $app;
        $this->service = BOL_UserService::getInstance();
    }

    /**
     * Load user by username
     *
     * @param string $userName
     * @return User
     */
    public function loadUserByUsername($userName)
    {
        // find by user name
        $user = $this->service->findByUsername($userName);

        // find by email
        if (!$user) {
            $user = $this->service->findByEmail($userName);
        }

        if (!$user) {
            OW::getUser()->authenticate(new SKMOBILEAPP_CLASS_AuthAdapter);
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist or not activated.', $userName));
        }

        SKMOBILEAPP_BOL_Service::getInstance()->internalUserAuthenticate($user->id);

        return new UserDto($user->username, $user->password, [self::DEFAULT_ROLE], true, true, true, true);
    }

    /**
     * Refresh user
     *
     * @param UserInterface $user
     * @throws UnsupportedUserException
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Get logged user id
     *
     * @return integer
     */
    public function getLoggedUserId()
    {
        if ($this->loggedUserId) {
            return $this->loggedUserId;
        }

        $token = $this->app['security.token_storage']->getToken();

        if (null !== $token) {
            $userData = $token->getUser();

            if (is_object($userData) && in_array(self::DEFAULT_ROLE, $userData->getRoles())) {
                $userDto = $this->service->findByUsername($userData->getUsername());

                if ($userDto) {
                    $this->loggedUserId = $userDto->getId();
                }
            }
        }

        return $this->loggedUserId;
    }

    /**
     * Supports class
     *
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
