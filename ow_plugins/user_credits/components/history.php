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
 * Credits history component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.user_credits.components
 * @since 1.5.3
 */
class USERCREDITS_CMP_History extends OW_Component
{
    const HISTORY_DISPLAY_ENTRY_LIMIT = 8;

    public function __construct()
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }

        $lang = OW::getLanguage();
        $this->addComponent('items', new USERCREDITS_CMP_HistoryItems(1, self::HISTORY_DISPLAY_ENTRY_LIMIT));

        $userId = OW::getUser()->getId();
        $loadMore = USERCREDITS_BOL_CreditsService::getInstance()->countUserLogEntries($userId) > self::HISTORY_DISPLAY_ENTRY_LIMIT;
        $this->assign('loadMore', $loadMore);

        $toolbar = array();
        if ( $loadMore )
        {
            $toolbar = array(array(
                'label' => $lang->text('usercredits', 'view_more'),
                'href' => OW::getRouter()->urlForRoute('usercredits.history')
            ));
        }

        $this->assign('toolbar', $toolbar);

    }
}