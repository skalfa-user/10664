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
class CVIDEOUPLOAD_CMP_ViewVideo extends OW_Component
{
    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName;

    /**
     * Constructor
     *
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        parent::__construct();

        $this->fileName = $fileName;
        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        $video = $this->service->findVideoByName($this->fileName, true);

        $this->assign('video', []);

        if ( !empty($video) )
        {
            $this->assign('video', $this->service->getVideoForPreview($video));
        }
    }
}
