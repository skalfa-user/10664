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

OW::getRouter()->addRoute(new OW_Route('billing_paypal_order_form', 'billing-paypal/order', 'BILLINGPAYPAL_CTRL_Order', 'form'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_notify', 'billing-paypal/order/notify', 'BILLINGPAYPAL_CTRL_Order', 'notify'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_completed', 'billing-paypal/order/completed/', 'BILLINGPAYPAL_CTRL_Order', 'completed'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_canceled', 'billing-paypal/order/canceled/', 'BILLINGPAYPAL_CTRL_Order', 'canceled'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_admin', 'admin/billing-paypal', 'BILLINGPAYPAL_CTRL_Admin', 'index'));

BILLINGPAYPAL_CLASS_EventHandler::getInstance()->init();