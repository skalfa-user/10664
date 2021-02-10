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

OW::getRouter()->addRoute(new OW_Route('billing_paypal_order_form', 'billing-paypal/order', 'BILLINGPAYPAL_MCTRL_Order', 'form'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_notify', 'billing-paypal/order/notify', 'BILLINGPAYPAL_MCTRL_Order', 'notify'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_completed', 'billing-paypal/order/completed/', 'BILLINGPAYPAL_MCTRL_Order', 'completed'));
OW::getRouter()->addRoute(new OW_Route('billing_paypal_canceled', 'billing-paypal/order/canceled/', 'BILLINGPAYPAL_MCTRL_Order', 'canceled'));

//BILLINGPAYPAL_CLASS_EventHandler::getInstance()->init();