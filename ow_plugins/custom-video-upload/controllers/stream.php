<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CVIDEOUPLOAD_CTRL_Stream extends OW_ActionController
{
    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

//        if ( !OW::getUser()->isAuthenticated() )
//        {
//            throw new Redirect404Exception;
//        }

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
    }

    /**
     * Index
     */
    public function index($params)
    {
        $name = isset($params['name']) ? $params['name'] : '';
        $extension = isset($params['extension']) ? $params['extension'] : '';

        if ( $name && in_array($extension, CVIDEOUPLOAD_BOL_Service::CONVERT_MIME_TYPES) )
        {
            $video = $this->service->findVideoByName($name, true);

            if ( !empty($video) )
            {
                /* @var CVIDEOUPLOAD_BOL_Video $video */

//                if ( OW::getUser()->getId() != $video->getUserId() && $video->getAuthorization() != 'approved' )
//                {
//                    throw new Redirect404Exception;
//                }

                $stream = new CVIDEOUPLOAD_CLASS_Stream($this->service->getPublicVideosPath() . $video->fileName . $extension);

                $stream->start();

                exit;
            }
        }

        throw new Redirect404Exception;
    }
}
