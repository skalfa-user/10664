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

BOL_ComponentAdminService::getInstance()->deleteWidget('MATCHMAKING_CMP_CompatibilityWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('MATCHMAKING_CMP_MatchesWidget');

OW::getNavigation()->deleteMenuItem('matchmaking', 'matches_index');

$dbPrefix = OW_DB_PREFIX;
$sql = "UPDATE `{$dbPrefix}base_question_section` SET `isHidden`=1 WHERE `name`='about_my_match'";
OW::getDbo()->query($sql);

BOL_LanguageService::getInstance()->deletePrefix(363);

// Mobile activation
OW::getNavigation()->deleteMenuItem('matchmaking', 'matches_mobile_index');