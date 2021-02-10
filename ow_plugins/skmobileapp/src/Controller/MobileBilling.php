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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use SKMOBILEAPP_BOL_PaymentsService;
use BOL_BillingService;

class MobileBilling extends Base
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

        // inits purchases
        $controllers->post('/inits/', function (Request $request, SilexApplication $app) {
            $billingSessionData = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();

            $saleId = SKMOBILEAPP_BOL_PaymentsService::getInstance()->initMobilePurchaseSession($billingSessionData, $loggedUserId);

            return $app->json((int) $saleId);
        });

        // get the sale info
        $controllers->get('/{id}/', function (SilexApplication $app, $id) {
            $sale = BOL_BillingService::getInstance()->getSaleById($id);
            $loggedUserId = $app['users']->getLoggedUserId();

            if (!$sale || $loggedUserId != $sale->userId) {
                throw new NotFoundHttpException('The sale not found');
            }

            $convertedSale = SKMOBILEAPP_BOL_PaymentsService::getInstance()->getMobileSaleFields($sale);

            return $app->json($convertedSale);
        });
 
        // finishes purchases
        $controllers->post('/finishes/', function (Request $request, SilexApplication $app) {
            $paymentService = SKMOBILEAPP_BOL_PaymentsService::getInstance();

            $creditCardData = json_decode($request->getContent(), true);
            $saleId = isset($creditCardData['saleId']) 
                ? $creditCardData['saleId'] 
                : 0;

            $token = $paymentService->processMobilePurchaseToken($creditCardData);
            $sale = BOL_BillingService::getInstance()->getSaleById($saleId);

            $result = $paymentService->processMobilePurchase($token, $sale);

            if ($result['status'] == 'error') {
                throw new BadRequestHttpException('Error during the payment process');
            }

            return $app->json([], 204);
        });

        // process purchases
        $controllers->post('/process/', function (Request $request, SilexApplication $app) {
            $paymentService = SKMOBILEAPP_BOL_PaymentsService::getInstance();

            $creditCardData = json_decode($request->getContent(), true);
            $saleId = isset($creditCardData['saleId'])
                ? $creditCardData['saleId']
                : 0;

            $sale = BOL_BillingService::getInstance()->getSaleById($saleId);

            $result = $paymentService->processMobilePurchaseStatus($sale, $creditCardData['options']);

            if (!empty($result['status']) &&  $result['status'] == 'error') {
                throw new BadRequestHttpException($result['message']);
            }

            return $app->json($result);
        });

        return $controllers;
    }
}
