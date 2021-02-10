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

class HLOOKINGFOR_CTRL_Search extends USEARCH_CTRL_Search
{
    public function form()
    {
        parent::form();

        $this->setTemplate(OW::getPluginManager()->getPlugin(HLOOKINGFOR_BOL_Service::PLUGIN_KEY)->getCtrlViewDir() . 'search_form.html');

        $matchSexStudent = HLOOKINGFOR_BOL_Service::getInstance()->getGenderByAccounType(HLOOKINGFOR_BOL_Service::STUDENTS_ACCOUNT_TYPE);
        $matchSexTeacher = HLOOKINGFOR_BOL_Service::getInstance()->getGenderByAccounType(HLOOKINGFOR_BOL_Service::TEACHERS_ACCOUNT_TYPE);
        $elements = $this->getForm('MainSearchForm')->getElements();

        $isStudent = false;

        if ( isset($elements['sex']) )
        {
            $sex = $elements['sex'];

            if ( !OW::getUser()->isAuthenticated() )
            {
                $sexValue = $sex->getValue();

                if ( $sexValue == $matchSexStudent )
                {
                    $isStudent = true;
                }

                if ( is_null($sexValue) && !empty($sex->getOptions()) )
                {
                    foreach ($sex->getOptions() as $value => $option)
                    {
                        $sexValue = $value;

                        break;
                    }

                    if ( $sexValue == $matchSexStudent )
                    {
                        $isStudent = true;
                    }
                }
            }
        }

        if ( isset($elements['match_sex']) )
        {
            $matchSex = $elements['match_sex'];

            if ( $isStudent )
            {
                $matchSex->setValue($matchSexTeacher);
            }
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->assign('authenticated', true);
        }
        else
        {
            $this->assign('authenticated', false);
        }

        $this->assign('isStudent', $isStudent);
    }
}