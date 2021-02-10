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

try {
    Updater::getDbo()->query(" ALTER TABLE `".OW_DB_PREFIX."membership_plan` ADD COLUMN `periodUnits` varchar(20) NOT NULL default 'days' ");
} catch (Exception $ex) {
    
}

Updater::getLanguageService()->deleteLangKey('membership', 'plan_struct_trial');
Updater::getLanguageService()->deleteLangKey('membership', 'plan_struct_recurring');
Updater::getLanguageService()->deleteLangKey('membership', 'plan_struct');
Updater::getLanguageService()->deleteLangKey('membership', 'trial_granted');


Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'membership');
