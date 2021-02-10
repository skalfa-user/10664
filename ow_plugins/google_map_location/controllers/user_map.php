<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.controllers
 * @since 1.0
 */


class GOOGLELOCATION_CTRL_UserMap extends OW_ActionController
{
    const MAX_USERS_COUNT = 16;
    
    public function map()
    {
        $menu = BASE_CTRL_UserList::getMenu('map');
        $this->addComponent('menu', $menu);

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('googlelocation', 'map_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_bookmark');

        $event = new OW_Event( 'googlelocation.get_map_component', array( 'userIdList' => 'all', 'backUri' => OW::getRouter()->getUri() ) );
        OW::getEventManager()->trigger($event);

        /* @var $map GOOGLELOCATION_CMP_Map */
        $map = $event->getData();
        $map->displaySearchInput(true);
        $map->disableDefaultUI(false);
        $map->disableInput(false);
        $map->disableZooming(false);
        $map->disablePanning(false);
        
        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));
        
        $this->addComponent("map", $map);
    }

    private function getUserFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = '';

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid] =  $sexValue . ' ' . $age;
            }
        }

        return $fields;
    }
}
