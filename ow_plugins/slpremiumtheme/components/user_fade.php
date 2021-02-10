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
 * @package ow_plugins.slpremiumtheme.components
 * @since 1.0
 */
class SLPREMIUMTHEME_CMP_UserFade extends OW_Component
{
    public function __construct()
    {
        $service = SLPREMIUMTHEME_BOL_UserListService::getInstance();
        $plugin = OW::getPluginManager()->getPlugin('slpremiumtheme');
        $userList = $service->getUserList(SLPREMIUMTHEME_BOL_UserListService::LIST_LATEST, 0, SLPREMIUMTHEME_BOL_UserListService::USER_COUNT);

        if ( ($count = count($userList)) < SLPREMIUMTHEME_BOL_UserListService::USER_MIN_REQUIRED )
        {
            $this->setVisible(false);

            return;
        }
        elseif ( $count == SLPREMIUMTHEME_BOL_UserListService::USER_MIN_REQUIRED || $count == SLPREMIUMTHEME_BOL_UserListService::USER_MAX_REQUIRED )
        {
            $length = $count;
        }
        else
        {
            if ( $count < SLPREMIUMTHEME_BOL_UserListService::USER_MAX_REQUIRED )
            {
                $length = SLPREMIUMTHEME_BOL_UserListService::USER_MIN_REQUIRED;
            }
            else
            {
                $length = SLPREMIUMTHEME_BOL_UserListService::USER_MAX_REQUIRED;
            }

            OW::getDocument()->addScriptDeclarationBeforeIncludes(
                UTIL_JsGenerator::composeJsString(';window.fadeUserParams = {$params};', array(
                    'params' => array(
                        'min' => SLPREMIUMTHEME_BOL_UserListService::USER_MIN_REQUIRED,
                        'max' => $length,
                        'userList' => $userList
                    )
                ))
            );

            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'user_fade.js');
        }

        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'user_fade.css');

        $this->assign('userList', array_slice($userList, 0, $length));
    }
}
