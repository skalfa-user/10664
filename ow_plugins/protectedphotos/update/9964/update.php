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

Updater::getLanguageService()->importPrefixFromZip(dirname(dirname(__DIR__)) . DS . 'langs.zip', 'protectedphotos');

$sqls = array(
    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` ADD `privacy` VARCHAR(128) NOT NULL AFTER `albumId`;',
    'UPDATE `' . OW_DB_PREFIX . 'protectedphotos_passwords` SET `privacy` = "password"',
    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` ADD INDEX(`privacy`);',
    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` ADD `meta` TEXT NULL AFTER `privacy`;',
    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` CHANGE `password` `password` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;',
);

foreach ( $sqls as $sql )
{
    try
    {
        Updater::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }
}
