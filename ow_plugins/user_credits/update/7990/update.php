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

try
{
    $sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."usercredits_action_price` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `actionId` int(11) NOT NULL,
      `accountTypeId` int(11) NOT NULL,
      `amount` int(11) NOT NULL,
      `disabled` TINYINT(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `actionPrice` (`actionId`,`accountTypeId`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."usercredits_pack` ADD `accountTypeId` INT NULL DEFAULT NULL AFTER `id`";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) {  
    
}

try
{
    $sql = "SELECT * FROM `".OW_DB_PREFIX."base_question_account_type`
        ORDER BY `sortOrder` ASC";
    $accTypes = Updater::getDbo()->queryForList($sql);

    if ( $accTypes )
    {
        $default = true;

        $sql = "SELECT * FROM `".OW_DB_PREFIX."usercredits_action` ORDER BY `id` ASC";
        $actions = Updater::getDbo()->queryForList($sql);

        $sql = "SELECT * FROM `".OW_DB_PREFIX."usercredits_pack` ORDER BY `id` ASC";
        $packs = Updater::getDbo()->queryForList($sql);

        foreach ( $accTypes as $type )
        {
            if ( $actions )
            {
                // insert actions price
                foreach ( $actions as $action )
                {
                    // check if exists
                    $sql = "SELECT *  FROM `".OW_DB_PREFIX."usercredits_action_price`
                        WHERE `actionId` = :aid AND `accountTypeId` = :atid";
                    $params = array('aid' => $action['id'], 'atid' => $type['id']);
                    $actionPrice = Updater::getDbo()->queryForRow($sql, $params);

                    if ( $actionPrice )
                    {
                        continue;
                    }

                    $sql = "INSERT INTO `".OW_DB_PREFIX."usercredits_action_price`
                        SET `actionId` = :aid, `accountTypeId` = :atid, `amount` = :amt, `disabled` = 0";
                    $params = array('aid' => $action['id'], 'atid' => $type['id'], 'amt' => $action['amount']);
                    Updater::getDbo()->query($sql, $params);
                }
            }

            if ( $packs )
            {
                if ( $default )
                {
                    $sql = "UPDATE `".OW_DB_PREFIX."usercredits_pack`
                        SET `accountTypeId` = :atid";
                    Updater::getDbo()->query($sql, array('atid' => $type['id']));
                    $default = false;
                }
                else
                {
                    // insert packs
                    foreach ( $packs as $pack )
                    {
                        $sql = "SELECT * FROM `".OW_DB_PREFIX."usercredits_pack`
                            WHERE `accountTypeId` = :atid AND `credits` = :cred AND `price` = :price";
                        $params = array('atid' => $type['id'], 'cred' => $pack['credits'], 'price' => $pack['price']);
                        $p = Updater::getDbo()->queryForRow($sql, $params);

                        if ( $p )
                        {
                            continue;
                        }

                        $sql = "INSERT INTO `".OW_DB_PREFIX."usercredits_pack`
                            SET `accountTypeId` = :atid, `credits` = :cred, `price` = :price";
                        $params = array('atid' => $type['id'], 'cred' => $pack['credits'], 'price' => $pack['price']);
                        Updater::getDbo()->query($sql, $params);
                    }
                }
            }
        }
    }
}
catch ( Exception $e ){ }

try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."usercredits_action` DROP `amount`";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) {}

try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."usercredits_action` DROP `disabled`";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) {}

try
{
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('USERCREDITS_CMP_MyCreditsWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);
}
catch ( Exception $e ) { }

$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'usercredits');