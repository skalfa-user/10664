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
use Symfony\Component\HttpFoundation\Request;
use SKMOBILEAPP_BOL_Service;
use MEMBERSHIP_BOL_MembershipService;
use SKMOBILEAPP_BOL_PaymentsService;
use OW;

class Memberships extends Base
{
    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isPluginActive = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive(SKMOBILEAPP_BOL_Service::MEMBERSHIP_PLUGIN_KEY);
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

        // get all memberships
        $controllers->get('/', function () use ($app) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();

                return $app->json(SKMOBILEAPP_BOL_PaymentsService::getInstance()->getMemberships($loggedUserId));
            }

            throw new BadRequestHttpException('Membership plugin is not activated');
        });

        // get membership info
        $controllers->get('/{id}/', function ($id) use ($app) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();

                return $app->json(SKMOBILEAPP_BOL_PaymentsService::getInstance()->getFullMembershipInfo($id, $loggedUserId));
            }

            throw new BadRequestHttpException('Membership plugin is not activated');
        });

        // add a trial plan
        $controllers->post('/trial/{id}/', function (Request $request, SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $membershipService = MEMBERSHIP_BOL_MembershipService::getInstance();

                // check if any trial plan is used
                if ($membershipService->isTrialUsedByUser($loggedUserId)) {
                    throw new BadRequestHttpException('You have already used a trial plan');
                }

                // add the plan
                $plan = $membershipService->findPlanById($id);

                if ($plan) {
                    SKMOBILEAPP_BOL_PaymentsService::getInstance()->addTrialMembership($loggedUserId, $plan);

                    return $app->json([], 204);
                }

                throw new BadRequestHttpException('The requested plan is missing');
            }

            throw new BadRequestHttpException('Membership plugin is not activated');
        });
 
        return $controllers;
    }
}
