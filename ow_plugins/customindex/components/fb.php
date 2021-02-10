<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CUSTOMINDEX_CMP_Fb extends OW_Component
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if ( (int) OW::getConfig()->
                getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS ) {

            $this->setVisible(false);
        }
    }

    /**
     * On before render
     */
    public function onBeforeRender()
    {
        parent::onBeforeRender();
    }
}
