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

/**
 * PayPal admin controller
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.billing_paypal.controllers
 * @since 1.0
 */
class BILLINGPAYPAL_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $billingService = BOL_BillingService::getInstance();
        $language = OW::getLanguage();

        $paypalConfigForm = new PaypalConfigForm();
        $this->addForm($paypalConfigForm);

        if ( OW::getRequest()->isPost() && $paypalConfigForm->isValid($_POST) )
        {
            $res = $paypalConfigForm->process();
            OW::getFeedback()->info($language->text('billingpaypal', 'settings_updated'));
            $this->redirect();
        }

        $adapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();
        $this->assign('logoUrl', $adapter->getLogoUrl());

        $gateway = $billingService->findGatewayByKey(BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY);
        $this->assign('gateway', $gateway);

        $this->assign('activeCurrency', $billingService->getActiveCurrency());

        $supported = $billingService->currencyIsSupported($gateway->currencies);
        $this->assign('currSupported', $supported);

        $this->setPageHeading(OW::getLanguage()->text('billingpaypal', 'config_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_app');
    }
}

class PaypalConfigForm extends Form
{

    public function __construct()
    {
        parent::__construct('paypal-config-form');


        $language = OW::getLanguage();
        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY;

        $business = new TextField('business');
        $business->setValue($billingService->getGatewayConfigValue($gwKey, 'business'));
        $this->addElement($business);

        $sandboxMode = new CheckboxField('sandboxMode');
        $sandboxMode->setValue($billingService->getGatewayConfigValue($gwKey, 'sandboxMode'));
        $this->addElement($sandboxMode);

        $shippingAdress = new CheckboxField('shippingAddress');
        $shippingAdress->setValue($billingService->getGatewayConfigValue($gwKey, 'shippingAddress'));
        $this->addElement($shippingAdress);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('billingpaypal', 'btn_save'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();

        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY;

        $billingService->setGatewayConfigValue($gwKey, 'business', $values['business']);
        $billingService->setGatewayConfigValue($gwKey, 'sandboxMode', $values['sandboxMode']);
        $billingService->setGatewayConfigValue($gwKey, 'shippingAddress', $values['shippingAddress']);
    }
}