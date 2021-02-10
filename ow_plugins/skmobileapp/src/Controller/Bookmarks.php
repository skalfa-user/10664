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
use BOOKMARKS_BOL_Service;
use SKMOBILEAPP_BOL_BookmarksService;
use OW;

class Bookmarks extends Base
{
    /**
     * Max bookmarks
     */
    const MAX_BOOKMARKS = 300;
 
    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isPluginActive = false;


    /**
     * Users constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('bookmarks');
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

        // delete the bookmark
        $controllers->delete('/users/{id}/', function (SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();
                BOOKMARKS_BOL_Service::getInstance()->unmark($loggedUserId, $id);

                return $app->json([], 204); // ok
            }

            throw new BadRequestHttpException('Bookmarks plugin is not activated');
        });

        // create a bookmark
        $controllers->post('/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                $vars = json_decode($request->getContent(), true);
                $loggedUserId = $app['users']->getLoggedUserId();

                if (!empty($vars['userId'])) {
                    $bookmarkId = BOOKMARKS_BOL_Service::getInstance()->mark($loggedUserId, $vars['userId']);

                    return $app->json(SKMOBILEAPP_BOL_BookmarksService::getInstance()->formatBookmarkData($loggedUserId, [[
                        'id' => (int) $bookmarkId,
                        'markUserId' => (int) $vars['userId']
                    ]]));
                }

                throw new BadRequestHttpException('userId is missing');
            }

            throw new BadRequestHttpException('Bookmarks plugin is not activated');
        });

        // get all bookmarks
        $controllers->get('/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $bookmarks = SKMOBILEAPP_BOL_BookmarksService::
                        getInstance()->findLatestBookmarksUserIdList($loggedUserId, self::MAX_BOOKMARKS);


                return $app->json(SKMOBILEAPP_BOL_BookmarksService::getInstance()->formatBookmarkData($loggedUserId, $bookmarks));
            }

            throw new BadRequestHttpException('Bookmarks plugin is not activated');
        });

        return $controllers;
    }
}
