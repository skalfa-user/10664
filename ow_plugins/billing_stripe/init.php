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

OW::getRouter()->addRoute(
    new OW_Route('billingstripe.admin', 'admin/billing-stripe', 'BILLINGSTRIPE_CTRL_Admin', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('billingstripe.order_form', 'stripe/order', 'BILLINGSTRIPE_CTRL_Action', 'orderForm')
);

OW::getRouter()->addRoute(
    new OW_Route('billingstripe.process_sale', 'billing-stripe/process-sale', 'BILLINGSTRIPE_CTRL_Action', 'processSale')
);

OW::getRouter()->addRoute(
    new OW_Route('billingstripe.after_sale', 'billing-stripe/after-sale', 'BILLINGSTRIPE_CTRL_Action', 'afterSale')
);

OW::getRouter()->addRoute(
    new OW_Route('billingstripe.webhook', 'billing-stripe/webhook', 'BILLINGSTRIPE_CTRL_Action', 'webhook')
);

BILLINGSTRIPE_CLASS_EventHandler::getInstance()->init();

$dir = OW::getPluginManager()->getPlugin('billingstripe')->getClassesDir();
require_once $dir . 'stripe' . DS . 'init.php';
