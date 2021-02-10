<?php

class CVIDEOUPLOAD_CTRL_BuyCredits extends USERCREDITS_CTRL_BuyCredits
{
    public function index()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $service = CVIDEOUPLOAD_BOL_Service::getInstance();
        $billingService = BOL_BillingService::getInstance();

        if ( !empty($_GET[$service->getParamToSubscribeKey()]) && intval($_GET[$service->getParamToSubscribeKey()]) === 1 )
        {
            $service->setSessionBackUrl();
            $billingService->setSessionBackUrl(OW::getRouter()->urlForRoute('cvideoupload.video-upload'));
        }
        else
        {
            $service->unsetSessionBackUrl();
            $billingService->setSessionBackUrl(null);
        }

        parent::index();
    }
}