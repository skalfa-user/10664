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

class CUSTOMINDEX_CTRL_Join extends BASE_CTRL_Join
{
    public function joinFormSubmit( $params )
    {
        parent::joinFormSubmit($params);

        OW::getSession()->set(CUSTOMINDEX_CLASS_JoinForm::SESSION_JOIN_STEP, 1);

        $this->setTemplate(OW::getPluginManager()->getPlugin('skadate')->getCtrlViewDir() . 'join_index.html');
    }
}
