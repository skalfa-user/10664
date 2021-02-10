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

SMSVERIFICATION_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('smsverification_admin', 'admin/smsverification', 'SMSVERIFICATION_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('smsverification_prepare_data', 'verification/input_number', 'SMSVERIFICATION_CTRL_MainController', 'inputNumber'));
OW::getRouter()->addRoute(new OW_Route('smsverification_send_code', 'verification/input_code', 'SMSVERIFICATION_CTRL_MainController', 'inputCode'));