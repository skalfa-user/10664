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
 * Global purchase credits history component
 *
 * @author Alex Ermashev <alexermashevc@gmail.com>
 * @package ow.ow_plugins.user_credits.components
 * @since 1.7.6
 */
class USERCREDITS_CMP_GlobalPurchaseHistory extends OW_Component
{
    /**
     * Display entry limit
     */
    const HISTORY_DISPLAY_ENTRY_LIMIT = 10;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( array $params = array() )
    {
        parent::__construct();

        $this->page = !empty($params['page']) && (int) $params['page'] > 0
            ? $params['page']
            : 1;
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $userIds = array();
        $itemsCount = $creditService->findPacksHistoryCount();
        $itemList = $itemsCount
            ? $creditService->findPacksHistory($this->page, self::HISTORY_DISPLAY_ENTRY_LIMIT)
            : array();

        // collect user names
        foreach ($itemList as $item) {
            array_push($userIds, $item['userId']);
        }

        // paginate
        $pageCount = $itemsCount
            ? ceil($itemsCount / self::HISTORY_DISPLAY_ENTRY_LIMIT)
            : 1;

        $paging = new BASE_CMP_Paging($this->page, $pageCount, self::HISTORY_DISPLAY_ENTRY_LIMIT);

        // assign view variables
        $this->assign('items', $itemList);
        $this->assign('users', BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds));
        $this->assign('paging', $paging->render());
    }
}