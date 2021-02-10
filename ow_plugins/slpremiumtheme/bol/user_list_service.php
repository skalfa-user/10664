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
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.slpremiumtheme.bol
 * @since 1.0
 */
class SLPREMIUMTHEME_BOL_UserListService
{
    CONST LIST_ONLINE = 'online';
    CONST LIST_LATEST = 'latest';

    CONST SESSION_NAME = 'slpremiumtheme.offset';

    CONST USER_MIN_REQUIRED = 3;
    CONST USER_MAX_REQUIRED = 6;
    CONST USER_COUNT = 100;

    private static $classInstance;

    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getUserList( $listType, $first, $count )
    {
        $userIdList = $this->getUserIdListByListType($listType, $first, $count);

        if ( empty($userIdList) )
        {
            return array();
        }

        $result = array();
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($userIdList, 2);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);
        $urls = BOL_UserService::getInstance()->getUserUrlsForList($userIdList);
        $data = BOL_QuestionService::getInstance()->getQuestionData($userIdList, array('sex', 'birthdate', 'googlemap_location'));

        foreach ( $userIdList as $userId )
        {
            $_data = array();
            $userData = $data[$userId];

            if ( !empty($userData['sex']) )
            {
                for ( $i = 0 ; $i < 31; $i++ )
                {
                    $val = pow(2, $i);

                    if ( (int)$userData['sex'] & $val  )
                    {
                        $_data['sex'] = BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val);

                        break;
                    }
                }
            }

            if ( !empty($userData['birthdate']) )
            {
                $parseDate = UTIL_DateTime::parseDate($userData['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $_data['age'] = UTIL_DateTime::getAge($parseDate['year'], $parseDate['month'], $parseDate['day']);
            }

            $_data['address'] = !empty($userData['googlemap_location']['address']) ? $userData['googlemap_location']['address'] : '';

            $result[] = array(
                'src' => $avatars[$userId],
                'displayName' => $displayNames[$userId],
                'url' => $urls[$userId],
                'data' => $_data
            );
        }

        return $result;
    }

    public function getUserIdListByListType( $listType, $first, $count )
    {
        switch ( $listType )
        {
            case self::LIST_LATEST:
                $list = BOL_UserService::getInstance()->findList($first, $count);
                break;
            case self::LIST_ONLINE:
            default:
                $list = BOL_UserService::getInstance()->findOnlineList($first, $count);
                break;
        }

        $result = array();

        foreach ( $list as $user )
        {
            $result[] = $user->id;
        }

        return $result;
    }
}
