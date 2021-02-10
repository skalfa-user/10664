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

class CVIDEOUPLOAD_Cron extends OW_Cron
{
    /**
     * Convert videos limit
     */
    const CONVERT_VIDEOS_LIMIT = 10;

    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
    }

    /**
     * Run command every minute
     *
     * @return void
     */
    public function run()
    {
        // get list of not converted videos

        if ( !$this->service->isServerReadyForUploading() )
        {
            return;
        }

        $convertType = $this->service->getConfigValue('typeOutput');

        if ( empty($convertType) )
        {
            return;
        }

        $videos = $this->service->findVideosForConverting(self::CONVERT_VIDEOS_LIMIT, true);

        if ( !empty($videos) )
        {
            // converting videos
            foreach ($videos as $videoDto)
            {
                /* @var CVIDEOUPLOAD_BOL_Video $videoDto */

                $convertedFiles = [];

                $convertedFile = [];
                // convert video files
                switch ($convertType)
                {
                    // convert to mp4
                    case '.mp4' :

                        $convertedFile = $this->service->convertToMp4Format($videoDto->fileName, $videoDto->fileType, $convertType);
                        $convertedFiles[] = $convertedFile;

                        break;

                    // convert to web
                    case '.webm' :

                        $convertedFile = $this->service->convertTWebmFormat($videoDto->fileName, $videoDto->fileType, $convertType);
                        $convertedFiles[] = $convertedFile;

                        break;

                    default :
                }

                if ( empty($convertedFile) )
                {
                    $videoDto->setStatus(CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_PROCESSED);

                    $this->service->saveVideo($videoDto);

                    continue;
                }

                // check converted files
                $convertedFilesCount = 0;
                foreach ( $convertedFiles as $file )
                {
                    if ( file_exists($this->service->getPrivateVideosPath() . $file) )
                    {
                        $convertedFilesCount++;
                    }
                }

                // move the converted file in public dir
                if ( count($convertedFiles) == $convertedFilesCount )
                {
                    foreach ( $convertedFiles as $file )
                    {
                        CVIDEOUPLOAD_BOL_Service::getStorage()->copyFile($this->service->
                            getPrivateVideosPath() . $file, $this->service->getPublicVideosPath() . $file);

                        // delete converted file
                        unlink($this->service->getPrivateVideosPath() . $file);
                    }

                    // delete initial data
                    $this->service->deleteNotProcessedVideo($videoDto->fileName, false);

                    // mark video as processed
                    $this->service->markVideo($videoDto->id);
                }
                else
                {
                    // mark video as not processed
                    $this->service->markVideo($videoDto->id, false);
                }
            }
        }
    }
}
