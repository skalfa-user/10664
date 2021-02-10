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
use SKMOBILEAPP_BOL_GuestsService;
use OW;

class Guests extends Base
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

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('ocsguests');
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

        // delete guest
        $controllers->delete('/{id}/', function (SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();

                // check ownership
                $guest = SKMOBILEAPP_BOL_GuestsService::getInstance()->findGuestById($id, $loggedUserId);

                if ($guest) {
                    SKMOBILEAPP_BOL_GuestsService::getInstance()->deleteGuestById($id);

                    return $app->json([], 204);
                }

                throw new BadRequestHttpException('Guest cannot be deleted');
            }

            throw new BadRequestHttpException('Guests plugin is not activated');
        });

        // mark all guests as read
        $controllers->put('/me/mark-all-as-read/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                SKMOBILEAPP_BOL_GuestsService::getInstance()->
                        markAllGuestsAsRead($app['users']->getLoggedUserId());

                return $app->json([], 204);
            }

            throw new BadRequestHttpException('Guests plugin is not activated');
        });

        return $controllers;
    }
}
