<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Notification
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.matchmaking.components
 * @since 1.0
 */
class MATCHMAKING_CMP_Notification extends OW_Component
{
    private $items = array();
    private $user;
    private $lastUserId=0;

    const NL_PLACEHOLDER = '%%%nl%%%';
    const TAB_PLACEHOLDER = '%%%tab%%%';
    const SPACE_PLACEHOLDER = '%%%space%%%';

    public function __construct( $user, $items )
    {
        parent::__construct();

        $this->user = $user;
        $this->items = $items;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $userService = BOL_UserService::getInstance();
        $matchService = MATCHMAKING_BOL_Service::getInstance();

        $idList = array();
        $compatibilityList = array();
        foreach ( $this->items as $item )
        {
            $idList[] = $item['id'];
            $compatibilityList[$item['id']] = $matchService->getCompatibilityByValue($item['compatibility']);
            if ($item['id'] > $this->lastUserId)
            {
                $this->lastUserId = $item['id'];
            }
        }

        $avatars = array();
        $usernameList = array();
        $displayNameList = array();
        $onlineInfo = array();

        $list = array();
        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList, 2);

            foreach ($idList as $userId)
            {
                $list[$userId]['userUrl'] = $userService->getUserUrl($userId);
                $list[$userId]['displayName'] = $userService->getDisplayName($userId);
                $list[$userId]['avatarUrl'] = $avatars[$userId];
                $list[$userId]['compatibility'] = $compatibilityList[$userId];

                $fields = $matchService->getFieldsForEmail($userId);
                $list[$userId]['sex'] = empty($fields['sex']) ? '' : $fields['sex'];
                $list[$userId]['age'] = empty($fields['age']) ? '' : $fields['age'];
                $list[$userId]['googlemap_location'] = empty($fields['googlemap_location']) ? '' : $fields['googlemap_location'];
            }
        }

        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($this->user->getId()));
        $this->assign('list', $list);
        $this->assign('matchesUrl', OW::getRouter()->urlForRoute('matchmaking_members_page'));
    }

    private function getSubject()
    {
        return OW::getLanguage()->text('matchmaking', 'email_notifications_send_new_matches_subject');
    }

    private function getUnsubscribeUrl()
    {
        return OW::getRouter()->urlForRoute('base_preference_index');
    }

    private function getHtml()
    {
        $template = OW::getPluginManager()->getPlugin('matchmaking')->getCmpViewDir() . 'notification_html.html';
        $this->setTemplate($template);
        $this->assign('unsubscribeUrl', $this->getUnsubscribeUrl());

        return parent::render();
    }

    private function getTxt()
    {
        $template = OW::getPluginManager()->getPlugin('matchmaking')->getCmpViewDir() . 'notification_txt.html';
        $this->setTemplate($template);

        $this->assign('nl', self::NL_PLACEHOLDER);
        $this->assign('tab', self::TAB_PLACEHOLDER);
        $this->assign('space', self::SPACE_PLACEHOLDER);
        $this->assign('unsubscribeUrl', $this->getUnsubscribeUrl());

        $content = parent::render();
        $search = array(self::NL_PLACEHOLDER, self::TAB_PLACEHOLDER, self::SPACE_PLACEHOLDER);
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    public function sendNotification()
    {
        $subject = $this->getSubject();
        $txt = $this->getTxt();
        $html = $this->getHtml();

        $mail = OW::getMailer()->createMail()
            ->addRecipientEmail($this->user->email)
            ->setTextContent($txt)
            ->setHtmlContent($html)
            ->setSubject($subject);

        OW::getMailer()->send($mail);

//        BOL_PreferenceService::getInstance()->savePreferenceValue('matchmaking_lastmatch_userid', (int)$this->items[0]['id'], $this->user->getId());
        BOL_PreferenceService::getInstance()->savePreferenceValue('matchmaking_lastmatch_userid', (int)$this->lastUserId, $this->user->getId());
    }
}