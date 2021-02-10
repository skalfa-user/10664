<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class FIREBASEAUTH_BOL_Service 
{
    use OW_Singleton;

    /**
     * Plugin key
     */
    const PLUGIN_KEY = 'firebaseauth';

    /**
     * Default user name
     */
    const DEFAULT_USER_NAME = 'user';

    /**
     * Firebase admin key file name
     */
    const ADMIN_KEY_FILE_NAME = 'key.json';

    /**
     * Twitter provider
     */
    const TWITTER_PROVIDER = 'twitter.com';

    /**
     * Facebook provider
     */
    const FACEBOOK_PROVIDER = 'facebook.com';

    /**
     * Google provider
     */
    const GOOGLE_PROVIDER = 'google.com';

    /**
     * Linkedin provider
     */
    const LINKEDIN_PROVIDER = 'linkedin.com';

    /**
     * Instagram provider
     */
    const INSTAGRAM_PROVIDER = 'instagram.com';

    /**
     * Get all registered providers
     * 
     * @return array
     */
    public function getAllRegisteredProviders()
    {
        return [
            FIREBASEAUTH_BOL_Service::TWITTER_PROVIDER => 'Twitter',
            FIREBASEAUTH_BOL_Service::FACEBOOK_PROVIDER => 'Facebook',
            FIREBASEAUTH_BOL_Service::GOOGLE_PROVIDER => 'Google',
            //FIREBASEAUTH_BOL_Service::LINKEDIN_PROVIDER => 'LinkedIn',
        ];
    }

    /**
     * Get plugin build
     *
     * @return integer
     */
    public function getPluginBuild()
    {
        return OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getDto()->getBuild();
    }

    /**
     * Is firebase auth enabled
     */
    public function isFirebaseAuthEnabled()
    {
        return $this->getApiKey() != '' && $this->getAuthDomain() != '';
    }

    /**
     * Get auth domain
     * 
     * @return string
     */
    public function getAuthDomain()
    {
        return OW::getConfig()->getValue(self::PLUGIN_KEY, 'auth_domain');
    }

    /**
     * Get email example
     *
     * @return string
     */
    public function getEmailExample()
    {
        return OW::getConfig()->getValue(self::PLUGIN_KEY, 'email_example');
    }

    /**
     * Set auth domain
     * 
     * @param string $authDomain
     * @return void
     */
    public function setAuthDomain($authDomain)
    {
        OW::getConfig()->saveConfig(self::PLUGIN_KEY, 'auth_domain', $authDomain);
    }

    /**
     * Get api key
     * 
     * @return string
     */
    public function getApiKey()
    {
        return OW::getConfig()->getValue(self::PLUGIN_KEY, 'api_key');
    }

    /**
     * Set api key
     * 
     * @param string $apiKey
     * @return void
     */
    public function setApiKey($apiKey)
    {
        OW::getConfig()->saveConfig(self::PLUGIN_KEY, 'api_key', $apiKey);
    }

    /**
     * Get enabled providers
     * 
     * @return array
     */
    public function getEnabledProviders()
    {
        return json_decode(OW::getConfig()->getValue(self::PLUGIN_KEY, 'enabled_providers'), true);
    }

    /**
     * Set enabled providers
     * 
     * @param array $providers
     * @return void
     */
    public function setEnabledProviders(array $providers)
    {
        OW::getConfig()->saveConfig(self::PLUGIN_KEY, 'enabled_providers', json_encode($providers));
    }

    /**
     * Set email example
     *
     * @param string $email
     *
     * @return void
     */
    public function setEmailExample($email)
    {
        OW::getConfig()->saveConfig(self::PLUGIN_KEY, 'email_example', $email);
    }

    /**
     * Get max displayed providers
     * 
     * @return integer
     */
    public function getMaxDisplayedProviders()
    {
        return (int) OW::getConfig()->getValue(self::PLUGIN_KEY, 'max_displayed_providers');
    }

    /**
     * Set max displayed providers
     * 
     * @param integer $maxProviders
     * @return void
     */
    public function setMaxDisplayedProviders($maxProviders)
    {
        OW::getConfig()->saveConfig(self::PLUGIN_KEY, 'max_displayed_providers', $maxProviders);
    }

    /**
     * Is register allowed
     * 
     * @param string $inviteCode
     * @return boolean
     */
    public function isRegisterAllowed($inviteCode = '')
    {
        // check the join by invitations
        if ( (int) OW::getConfig()->
                getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            return false;
        }

        return true;
    }

    /**
     * Authenticate user
     * 
     * @param string $providerId
     * @param string $remoteId
     * @param string $username
     * @param string $email
     * @param string $photo
     * @return array
     *      string type
     *      integer id
     * @throws Exception
     */
    public function authenticateUser( $providerId, $remoteId, $username = '', $email = '', $photo = '' )
    {
        // try to find a user profile using the remote id and provider id
        $authAdapter = new FIREBASEAUTH_CLASS_FirebaseAuthAdapter($providerId, $remoteId);

        if ( $authAdapter->isRegistered() )
        {
            $authResult = OW::getUser()->authenticate($authAdapter);

            if ( $authResult->isValid() )
            {
                return [
                    'logged',
                    $authResult->getUserId()
                ];
            }

            throw new Exception('Auth failed');
        }

        // create a new user profile
        return [
            'created',
            $this->registerUser($providerId, $remoteId, $username, $email, $photo)
        ];
    }

    /**
     * Register user
     * 
     * @param string $providerId
     * @param integer $remoteId
     * @param string $username
     * @param string $email
     * @param string $photo
     *
     * @throws Exception
     *
     * @return integer
     */
    protected function registerUser($providerId, $remoteId, $username = '', $email = '', $photo = '')
    {
        $userByEmail = BOL_UserService::getInstance()->findByEmail($email);

        // user is already registered by a some provider
        if ( $userByEmail !== null )
        {
            return $userByEmail->id;
        }

        // process user name
        $realName = $username;
        $username = preg_replace( "/[^a-zA-Z0-9]/", '', $username );
        $username = empty($username) || !UTIL_Validator::isUserNameValid($username) ? self::DEFAULT_USER_NAME : $username;

        if ( BOL_UserService::getInstance()->isExistUserName($username) )
        {
            // get like users
            $example = new OW_Example();
            $example->andFieldLike('username', '%' . $username . '%');

            // get count like users
            $countLikeUserNames = BOL_UserDao::getInstance()->countByExample($example);

            // set new username
            $username = $username . '_'  . $countLikeUserNames;
        }

        // process email
        $email = $email && UTIL_Validator::isEmailValid($email) ? $email : $this->generateUserEmail();

        $password = uniqid();

        $event = new OW_Event(OW_EventManager::ON_BEFORE_USER_REGISTER, [
            'username' => $username,
            'password' => $password,
            'email' => $email,

        ]);

        OW::getEventManager()->trigger($event);

        // create a user's profile
        $user = BOL_UserService::getInstance()->createUser($username, $password, $email, null, true);

        if ($user) {
            $email = $user->email;
            $parseEmail = explode('@', $email);

            $exampleEmail = FIREBASEAUTH_BOL_Service::getInstance()->getEmailExample();
            $parseExampleEmail = explode('@', $exampleEmail);

            if ($parseEmail[1] === $parseExampleEmail[1] && stripos($parseEmail[0], $parseExampleEmail[0] . '+user' . $user->id) !== false) {
                $sql = "UPDATE " . BOL_UserDao::getInstance()->getTableName() . " SET email = '' WHERE id = " . $user->id;
                OW::getDbo()->query($sql);
            }
        }

        // save a photo
        if ( $photo )
        {
            switch ($providerId) {
                case self::FACEBOOK_PROVIDER :
                    $photo = $photo . '?type=large&?return_ssl_resources=0'; // wee need a large photo

                    break;
                    
                case self::TWITTER_PROVIDER :
                    // https://developer.twitter.com/en/docs/accounts-and-users/user-profile-images-and-banners.html
                    $photo = str_replace([
                        '_normal', 
                        '_bigger', 
                        '_mini'
                    ], [
                        '', 
                        '', 
                        '', 
                    ], $photo); // wee need a large photo

                    break;
            }

            BOL_AvatarService::getInstance()->setUserAvatar($user->id, $photo, [
                'isModerable' => true,
                'trackAction' => false
            ]);
        }

        // save a real name
        BOL_QuestionService::getInstance()->saveQuestionsData([
            'realname' => $realName
        ], $user->id);

        // authenticate
        $authAdapter = new FIREBASEAUTH_CLASS_FirebaseAuthAdapter($providerId, $remoteId);
        $authAdapter->register($user->id);

        $authResult = OW_Auth::getInstance()->authenticate($authAdapter);

        if ( $authResult->isValid() )
        {
            $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, [
                'method' => FIREBASEAUTH_CLASS_FirebaseAuthAdapter::PROVIDER_PREFIX . '.' . $providerId,
                'userId' => $user->id,
                'params' => []
            ]);

            OW::getEventManager()->trigger($event);

            return $user->id;
        }

        throw new Exception('Register user failed');
    }

    /**
     * Generate username
     * 
     * @return string
     */
    protected function generateUsername()
    {
        $username =  substr(uniqid(time() . OW_PASSWORD_SALT), 0, 32);

        if ( BOL_UserService::getInstance()->isExistUserName($username) )
        {
            return $this->generateUsername();
        }

        return $username;
    }

    /**
     * Generate user email
     * 
     * @return string
     */
    protected function generateUserEmail()
    {
        $exampleEmail = $this->getEmailExample();

        $dbName = OW_DB_NAME;
        $tableName = BOL_UserDao::getInstance()->getTableName();

        $sql = "SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$tableName}'";

        $lastId =  OW::getDbo()->queryForColumn($sql);

        $parseEmail = explode('@', $exampleEmail);

        return $parseEmail[0] . '+user' . $lastId . '@' . $parseEmail[1];
    }
}
