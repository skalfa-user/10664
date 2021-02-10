<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

OW::getRouter()->addRoute(new OW_Route('customindex.admin', 'customindex/admin', 'CUSTOMINDEX_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('customindex.admin-banner', 'customindex/admin/banner', 'CUSTOMINDEX_CTRL_Admin', 'bannerManager'));
OW::getRouter()->addRoute(new OW_Route('customindex.admin-banner-id', 'customindex/admin/banner/:id', 'CUSTOMINDEX_CTRL_Admin', 'bannerManager'));
OW::getRouter()->addRoute(new OW_Route('customindex.admin-banner-delete', 'customindex/admin/banner/delete', 'CUSTOMINDEX_CTRL_Admin', 'deleteBanner'));
OW::getRouter()->addRoute(new OW_Route('customindex.admin-banner-id-save', 'customindex/admin/banner/save/:id', 'CUSTOMINDEX_CTRL_Admin', 'updateBanner'));

OW::getRouter()->addRoute(new OW_Route('customindex_submit_handler', 'customindex/submit', 'CUSTOMINDEX_CTRL_Join', 'joinFormSubmit'));

$handler = new CUSTOMINDEX_CLASS_EventHandler();
$handler->init();
