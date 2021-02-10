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
namespace Skadate\Mobile\Middleware;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OW;

class Maintenance extends Base
{
    /**
     * Get middleware
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return function (Request $request) {
            if ( $request->getMethod() != Request::METHOD_OPTIONS) {
                $startUpControllers = $this->app['startup.controllers'];

                // skip all startUp controllers
                foreach ($startUpControllers as $controller) {
                    if (substr($request->getRequestUri(), 0, strlen($controller) + 1) == '/' . $controller) {
                        return;
                    }
                }

                if ((bool) OW::getConfig()->getValue('base', 'maintenance')) {
                    $message = [
                        'type' => 'maintenance',
                        'shortDescription' => OW::getLanguage()->text('skmobileapp', 'maintenance_mode_error')
                    ];

                    return new Response(json_encode($message), 403, [
                        'Content-Type' => 'application/json'
                    ]);
                }
            }
        };
    }
}
