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
 * CCBillFlex admin controller
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 */
class BILLINGCCBILLFLEX_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @var string
     */
    protected $pluginKey;

    public function __construct()
    {
        parent::__construct();

        $this->pluginKey = BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter::GATEWAY_KEY;
    }

    public function index()
    {
        $billingService = BOL_BillingService::getInstance();
        $language = OW::getLanguage();

        $ccbillConfigForm = new BILLINGCCBILLFLEX_CLASS_SettingsForm();
        $this->addForm($ccbillConfigForm);

        if( OW::getPluginManager()->getPlugin('usercredits')->isActive() ){
            if(OW::getPluginManager()->getPlugin('usercredits')->getDto()->build <= 11000){
                OW::getFeedback()->error(OW::getLanguage()->text($this->pluginKey, 'user_credit_plugin_version_problem'));
            }
        }

        if ( OW::getRequest()->isPost() && $ccbillConfigForm->isValid($_POST) )
        {
            $ccbillConfigForm->process();
            OW::getFeedback()->info($language->text($this->pluginKey, 'settings_updated'));
            $this->redirect();
        }

        $adapter = new BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter();
        $this->assign('logoUrl', $adapter->getLogoUrl());

        $gateway = $billingService->findGatewayByKey(BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter::GATEWAY_KEY);
        $this->assign('gateway', $gateway);

        $this->assign('activeCurrency', $billingService->getActiveCurrency());

        $supported = $billingService->currencyIsSupported($gateway->currencies);
        $this->assign('currSupported', $supported);

        $subAccounts = $adapter->getAdditionalSubaccounts();
        $this->assign('subAccounts', $subAccounts);

        $this->setPageHeading(OW::getLanguage()->text($this->pluginKey, 'config_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_app');

        $this->assign('pluginKey', $this->pluginKey);
    }
}

