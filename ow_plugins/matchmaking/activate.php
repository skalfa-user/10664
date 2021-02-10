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

$widget = BOL_ComponentAdminService::getInstance()->addWidget('MATCHMAKING_CMP_CompatibilityWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('MATCHMAKING_CMP_MatchesWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 1);

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'matchmaking_members_page', 'matchmaking', 'matches_index', OW_Navigation::VISIBLE_FOR_MEMBER);

$dbPrefix = OW_DB_PREFIX;
$sql = "UPDATE `{$dbPrefix}base_question_section` SET `isHidden`=0 WHERE `name`='about_my_match'";
OW::getDbo()->query($sql);

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'matchmaking_members_page', 'matchmaking', 'matches_mobile_index', OW_Navigation::VISIBLE_FOR_MEMBER);