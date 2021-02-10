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

OW::getRouter()->addRoute(new OW_Route('videoim_admin_config', 'admin/plugins/videoim', 'VIDEOIM_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('videoim_admin_settings', 'admin/plugins/videoim/settings', 'VIDEOIM_CTRL_Admin', 'settings'));

VIDEOIM_CLASS_EventHandler::getInstance()->init();