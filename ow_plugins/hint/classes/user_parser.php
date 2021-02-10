<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.classes
 */
class HINT_CLASS_UserParser extends HINT_CLASS_Parser
{
    const ROUTE_NAME = 'base_user_profile';

    public function __construct()
    {
        $routeMask = OW::getRouter()->urlForRoute(self::ROUTE_NAME, array(
            'username' => '--PLACEHOLDER--'
        ));

        $parseMask = "^" . str_replace('--PLACEHOLDER--', '([\w]{1,32})$', $routeMask);
        
        parent::__construct($parseMask, array(
            ".ow_chat_in_item_author_href",
            ".index-BASE_CMP_MyAvatarWidget a",
            ".ow_menu_wrap a",
            ".ow_console_dropdown_hover a",
            ".ow_footer_menu a"
        ));
     }

    public function parse($url)
    {
        $match = array();
        preg_match('~' . $this->mask . '~', $url, $match);

        $userName = $match[1];
        $user = BOL_UserService::getInstance()->findByUsername($userName);

        if ( $user === null )
        {
            return null;
        }

        return array(
            'userId' => $user->id
        );
    }

    public function renderHint( array $params )
    {
        $isModerator = OW::getUser()->isAuthorized("base") || OW::getUser()->isAdmin();
        $noPermission = null;
        
        if ( !$isModerator )
        {
            if ( !OW::getUser()->isAuthorized('base', 'view_profile') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
                
                $noPermission = $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED 
                        ? $status['msg']
                        : null;
            }
            
            $eventParams = array(
                'action' => 'base_view_profile',
                'ownerId' => $params['userId'],
                'viewerId' => OW::getUser()->getId()
            );

            try
            {
                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $e )
            {
                $noPermission = OW::getLanguage()->text("hint", "private_profile_message");
            }
        }
                
        if ( $noPermission === null )
        {
            $hint = new HINT_CMP_UserHint($params['userId']);
        }
        else
        {
            $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, false, true, false);
            $hint = new HINT_CMP_PrivateHint(HINT_BOL_Service::ENTITY_TYPE_USER, $params['userId'], $avatarData[$params['userId']], $noPermission);
        }

        return array(
            'body' => $hint->render(),
            'topCorner' => $hint->renderTopCover(),
            'rightCorner' => $hint->renderRightCover(),
            'bottomCorner' => $hint->renderBottomCover()
        );
    }
}

