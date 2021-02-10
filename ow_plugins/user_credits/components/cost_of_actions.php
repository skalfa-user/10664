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
 * Credits earn component
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.user_credits.components
 * @since 1.7.4
 */
class USERCREDITS_CMP_CostOfActions extends OW_Component
{
    public function __construct( )
    {
        parent::__construct();

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $accountTypeId = $creditService->getUserAccountTypeId(OW::getUser()->getId());
        $earning = $creditService->findCreditsActions('earn', $accountTypeId, false);
        $losing = $creditService->findCreditsActions('lose', $accountTypeId, false);
        
        $this->assign('losing', $losing);
        $this->assign('earning', $earning);
    }
}
