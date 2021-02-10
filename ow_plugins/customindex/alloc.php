<?php

UTIL_File::copyDir(
    __DIR__ . DS . 'files',
    OW::getPluginManager()->getPlugin('customindex')->getUserFilesDir()
);
