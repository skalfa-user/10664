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

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.protected_photos.controllers
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_CTRL_ProtectedPhoto extends OW_ActionController
{
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = PROTECTEDPHOTOS_BOL_Service::getInstance();
    }

    public function enterPassword()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit();
        }

        $response = array();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $response['msg'] = OW::getLanguage()->text('base', 'base_sign_in_cap_label');
        }
        else
        {
            $userId = OW::getUser()->getId();
            $form = new PROTECTEDPHOTOS_CLASS_EnterPasswordForm();
            if ( $form->isValid($_POST) )
            {
                $values = $form->getValues();
                $password = $this->service->findPasswordForAlbumByAlbumId($values[PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_ALBUM_ID]);
                if ( $password === null )
                {
                    $response['msg'] = OW::getLanguage()->text('protectedphotos', 'not_protected_album');
                }
                elseif ( count($this->service->getAccessForUser($userId, array($password->albumId))) !== 0 )
                {
                    $response['msg'] = OW::getLanguage()->text('protectedphotos', 'has_access');
                }
                elseif ( strcmp(trim($values[PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_PASSWORD]), trim($password->password)) !== 0 )
                {
                    $response['msg'] = OW::getLanguage()->text('protectedphotos', 'mismatch_password');
                }
                else
                {
                    $this->service->grantAccess($password->albumId, $userId);
                    $context = explode('|', $values[PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_CONTEXT]);
                    if ( $context[0] == 'album_view' )
                    {
                        $event = OW::getEventManager()->trigger(new OW_Event('photo.album_find', array('albumId' => $context[1])));
                        $data = $event->getData();
                        $values[PROTECTEDPHOTOS_CLASS_EnterPasswordForm::ELEMENT_CONTEXT] = implode('|', array($context[0], $data['url']));
                    }
                    $response['success'] = true;
                    //$response['msg'] = OW::getLanguage()->text('protectedphotos', 'match_password');
                    $response['data'] = $values;
                }
            }
            else
            {
                $response['msg'] = OW::getLanguage()->text('protectedphotos', 'invalid_argument');
            }
        }

        exit(json_encode($response));
    }

    public function rspFriendList( array $params )
    {
        if ( empty($_POST['searchText']) ) exit(json_encode(array()));

        $searchText = trim($_POST['searchText']);
        $userIds = OW::getEventManager()->call('plugin.friends.get_friend_list_by_display_name', array(
            'userId' => OW::getUser()->getId(),
            'search' => $searchText
        ));
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($userIds);

        return exit(json_encode(array_reduce($userIds, function( $carry, $userId ) use( $displayNames, $avatars )
        {
            $carry[] = array(
                'id' => $userId,
                'displayName' => $displayNames[$userId],
                'src' => $avatars[$userId]
            );

            return $carry;
        }, array())));
    }
}
