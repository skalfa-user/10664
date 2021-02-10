<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.7.0
 */

@mkdir(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'avatars' . DS);
@chmod(OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'avatars' . DS, 0777);

@mkdir(OW_DIR_PLUGINFILES . 'skadate' . DS . 'avatars' . DS);
@chmod(OW_DIR_PLUGINFILES . 'skadate' . DS . 'avatars' . DS, 0777);

$source = OW_DIR_PLUGIN . 'skadate' . DS . 'update' . DS . '9612' . DS . 'mobile_promo_image.jpg';
$dest = OW_DIR_USERFILES . 'plugins' . DS . 'skadate' . DS . 'mobile_promo_image.jpg';

@copy($source, $dest);
@chmod($dest, 0666);