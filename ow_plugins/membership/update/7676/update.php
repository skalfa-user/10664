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

$widgetService = Updater::getWidgetService();

try {
    $widget = $widgetService->addWidget('MEMBERSHIP_CMP_PromoWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_SIDEBAR);
}
catch ( Exception $e ) { }

try
{
    $sql = "ALTER TABLE `" . OW_DB_PREFIX . "membership_type` ADD `accountTypeId` INT NULL DEFAULT NULL,
        ADD INDEX ( `accountTypeId` )";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "membership_user_trial` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `planId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      `startStamp` int(11) NOT NULL,
      `expirationStamp` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `userId` (`userId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "ALTER TABLE `" . OW_DB_PREFIX . "membership_user` ADD `trial` TINYINT( 1 ) NULL DEFAULT '0'";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "SELECT * FROM `" . OW_DB_PREFIX . "membership_type` WHERE `accountTypeId` IS NULL";
    $msList = Updater::getDbo()->queryForList($sql);

    if ( $msList )
    {
        $sql = "SELECT * FROM `" . OW_DB_PREFIX . "base_question_account_type` ORDER BY `sortOrder`";
        $accTypes = Updater::getDbo()->queryForList($sql);

        $acc = array_shift($accTypes);

        $sql = "UPDATE `" . OW_DB_PREFIX . "membership_type`
            SET `accountTypeId` = :accId";

        Updater::getDbo()->query($sql, array('accId' => $acc['id']));

        if ( count($accTypes) )
        {
            foreach ( $accTypes as $acc )
            {
                 $sql = "INSERT INTO `" . OW_DB_PREFIX . "membership_type`
                    SET roleId = :roleId, `accountTypeId` = :accId";

                foreach ( $msList as $ms )
                {
                    Updater::getDbo()->query($sql, array('roleId' => $ms['roleId'], 'accId' => $acc['id']));
                }
            }
        }
    }
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'membership');