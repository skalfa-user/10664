<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Bookmarks Notify components
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.components
 * @since 1.0
 */
class BOOKMARKS_CMP_Notify extends OW_Component
{
    private $user;
    
    public function __construct( $userId, $idList )
    {
        parent::__construct();
        
        if ( !empty($userId) && !empty($idList) )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($userId);
            
            $userService = BOL_UserService::getInstance();
            $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList, 2);
            $sexValue = array();
            $list = array();
            
            foreach ( BOL_QuestionValueDao::getInstance()->findQuestionValues('sex') as $sexDto )
            {
                $sexValue[$sexDto->value] = BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $sexDto->value);
            }

            $userData = BOL_QuestionService::getInstance()->getQuestionData($idList, array('sex', 'birthdate', 'googlemap_location'));
            
            foreach ( $idList as $userId )
            {
                $list[$userId]['userUrl'] = $userService->getUserUrl($userId);
                $list[$userId]['displayName'] = $userService->getDisplayName($userId);
                $list[$userId]['avatarUrl'] = $avatars[$userId];
                $list[$userId]['activity'] = UTIL_DateTime::formatDate(BOL_UserService::getInstance()->findUserById($userId)->getActivityStamp());

                if ( !empty($userData[$userId]['birthdate']) )
                {
                    $date = UTIL_DateTime::parseDate($userData[$userId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                    $list[$userId]['age'] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                }

                if ( !empty($userData[$userId]['sex']) )
                {
                    $list[$userId]['sex'] = $sexValue[$userData[$userId]['sex']];
                }
                
                if ( !empty($userData[$userId]['googlemap_location']) )
                {
                    $list[$userId]['googlemap_location'] = $userData[$userId]['googlemap_location']['address'];
                }
            }
            
            $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($this->user->id));
            $this->assign('list', $list);
        }
        else
        {
            $this->setVisible(FALSE);
        }
    }

    private function getHtml()
    {
        $this->setTemplate(OW::getPluginManager()->getPlugin('bookmarks')->getCmpViewDir() . 'notify_html.html');

        return parent::render();
    }

    public function sendNotification()
    {
        if ( empty($this->user) )
        {
            return;
        }
        
        $content = $this->getHtml();
        
        $mail = OW::getMailer()->createMail()
            ->addRecipientEmail($this->user->email)
            ->setSubject(OW::getLanguage()->text('bookmarks', 'email_notify_subject'))
            ->setHtmlContent($content)
            ->setTextContent($content);

        OW::getMailer()->send($mail);
    }
}
