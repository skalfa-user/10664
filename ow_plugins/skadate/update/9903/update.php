<?php

/**
 * Copyright (c) 2015, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

Updater::getLanguageService()->deleteLangKey('skadate', 'turquoise_index_mobile');
Updater::getLanguageService()->deleteLangKey('skadate', 'turquoise_index_match');

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'skadate');
