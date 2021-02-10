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

$activeTypes = json_decode(Updater::getConfigService()->getValue("moderation", "content_types"), true);
$activeTypes["user_join"] = (bool)Updater::getConfigService()->getValue("base", "mandatory_user_approve");

Updater::getConfigService()->saveConfig("moderation", "content_types", json_encode($activeTypes));