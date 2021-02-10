<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

@mkdir(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS );
@chmod(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS, 0777);
@mkdir(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'avatars' . DS);
@chmod(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'avatars' . DS, 0777);

@mkdir(OW_DIR_PLUGINFILES . 'skadate' . DS );
@chmod(OW_DIR_PLUGINFILES . 'skadate' . DS, 0777);
@mkdir(OW_DIR_PLUGINFILES . 'skadate' . DS . 'avatars' . DS);
@chmod(OW_DIR_PLUGINFILES . 'skadate' . DS . 'avatars' . DS, 0777);
