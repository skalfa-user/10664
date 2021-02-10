<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'badwords_badwords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$config = OW::getConfig();

if ( !$config->configExists('badwords', 'censorText') )
{
    $config->addConfig('badwords', 'censorText', '#censored#');
}

if ( !$config->configExists( 'badwords', 'censorColor') )
{
    $config->addConfig('badwords', 'censorColor', '#FC0808');
}

OW::getPluginManager()->addPluginSettingsRouteName('badwords', 'badwords.admin');
