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

OW::getRouter()->addRoute(new OW_Route('winks.rsp', 'winks/rsp', 'WINKS_CTRL_Winks', 'ajaxRsp'));

WINKS_CLASS_EventHandler::getInstance()->init();
