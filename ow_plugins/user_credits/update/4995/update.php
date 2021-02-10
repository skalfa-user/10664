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

$updateDir = dirname(__FILE__) . DS;

try
{
    UPDATER::getWidgetService()->deleteWidget('USERCREDITS_CMP_MyCreditsWidget');
}
catch( Exception $e ) {}

Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'usercredits');
