<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class FIREBASEAUTH_CTRL_Connect extends OW_ActionController
{
    /**
     * Authenticate
     */
    public function authenticate()
    {
        $backUrl = OW_URL_HOME . (!empty($_POST['backUri']) ? $_POST['backUri'] : '');
        $providerId = !empty($_POST['providerId']) ? $_POST['providerId'] : '';
        $userUid = !empty($_POST['uid']) ? $_POST['uid'] : '';
        $inviteCode = !empty($_POST['inviteCode']) ? $_POST['inviteCode'] : '';

        // check required params
        if ( $providerId && $userUid && FIREBASEAUTH_BOL_Service::getInstance()->isRegisterAllowed($inviteCode) ) 
        {
            $displayName = !empty($_POST['displayName']) ? $_POST['displayName'] : '';
            $email = !empty($_POST['email']) ? $_POST['email'] : '';
            $photoURL = !empty($_POST['photoURL']) ? $_POST['photoURL'] : '';

            try {
                list(, $userId) = FIREBASEAUTH_BOL_Service::getInstance()->
                        authenticateUser($providerId, $userUid, $displayName, $email, $photoURL);

                OW::getFeedback()->info(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'successfully_authenticated'));
                OW::getUser()->login($userId);

                $this->redirect($backUrl);
            }
            catch (Exception $e) {}
        }

        OW::getFeedback()->error(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'authenticate_error'));

        $this->redirect($backUrl);
    }

    /**
     * Display token
     */
    public function displayToken() 
    {
        $token = !empty($_GET['token']) ? htmlentities($_GET['token']) : '__empty__';

        $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('blank');
        OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);

        // init view variables
        $this->assign('token', $token);
    }
}
