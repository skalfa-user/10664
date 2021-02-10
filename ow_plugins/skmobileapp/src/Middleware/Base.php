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

abstract class Base implements IMiddleware
{
    /**
     * App
     *
     * @var SilexApplication
     */
    protected $app;

    /**
     * Base constructor.
     *
     * @param SilexApplication $app
     */
    public function __construct(SilexApplication $app)
    {
        $this->app = $app;
    }

    /**
     * Call before request
     *
     * @return boolean
     */
    public function callBefore()
    {
        return true;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return 0;
    }
}
