<?php

/**
 * CCBill Settings Form Class.
 *
 * @author Sergey Pryadkin <GIperProger@gmail.com>
 * @package ow.ow_plugins.billing_ccbill_flex.classes
 * @since 1.8.6
 */

class BILLINGCCBILLFLEX_CLASS_SettingsForm extends Form
{
    /**
     * @var OW_Language
     */
    protected $lang;

    /**
     * @var BOL_BillingService
     */
    protected $billingService;

    /**
     * @var string
     */
    protected $pluginKey;

    public function __construct()
    {
        parent::__construct('ccbill-flex-config-form');

        $this->lang = OW::getLanguage();
        $this->billingService = BOL_BillingService::getInstance();
        $this->pluginKey = BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter::GATEWAY_KEY;

        $clientAccnum = new TextField('clientAccnum');
        $clientAccnum->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'clientAccnum'));
        $this->addElement($clientAccnum);

        $clientSubacc = new TextField('clientSubacc');
        $clientSubacc->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'clientSubacc'));
        $this->addElement($clientSubacc);

        $adapter = new BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter();
        $subAccounts = $adapter->getAdditionalSubaccounts();

        if ( $subAccounts )
        {
            foreach ( $subAccounts as $key => $sub )
            {
                $field = new TextField($key);
                $field->setLabel($sub['label']);
                $field->setValue($sub['value']);
                $this->addElement($field);
            }
        }

        $dynamicPricingSalt = new TextField('dynamicPricingSalt');
        $dynamicPricingSalt->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'dynamicPricingSalt'));
        $this->addElement($dynamicPricingSalt);

        $flexFormId = new TextField('flexFormId');
        $flexFormId->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'flexFormId'));
        $this->addElement($flexFormId);

        $datalinkUsername = new TextField('datalinkUsername');
        $datalinkUsername->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'datalinkUsername'));
        $this->addElement($datalinkUsername);

        $datalinkPassword = new PasswordField('datalinkPassword');
        $datalinkPassword->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'datalinkPassword'));
        $this->addElement($datalinkPassword);

        $sandBox = new CheckboxField('sandbox');
        $sandBox->setValue($this->billingService->getGatewayConfigValue($this->pluginKey, 'sandbox'));
        $this->addElement($sandBox);

        // submit
        $submit = new Submit('save');
        $submit->setValue($this->lang->text($this->pluginKey, 'btn_save'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();

        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter::GATEWAY_KEY;

        $billingService->setGatewayConfigValue($gwKey, 'clientAccnum', $values['clientAccnum']);
        $billingService->setGatewayConfigValue($gwKey, 'clientSubacc', $values['clientSubacc']);
        $billingService->setGatewayConfigValue($gwKey, 'dynamicPricingSalt', $values['dynamicPricingSalt']);
        $billingService->setGatewayConfigValue($gwKey, 'datalinkUsername', $values['datalinkUsername']);
        $billingService->setGatewayConfigValue($gwKey, 'datalinkPassword', $values['datalinkPassword']);
        $billingService->setGatewayConfigValue($gwKey, 'flexFormId', $values['flexFormId']);
        $billingService->setGatewayConfigValue($gwKey, 'sandbox', $values['sandbox']);

        // update additional sub-account values
        $adapter = new BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter();
        $subAccounts = $adapter->getAdditionalSubaccounts();

        if ( $subAccounts )
        {
            foreach ( $subAccounts as $key => $sub )
            {
                if ( array_key_exists($key, $values) )
                {
                    $billingService->setGatewayConfigValue($gwKey, $key, $values[$key]);
                }
            }
        }
    }
}