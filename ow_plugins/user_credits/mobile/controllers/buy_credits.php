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
 * User credits order page controller.
 *
 * @author Pryadkin Sergey <GiperProger@gmai.com>
 * @package ow_plugins.usrcredits.classes
 * @since 1.8.0
 */
class USERCREDITS_MCTRL_BuyCredits extends OW_MobileActionController
{
    /**
     * @var USERCREDITS_BOL_CreditsService
     */
    private $creditsService;
    private $userId;

    public function __construct()
    {
        $this->creditsService = USERCREDITS_BOL_CreditsService::getInstance();
        $this->userId = OW::getUser()->getId();
    }


    public function creditInfo()
    {
        $backUrl = OW::getRouter()->urlForRoute("usercredits.buy_credits");
        $masterPage = OW::getDocument()->getMasterPage();
        $masterPage->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
        $masterPage->setLButtonData(array(OW_MobileMasterPage::BTN_DATA_CLASS => "owm_nav_menu owm_nav_back", OW_MobileMasterPage::BTN_DATA_HREF => $backUrl));

        $accountTypeId = $this->creditsService->getUserAccountTypeId(OW::getUser()->getId());
        $earning = $this->creditsService->findCreditsActions('earn', $accountTypeId, false);
        $losing = $this->creditsService->findCreditsActions('lose', $accountTypeId, false);
        $balance = $this->creditsService->getCreditsBalance($this->userId);

        $this->assign('balance', $balance);
        $this->assign('losing', $losing);
        $this->assign('earning', $earning);

        $this->setPageHeading(OW::getLanguage()->text('usercredits', 'credit_rewards'));
    }

    public function subscribeCredits()
    {
        $balance = $this->creditsService->getCreditsBalance($this->userId);

        $accountTypeId = $this->creditsService->getUserAccountTypeId(OW::getUser()->getId());
        $packs = $this->creditsService->getPackList($accountTypeId);

        foreach($packs as &$pack)
        {
            $pack['title'] = str_replace('<b>',"",$pack['title']);
            $pack['title'] =  str_replace('</b>',"",$pack['title']);
            $pack['link'] = OW::getRouter()->urlForRoute('usercredits_pay_page', array('packId' => $pack['id']));
        }

        $creditInfoLink = OW::getRouter()->urlForRoute('usercredits_credit_info_mobile');

        $this->assign('creditInfoLink', $creditInfoLink);
        $this->assign('balance', $balance);
        $this->assign('packs', $packs);

        $menu = $this->getMenu();

        $this->addComponent('menu', $menu);

        $subscribePageHeaders = $this->membershipUsercreditsPluginInfo();

        $this->setPageHeading($subscribePageHeaders);
    }

    public function payPage( $params )
    {
        $backUrl = OW::getRouter()->urlForRoute("usercredits.buy_credits");
        $masterPage = OW::getDocument()->getMasterPage();
        $masterPage->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
        $masterPage->setLButtonData(array(OW_MobileMasterPage::BTN_DATA_CLASS => "owm_nav_menu owm_nav_back", OW_MobileMasterPage::BTN_DATA_HREF => $backUrl));

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $packId = $params['packId'];



        $form = new BuyCreditsForm();
        $this->addForm($form);

        $form->getElement('pack')->setValue($params['packId']);

        $accountTypeId = $this->creditsService->getUserAccountTypeId(OW::getUser()->getId());
        $packs = $this->creditsService->getPackList($accountTypeId);

        $packTitle = null;

        foreach($packs as $pack)
        {
            if($pack['id'] == $packId)
            {
                $packTitle = $pack['title'];
            }
        }

        $this->assign('packTitle', $packTitle);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();
            $lang = OW::getLanguage();
            $userId = OW::getUser()->getId();

            $billingService = BOL_BillingService::getInstance();

            if ( empty($values['gateway']['url']) || empty($values['gateway']['key'])
                || !$gateway = $billingService->findGatewayByKey($values['gateway']['key'])
                    || !$gateway->active )
            {
                OW::getFeedback()->error($lang->text('base', 'billing_gateway_not_found'));
                $this->redirect();
            }

            if ( !$pack = $this->creditsService->findPackById($values['pack']) )
            {
                OW::getFeedback()->error($lang->text('usercredits', 'pack_not_found'));
                $this->redirect();
            }

            // create pack product adapter object
            $productAdapter = new USERCREDITS_CLASS_UserCreditsPackProductAdapter();

            // sale object
            $sale = new BOL_BillingSale();
            $sale->pluginKey = 'usercredits';
            $sale->entityDescription = strip_tags($this->creditsService->getPackTitle($pack->price, $pack->credits));
            $sale->entityKey = $productAdapter->getProductKey();
            $sale->entityId = $pack->id;
            $sale->price = floatval($pack->price);
            $sale->period = 30;
            $sale->userId = $userId ? $userId : 0;
            $sale->recurring = 0;

            $saleId = $billingService->initSale($sale, $values['gateway']['key']);

            if ( $saleId )
            {
                // sale Id is temporarily stored in session
                $billingService->storeSaleInSession($saleId);
                $billingService->setSessionBackUrl($productAdapter->getProductOrderUrl());

                // redirect to gateway form page
                OW::getApplication()->redirect($values['gateway']['url']);
            }
        }

        $this->setPageHeading(OW::getLanguage()->text('usercredits', 'payment_provider'));

    }

    private function membershipUsercreditsPluginInfo()
    {
        $language = OW::getLanguage();

        if(OW::getPluginManager()->isPluginActive('membership'))
        {
            return $language->text('membership', 'membership_and_credits');
        }

        else
        {
            return $language->text('usercredits', 'credits');
        }
    }


    private function getMenu()
    {
        $menuArray = array();

        $event = new BASE_CLASS_EventCollector('base.collect_subscribe_menu');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if(count($data) == 1)
        {
            return new BASE_MCMP_ContentMenu();
        }


        if ( !empty($data) )
        {
            $menuArray = array_merge($menuArray, $data);
        }

        $menu = new BASE_MCMP_ContentMenu();

        foreach ( $menuArray as $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($item['label']);
            $menuItem->setIconClass($item['iconClass']);
            $menuItem->setUrl($item['url']);
            $menuItem->setKey($item['key']);
            $menuItem->setOrder(empty($item['order']) ? 999 : $item['order']);
            $menu->addElement($menuItem);

        }

        return $menu;
    }
}

/**
 * Buy credits form class
 */
class BuyCreditsForm extends Form
{

    public function __construct()
    {
        parent::__construct('buy-credits-form');

        $gatewaysField = new MobileBillingGatewaySelectionField('gateway');
        $gatewaysField->setRequired(true);
        $this->addElement($gatewaysField);

        OW::getDocument()->addOnloadScript(";
            var \$wrapper = $('#{$gatewaysField->getId()}').parents('ul'); 
            \$wrapper.find('input').bind('change', function(e){
                $('#{$gatewaysField->getId()}').parents('ul').find('input').each(function(i, el){
                    $(el).parent().removeClass('active');
                });
                var checked_element = \$wrapper.find('input:checked').get(0);
                $(checked_element).parent().addClass('active');
            });
        ");

        $pack = new HiddenField('pack');
        $this->addElement($pack);

        $submit = new Submit('buy');
        $submit->setValue(OW::getLanguage()->text('base', 'checkout'));
        $this->addElement($submit);
    }
}

