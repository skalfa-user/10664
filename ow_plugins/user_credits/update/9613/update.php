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

Updater::getLanguageService()->deleteLangKey('usercredits', 'allow_grant_credits_label');
Updater::getLanguageService()->deleteLangKey('usercredits', 'settings_saved');
Updater::getLanguageService()->deleteLangKey('usercredits', 'usercredits_action_buy_credits');
Updater::getLanguageService()->deleteLangKey('usercredits', 'actions_description');

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'usercredits');