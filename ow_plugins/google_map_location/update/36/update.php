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
 * @package ow_plugins.google_maps_location
 * @since 1.6
 */

try
{
    // -- add location question to all account types
    $accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();

    $list = array();

    foreach( $accountTypeList as $accauntType )
    {
        /* @var $accauntType BOL_QuestionAccountType */
        $list[$accauntType->name] = $accauntType->name;
    }

    BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList(array('googlemap_location'), $list);

    // ----------------------------------------------
}
catch ( Exception $e ){ }

try
{
    if ( !OW::getConfig()->configExists('googlelocation', 'distance_units') )
    {
        OW::getConfig()->addConfig('googlelocation', 'distance_units', 'miles', 'Distance Units');
    }
}
catch ( Exception $e ){ }

try
{
    if ( !OW::getConfig()->configExists('googlelocation', 'auto_fill_location_on_search') )
    {
        OW::getConfig()->addConfig('googlelocation', 'auto_fill_location_on_search', '0', 'Auto fill location on search');
    }
}
catch ( Exception $e ){ }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'googlelocation');
