<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class CUSTOMFIELDS_CTRL_CompleteProfile extends BASE_CTRL_CompleteProfile
{
    public function fillRequiredQuestions($params)
    {
        parent::fillRequiredQuestions($params);
        // change template
        $this->setTemplate(CUSTOMFIELDS_BOL_Service::getPlugin()->getCtrlViewDir() . 'complete_profile_fill_required_questions.html');
    }
}