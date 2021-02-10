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
namespace Skadate\Mobile\Controller;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use SKMOBILEAPP_BOL_PaymentsService;
use BOL_BillingService;
use OW;

class BillingGateways extends Base
{
    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // get all active gateways supported mobile platform
        $controllers->get('/', function (SilexApplication $app) {
            $gatewaysKeys = [];
            $gatewaysList = BOL_BillingService::getInstance()->getActiveGatewaysList(true);

            foreach ($gatewaysList as $item) {
                if (!in_array($item->gatewayKey, SKMOBILEAPP_BOL_PaymentsService::$allowedMobileBillingGateways)) {
                    continue;
                }

                $gatewaysKeys[] = [
                    'name' => $item->gatewayKey,
                    'isRedirectable' => in_array($item->gatewayKey, SKMOBILEAPP_BOL_PaymentsService::$redirectableMobileBillingGateways)
                ];
            }

            return $app->json($gatewaysKeys);
        });

        // get pay pall gateway data
        $controllers->get('/billingpaypal/', function (SilexApplication $app) {
            if (!OW::getPluginManager()->isPluginActive('billingpaypal')) {
                throw new BadRequestHttpException('Billing paypal plugin is not activated');
            }

            return $app->json(SKMOBILEAPP_BOL_PaymentsService::getInstance()->getMobilePayPalGatewayData());
        });

        // get stripe gateway data
        $controllers->get('/billingstripe/', function (SilexApplication $app) {
            if (!OW::getPluginManager()->isPluginActive('billingstripe')) {
                throw new BadRequestHttpException('Billing stripe plugin is not activated');
            }

            return $app->json([
                'questions' => SKMOBILEAPP_BOL_PaymentsService::getInstance()->getMobileStripeGatewayData(),
                'options' => ['publishKey' => SKMOBILEAPP_BOL_PaymentsService::getInstance()->getStripePublishKey()]
            ]);
        });

        return $controllers;
    }
}
