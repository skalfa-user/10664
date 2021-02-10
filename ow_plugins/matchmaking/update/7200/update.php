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

Updater::getLanguageService()->importPrefixFromZip(dirname(dirname(dirname(__FILE__))) . DS . 'langs.zip', 'matchmaking');

$example = new OW_Example();
$example->andFieldEqual('name', 'about_my_match');
$matchSection = BOL_QuestionSectionDao::getInstance()->findObjectByExample($example);
if (empty($matchSection))
{
    $matchSection = new BOL_QuestionSection();
    $matchSection->name = 'about_my_match';
    $matchSection->sortOrder = 1;
    $matchSection->isHidden = 0;

    BOL_QuestionSectionDao::getInstance()->save($matchSection);
}

$dbPrefix = OW_DB_PREFIX;

try
{
    $sql = "UPDATE `{$dbPrefix}base_question` SET `onJoin` = '1', `required` = '1' WHERE `name` = 'match_age' OR `name`='birthdate'";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "UPDATE `{$dbPrefix}base_question` SET `sectionName` = 'about_my_match', `onEdit` = 0, `onJoin` = 0 WHERE `name` <> 'match_age' AND `name`<>'match_sex' AND `parent` <> '' AND `parent` IS NOT NULL";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "SELECT *  FROM `{$dbPrefix}base_question_section` WHERE `sortOrder` = 0";
    $sectionData = Updater::getDbo()->queryForRow($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "UPDATE `{$dbPrefix}base_question` SET `sectionName` = :sectionName WHERE `sectionName` = 'about_my_match' AND (`parent` = '' OR `parent` IS NULL)";
    Updater::getDbo()->query($sql, array('sectionName'=>$sectionData['name']));
}
catch ( Exception $e ) { }
