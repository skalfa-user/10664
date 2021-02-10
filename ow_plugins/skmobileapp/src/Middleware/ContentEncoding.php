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

class ContentEncoding extends Base
{
    /**
     * Call before request
     *
     * @return boolean
     */
    public function callBefore()
    {
        return false;
    }

    /**
     * Get middleware
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return function (Request $request, Response $response) {
            if ($request->getMethod() != Request::METHOD_OPTIONS && $request->headers->get('Accept') != 'text/event-stream') {
                // Supported compression encodings
                $supported = array(
                    'x-gzip' => 'gz',
                    'gzip' => 'gz',
                    'deflate' => 'deflate'
                );

                $accepted = $request->headers->get('accept-encoding');

                if (is_string($accepted)) {
                    // Available encodings to use
                    $encodings = array_intersect(
                        preg_split('/,\s+/', $accepted), array_keys($supported)
                    );

                    foreach ($encodings as $encoding) {
                        if (($supported[$encoding] == 'gz') || ($supported[$encoding] == 'deflate')) {
                            // Verify that the server supports gzip compression before we attempt to gzip encode the data
                            if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
                                continue;
                            }

                            // Attempt to gzip encode the data with an optimal level 4.
                            $encodedContent = gzencode($response->getContent(), 4, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

                            // If there was a problem encoding the data just try the next encoding scheme
                            if ($encodedContent === false) {
                                continue;
                            }

                            // Replace the content with the encoded one
                            $response->setContent($encodedContent);

                            // Set the encoding headers
                            $response->headers->add(['Content-Encoding' => $encoding]);

                            break;
                        }
                    }
                }
            }
        };
    }
}
