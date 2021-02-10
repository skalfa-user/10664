<?php

$langService = Updater::getLanguageService();

// import languages
$langService->importPrefixFromDir(__DIR__ . DS . 'langs', true);