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

Updater::getLanguageService()->deleteLangKey('slpremiumtheme', 'paramount_promo_info_1');
Updater::getLanguageService()->deleteLangKey('slpremiumtheme', 'ow_salaam_index_widget');
Updater::getLanguageService()->deleteLangKey('slpremiumtheme', 'mosaic_promo_info_1');

$updateDir = dirname(dirname(__FILE__)) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'slpremiumtheme');
