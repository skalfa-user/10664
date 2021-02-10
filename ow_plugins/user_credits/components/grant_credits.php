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
 * Grant credits component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.user_credits.components
 * @since 1.5.2
 */
class USERCREDITS_CMP_GrantCredits extends OW_Component
{
    public function __construct( $userId )
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
        
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        $amount = $creditService->getGrantableAmountForUser(OW::getUser()->getId());

        $form = new USERCREDITS_CLASS_GrantCreditsForm();
        $form->getElement('userId')->setValue($userId);
        $form->getElement('amount')->setValue($amount);

        $this->addForm($form);
    }
}
