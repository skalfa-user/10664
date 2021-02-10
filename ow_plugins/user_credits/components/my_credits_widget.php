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
 * My credits widget component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.user_credits.components
 * @todo Remove widget
 * @since 1.0
 */
class USERCREDITS_CMP_MyCreditsWidget extends BASE_CLASS_Widget
{
    /**
     * @var USERCREDITS_BOL_CreditsService
     */
    private $creditsService;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->creditsService = USERCREDITS_BOL_CreditsService::getInstance();
        
        $userId = OW::getUser()->getId();
        $lang = OW::getLanguage();
        
        if ( !$userId )
        {
            $this->setVisible(false); 
            return;
        }
        
        $balance = $this->creditsService->getCreditsBalance($userId);
                
        $this->assign('balance', $balance);
                
        $this->setSettingValue(
            self::SETTING_TOOLBAR,
            array(
                array(
                    'label' => $lang->text('usercredits', 'buy_more'),
                    'href' => OW::getRouter()->urlForRoute('usercredits.buy_credits')
                )
            )
        );

        $accountTypeId = $this->creditsService->getUserAccountTypeId($userId);
        $earning = (bool) $this->creditsService->findCreditsActions('earn', $accountTypeId);
        $losing = (bool) $this->creditsService->findCreditsActions('lose', $accountTypeId);
        $showCostOfActions = ($earning || $losing);
        
        $this->assign('showCostOfActions', $showCostOfActions);

        $script = '';
        if ( $showCostOfActions )
        {
            $script .=
            '$("#credits-link-cost-of-actions").click(function(){
                document.creditsEarnFloatbox = OW.ajaxFloatBox(
                    "USERCREDITS_CMP_CostOfActions", {}, { width : 432, title: '.json_encode($lang->text('usercredits', 'cost_of_actions')).'}
                );
            });
            ';
        }

        $history = (bool) $this->creditsService->countUserLogEntries($userId);
        $this->assign('showHistory', $history);

        if ( $history )
        {
            $script .=
            '$("#credits-link-history").click(function(){
                document.creditsHistoryFloatbox = OW.ajaxFloatBox(
                    "USERCREDITS_CMP_History", {}, { width : 500, title: '.json_encode($lang->text('usercredits', 'history')).'}
                );
            });
            ';
        }

        if ( mb_strlen($script) )
        {
            OW::getDocument()->addOnloadScript($script);
        }
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('usercredits', 'my_credits'),
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}