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

if ( !Updater::getConfigService()->configExists('usercredits', 'allow_grant_credits') )
{
    Updater::getConfigService()->addConfig('usercredits', 'allow_grant_credits', "1");
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'usercredits');