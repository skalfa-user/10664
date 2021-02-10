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
 * Search result component
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.mobile.components
 * @since 1.5.3
 */
class USEARCH_MCMP_SearchResultList extends USEARCH_CMP_SearchResultList
{
    public function __construct( $items, $page, $orderType = null, $actions = false )
    {
        parent::__construct($items, $page, $orderType, $actions);
        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getMobileCmpViewDir() . 'search_result_list.html');
        $this->assign('orderType', strip_tags($orderType));
    }
}