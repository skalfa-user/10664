<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class GDPR_CMP_UserDataWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $userId = OW::getUser()->getId();
        $userService = BOL_UserService::getInstance();
        $user = $userService->findUserById($userId);
        $userDisplayName = $userService->getDisplayName($user->id);

        $jsParams = [
            'downloadUrl' => OW::getRouter()->urlForRoute('gdpr-request-download'),
            'deletionUrl' => OW::getRouter()->urlForRoute('gdpr-request-deletion')
        ];
        $script = ' var admin = new buttonsActions(); admin.init('.  json_encode($jsParams).' );';

        $this->assign('user', $user);
        $this->assign('userDisplayName', $userDisplayName);
        OW::getDocument()->addOnloadScript($script);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('gdpr')->getStaticJsUrl().'buttons_actions.js');
    }

    public static function getSettingList()
    {
        return [];
    }

    public static function getStandardSettingValueList()
    {
        return [
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => self::ICON_FRIENDS,
            self::SETTING_TITLE => OW::getLanguage()->text('gdpr', 'gdpr_user_data_label')
        ];
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}