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

$route->addRoute(new OW_Route('gdpr-admin-index', 'admin/plugins/gdpr/', "GDPR_CTRL_Admin", 'index'));
$route->addRoute(new OW_Route('gdpr-admin', 'gdpr/admin/:listType/', "GDPR_CTRL_Admin", 'index'));
$route->addRoute(new OW_Route('gdpr-create', 'gdpr/admin/search/', "GDPR_CTRL_Admin", 'search'));
$route->addRoute(new OW_Route('gdpr-export-csv', 'gdpr/admin/export-csv', "GDPR_CTRL_Admin", 'exportCsv'));
$route->addRoute(new OW_Route('gdpr-sendEmail', 'gdpr/sendEmail', "GDPR_CTRL_Email", 'sendEmail'));
$route->addRoute(new OW_Route('gdpr-request-download', 'gdpr/request-download', "GDPR_CTRL_Email", 'requestDownload'));
$route->addRoute(new OW_Route('gdpr-request-deletion', 'gdpr/request-deletion', "GDPR_CTRL_Email", 'requestDeletion'));
$route->addRoute(new OW_Route('gdpr-update-lang', 'admin/gdpr/ajaxUpdateLang', "GDPR_CTRL_Admin", 'ajaxUpdateLang'));

GDPR_CLASS_EventHandler::getInstance()->init();