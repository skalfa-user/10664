<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

try
{
    $sql = " CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "skadate_account_type_to_gender` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `accountType` varchar(32) NOT NULL,
      `genderValue` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

try
{
    $sql = " `ALTER IGNORE TABLE `" . OW_DB_PREFIX . "skadate_account_type_to_gender` DROP INDEX accountType;` ";

    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    //print_r($e);
}

try
{
    $sql = " ALTER IGNORE TABLE `" . OW_DB_PREFIX . "skadate_account_type_to_gender` ADD UNIQUE INDEX `accountType` ( `accountType` ) ";

    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    //print_r($e);
}

try
{
    $sql = " `ALTER IGNORE TABLE `" . OW_DB_PREFIX . "skadate_account_type_to_gender` DROP INDEX genderValue;` ";

    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    //print_r($e);
}


try
{
    $sql = " ALTER IGNORE TABLE `" . OW_DB_PREFIX . "skadate_account_type_to_gender` ADD UNIQUE INDEX `genderValue` ( `genderValue` ) ";

    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    //printVar($e);
}

try
{
   $sql = " UPDATE `" . OW_DB_PREFIX . "base_question` SET `required` = 1, `onJoin` = 1, `onEdit` = 0, `onSearch` = 0, `onView` = 1 WHERE `name` = 'sex'";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

try
{
   $sql = " UPDATE `" . OW_DB_PREFIX . "base_question` SET `onJoin` = 1, `onSearch` = 1 WHERE `name` = 'match_sex'";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}


try
{
   $sql = " UPDATE `" . OW_DB_PREFIX . "base_question` set sortOrder = 21  WHERE `name` IN ( 'joinStamp' ) AND sortOrder = 0";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

try
{
   $sql = "UPDATE `" . OW_DB_PREFIX . "base_question` set sortOrder = 22  WHERE `name` IN ( '9f427b5a957edde93cc955fa13971799' ) AND sortOrder = 0";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

$values = BOL_QuestionService::getInstance()->findQuestionValues('sex');
$genderToAccountType = array();

foreach ( $values as $value )
{
    /* @var $value BOL_QuestionValue */
    $accountType = null;

    try
    {
        //$accountType = BOL_QuestionService::getInstance()->createAccountType(md5(rand(0,999999999) + $value->value), BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $value->value));

        $accountTypeName = md5(rand(0,999999999) + $value->value);
        $label = BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $value->value);
        $roleId = 1;

        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($accountTypeName);

        if ( !empty($accountType) )
        {
            return;
        }

        $role = BOL_AuthorizationService::getInstance()->getRoleById($roleId);

        if ( empty($role) )
        {
            $role = BOL_AuthorizationService::getInstance()->getDefaultRole();
        }

        $accountType = new BOL_QuestionAccountType();
        $accountType->name = $accountTypeName;
        $accountType->sortOrder = (BOL_QuestionService::getInstance()->findLastAccountTypeOrder()) + 1;
        $accountType->roleId = $role->id;

        BOL_QuestionService::getInstance()->saveOrUpdateAccountType($accountType);

        //$event = new OW_Event(self::EVENT_ON_ACCOUNT_TYPE_ADD, array('dto' => $accountType, 'id' => $accountType->id));
        //OW::getEventManager()->trigger($event);

        $questions = BOL_QuestionService::getInstance()->findAllQuestions();

        $questionNameList = array();

        foreach ( $questions as $question )
        {
            /* @var $question BOL_Question */
            if ( $question->base == 1 )
            {
                $questionNameList[$question->name] = $question->name;
            }
        }

        //$event = new OW_Event(self::EVENT_BEFORE_ADD_QUESTIONS_TO_NEW_ACCOUNT_TYPE, array('dto' => $accountType), $questionNameList);
        //OW::getEventManager()->trigger($event);

        //$questionNameList = $event->getData();

        BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList($questionNameList, array($accountType->name));

        if ( !empty($label) )
        {
            $prefix = 'base';
            $key = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_ACCOUNT_TYPE, $accountTypeName);

            $languageService = Updater::getLanguageService();
            $currentLanguageId = $languageService->getCurrent()->getId();
            $currentLangValue = "";

            $keyDto = $languageService->findKey($prefix, $key);

            if ( empty($keyDto) )
            {
                $prefixDto = $languageService->findPrefix($prefix);
                $keyDto = $languageService->addKey($prefixDto->id, $key);
            }

            $label = trim($label);

            if ( mb_strlen(trim($label)) == 0 || $label == json_decode('"\u00a0"') ) // stupid hack
            {
                $label = '&nbsp;';
            }

            $dto = $languageService->findValue($currentLanguageId, $keyDto->getId());

            if ( $dto !== null )
            {
                if ( $dto->getValue() !== $label )
                {
                    $languageService->saveValue($dto->setValue($label), false);
                }
            }
            else
            {
                $dto = $languageService->addOrUpdateValue($currentLanguageId, $prefix, $key, $label, false);
            }
        }

        BOL_QuestionService::getInstance()->updateQuestionsEditStamp();

        /* @var $accountType BOL_QusetionAccountType */
    }
    catch ( Exception $e )
    {
        print_r($e);
    }

    if ( !empty($accountType) )
    {
        $genderToAccountType[$value->value] = $accountType->name;
    }
    
    try
    {
        $sql = " REPLACE INTO `" . OW_DB_PREFIX . "skadate_account_type_to_gender` ( `accountType`, `genderValue` )
            VALUES ( '".Updater::getDbo()->escapeString($accountType->name)."',  '".Updater::getDbo()->escapeString($value->value)."' ) ";

        Updater::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        print_r($e);
    }

    try
    {
       $sql = " UPDATE `" . OW_DB_PREFIX . "base_user` u INNER JOIN `" . OW_DB_PREFIX . "base_question_data` d ON ( u.id = d.userId  )

            SET u.accountType =  '".Updater::getDbo()->escapeString($accountType->name)."'

           WHERE d.questionName = 'sex' AND d.`intValue` = '".Updater::getDbo()->escapeString($value->value)."' ";

        Updater::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        print_r($e);
    }

    try
    {
       $sql =  " REPLACE INTO `".OW_DB_PREFIX."base_question_to_account_type` ( `questionName`, `accountType` ) SELECT q.name, a.name
        FROM  `".OW_DB_PREFIX."base_question` q, `".OW_DB_PREFIX."base_question_account_type` a
        WHERE 1 ";

        Updater::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        print_r($e);
    }

}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'skadate');

try 
{
   Updater::getLanguageService()->deleteLangKey('base', 'questions_question_9f427b5a957edde93cc955fa13971799_label');
   Updater::getLanguageService()->addValue('base', 'questions_question_9f427b5a957edde93cc955fa13971799_label', 'About the person I want to meet');
}
catch (Exception $ex) 
{
   print_r($ex);
}


try
{
    $sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."skadate_speedmatch_relation` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `oppUserId` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL DEFAULT '0',
      `addTimestamp` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `relation` (`userId`,`oppUserId`),
      KEY `oppUserId` (`oppUserId`),
      KEY `userId` (`userId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

try
{
    $sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."skadate_current_location` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `latitude` decimal(12,8) NOT NULL,
      `longitude` decimal(12,8) NOT NULL,
      `updateTimestamp` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `userId` (`userId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

try
{
    $sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."skadate_avatar` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `avatarId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      `hash` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `avatarId` (`avatarId`,`userId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    print_r($e);
}

try
{
    $sql = "UPDATE `" . OW_DB_PREFIX . "base_plugin` SET developerKey=:dk  WHERE `key`=:k";

    Updater::getDbo()->query($sql, array(
        "dk" => "99d6bdd5bb6468beaf118c4664dd92ff",
        "k" => "pcgallery"
    ));
}
catch ( Exception $e )
{
    print_r($e);
}

if( !OW::getConfig()->configExists('skadate', 'update_gender_values') )
{
    OW::getConfig()->addConfig('skadate', 'update_gender_values', true);
}
else 
{
    OW::getConfig()->saveConfig('skadate', 'update_gender_values', true);
}

if( !Updater::getConfigService()->configExists('skadate', 'photo_filter_setting_matching') )
{
    Updater::getConfigService()->addConfig('skadate', 'photo_filter_setting_matching', false);
}

@mkdir(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'avatars' . DS);
@chmod(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'avatars' . DS, 0777);

@mkdir(OW_DIR_PLUGINFILES . 'skadate' . DS . 'avatars' . DS);
@chmod(OW_DIR_PLUGINFILES . 'skadate' . DS . 'avatars' . DS, 0777);
