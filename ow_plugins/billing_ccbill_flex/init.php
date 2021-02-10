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

$pluginKey = 'billingccbillflex';


OW::getRouter()->addRoute(new OW_Route($pluginKey.'_order_form', 'billing-ccbill-flex/order/', strtoupper($pluginKey).'_CTRL_Order', 'form'));
OW::getRouter()->addRoute(new OW_Route($pluginKey.'_admin', 'admin/billing-ccbill-flex', strtoupper($pluginKey).'_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route($pluginKey.'_postback', 'billingccbillflex/postback', strtoupper($pluginKey).'_CTRL_PostBackHandler', 'postBack'));
