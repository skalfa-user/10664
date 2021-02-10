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

$plugin = OW::getPluginManager()->getPlugin('matchmaking');

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'matchmaking');

OW::getDbo()->query("
  CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "matchmaking_question_match` (
  `id` int(11) NOT NULL auto_increment,
  `questionName` varchar(255) NOT NULL,
  `matchQuestionName` varchar(255) NOT NULL,
  `coefficient` int(11) NOT NULL,
  `match_type` enum('exact','range') NOT NULL default 'exact',
  `required` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

OW::getDbo()->query("INSERT INTO `" . OW_DB_PREFIX . "matchmaking_question_match` (`questionName`, `matchQuestionName`, `coefficient`, `match_type`, `required`) VALUES
('sex', 'match_sex', 5, 'exact', 1),
('birthdate', 'match_age', 5, 'exact', 0);");

OW::getDbo()->query("CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "matchmaking_sent_matches` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `match_userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `match_userId` (`match_userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


OW::getPluginManager()->addPluginSettingsRouteName('matchmaking', 'matchmaking_admin_rules');
OW::getPluginManager()->addUninstallRouteName('matchmaking', 'matchmaking_uninstall');


OW::getConfig()->addConfig('matchmaking', 'send_new_matches_interval', 7, 'Send new matches to users by email');
OW::getConfig()->addConfig('matchmaking', 'last_matches_sent_timestamp', 0, 'Timestamp of the last matchmaking mass mailing');

$matchmaking_user_preference_section = BOL_PreferenceService::getInstance()->findSection('matchmaking');
if (empty($matchmaking_user_preference_section))
{
    $matchmaking_user_preference_section = new BOL_PreferenceSection();
    $matchmaking_user_preference_section->name = 'matchmaking';
    $matchmaking_user_preference_section->sortOrder = 0;
    BOL_PreferenceService::getInstance()->savePreferenceSection($matchmaking_user_preference_section);
}

$matchmaking_lastmatch_userid = BOL_PreferenceService::getInstance()->findPreference('matchmaking_lastmatch_userid');
if (empty($matchmaking_lastmatch_userid))
{
    $matchmaking_lastmatch_userid = new BOL_Preference();
    $matchmaking_lastmatch_userid->key = 'matchmaking_lastmatch_userid';
    $matchmaking_lastmatch_userid->defaultValue = 0;
    $matchmaking_lastmatch_userid->sectionName = 'matchmaking';
    $matchmaking_lastmatch_userid->sortOrder = 0;
    BOL_PreferenceService::getInstance()->savePreference($matchmaking_lastmatch_userid);
}

$distanceFromMyLocation = BOL_PreferenceService::getInstance()->findPreference('matchmaking_distance_from_my_location');
if ( empty($distanceFromMyLocation) )
{
    $distanceFromMyLocation = new BOL_Preference();
    $distanceFromMyLocation->key = 'matchmaking_distance_from_my_location';
    $distanceFromMyLocation->defaultValue = 10;
    $distanceFromMyLocation->sectionName = 'matchmaking';
    $distanceFromMyLocation->sortOrder = 1;
    BOL_PreferenceService::getInstance()->savePreference($distanceFromMyLocation);
}

$matchSection = BOL_QuestionService::getInstance()->findSectionBySectionName('about_my_match');
if (empty($matchSection))
{
    $matchSection = new BOL_QuestionSection();
    $matchSection->name = 'about_my_match';
    $matchSection->sortOrder = 1;
    $matchSection->isHidden = 0;
    $matchSection->isDeletable = 0;

    BOL_QuestionService::getInstance()->saveOrUpdateSection($matchSection);
}
