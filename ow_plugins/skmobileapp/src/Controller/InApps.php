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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use SKMOBILEAPP_BOL_PaymentsService;
use SKMOBILEAPP_BOL_Service;
use OW;

class InApps extends Base
{
    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isMembershipPluginActive = false;
    protected $isUserCreditsPluginActive = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isMembershipPluginActive = OW::getPluginManager()->isPluginActive(SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY);
        $this->isUserCreditsPluginActive = OW::getPluginManager()->isPluginActive(SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY);
    }

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

        // register a purchase
        $controllers->post('/', function (Request $request) use ($app) {
            if ( $this->isMembershipPluginActive || $this->isUserCreditsPluginActive ) {
                $paymentsService = SKMOBILEAPP_BOL_PaymentsService::getInstance();

                $loggedUserId = $app['users']->getLoggedUserId();
                $data = json_decode($request->getContent(), true);

                // add to log the purchase
                $logger = OW::getLogger('skmobileapp');
                $logger->addEntry(print_r($data, true), 'inapps_data');
                $logger->writeLog();

                // the purchase's platforms is undefined
                if ($data['platform'] !== 'android' && $data['platform'] !== 'ios') {
                    throw new BadRequestHttpException('The platform is not defined');
                }

                // validate purchase
                $result = $data['platform'] == 'android'
                    ? $paymentsService->validateAndroidPurchase($data)
                    : $paymentsService->validateIOSPurchase($data);

                // add to log the purchase validation result
                $logger->addEntry(print_r($result, true), 'inapps_validate_result');
                $logger->writeLog();

                if ($result && $result['status']) {
                    $paymentsService->createAndDeliver($result, $data, $loggedUserId);

                    return $app->json([], 204);
                }

                throw new BadRequestHttpException('The purchase in not valid');
            }

            throw new BadRequestHttpException('Membership or UserCredits plugin is not activated');
        });

        return $controllers;
    }
}
