<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$route = OW::getRouter();

$route->addRoute(new OW_Route('gdpr-sendEmail', 'gdpr/sendEmail', "GDPR_MCTRL_Email", 'sendEmail'));
$route->addRoute(new OW_Route('gdpr-request-download', 'gdpr/request-download', "GDPR_CTRL_Email", 'requestDownload'));
$route->addRoute(new OW_Route('gdpr-request-deletion', 'gdpr/request-deletion', "GDPR_CTRL_Email", 'requestDeletion'));
$route->addRoute(new OW_Route('mobile_profile_edit', 'mobileprofile/edit', 'GDPR_MCTRL_Email', 'editProfile'));

GDPR_MCLASS_EventHandler::getInstance()->init();