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
 * Stripe admin controller
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.billing_paypal.controllers
 * @since 1.0
 */
class BILLINGSTRIPE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $billingService = BOL_BillingService::getInstance();
        $language = OW::getLanguage();
        $gwKey = BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY;

        $form = new BILLINGSTRIPE_CLASS_SettingsForm();
        $this->addForm($form);

        $form->getElement('livePK')->setValue($billingService->getGatewayConfigValue($gwKey, 'livePK'));
        $form->getElement('liveSK')->setValue($billingService->getGatewayConfigValue($gwKey, 'liveSK'));
        $form->getElement('testPK')->setValue($billingService->getGatewayConfigValue($gwKey, 'testPK'));
        $form->getElement('testSK')->setValue($billingService->getGatewayConfigValue($gwKey, 'testSK'));
        $form->getElement('sandboxMode')->setValue($billingService->getGatewayConfigValue($gwKey, 'sandboxMode'));
        $form->getElement('requireData')->setValue($billingService->getGatewayConfigValue($gwKey, 'requireData'));

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();

            $billingService->setGatewayConfigValue($gwKey, 'sandboxMode', $values['sandboxMode']);
            $billingService->setGatewayConfigValue($gwKey, 'requireData', $values['requireData']);
            $billingService->setGatewayConfigValue($gwKey, 'livePK', $values['livePK']);
            $billingService->setGatewayConfigValue($gwKey, 'liveSK', $values['liveSK']);
            $billingService->setGatewayConfigValue($gwKey, 'testPK', $values['testPK']);
            $billingService->setGatewayConfigValue($gwKey, 'testSK', $values['testSK']);

            // save view settings
            foreach ($values as $name => $val)
            {
                $configName = 'card_detail_' . $name;

                if (OW::getConfig()->configExists('billingstripe', $configName))
                {
                    OW::getConfig()->saveConfig('billingstripe', $configName, $val);
                }
            }

            OW::getFeedback()->info($language->text('billingstripe', 'settings_updated'));
            $this->redirect();
        }

        $adapter = new BILLINGSTRIPE_CLASS_StripeAdapter();
        $this->assign('logoUrl', $adapter->getLogoUrl());

        $gateway = $billingService->findGatewayByKey($gwKey);
        $this->assign('gateway', $gateway);

        $this->assign('activeCurrency', $billingService->getActiveCurrency());

        $supported = $billingService->currencyIsSupported($gateway->currencies);
        $this->assign('currSupported', $supported);

        $this->setPageHeading(OW::getLanguage()->text('billingstripe', 'config_page_heading'));

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('billingstripe')->getStaticCssUrl() . 'admin.css');
    }
}
