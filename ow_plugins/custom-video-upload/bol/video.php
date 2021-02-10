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

class CVIDEOUPLOAD_BOL_Video extends OW_Entity
{
    /**
     * @var string
     */
    public $fileName;

    /**
     * @var string
     */
    public $readableFileName;

    /**
     * @var string
     */
    public $fileType;

    /**
     * @var string
     */
    public $status = 'not_confirmed';

    /**
     * @var string
     */
    public $authorization = 'approval';

    /**
     * @var string
     */
    public $privacy = 'everybody';

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var int
     */
    public $timestamp = 0;

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getReadableFileName()
    {
        return $this->readableFileName;
    }

    /**
     * @param string $readableFileName
     */
    public function setReadableFileName($readableFileName)
    {
        $this->readableFileName = $readableFileName;
    }

    /**
     * @return string
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * @param string $fileType
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param string $authorization
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @param string $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return intval($this->userId);
    }

    /**
     * @param int $userId
     */
    public function setUserId( $userId )
    {
        $this->userId = intval($userId);
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return intval($this->timestamp);
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp( $timestamp = null )
    {
        if ( empty($timestamp) )
        {
            $timestamp = time();
        }

        $this->timestamp = intval($timestamp);
    }
}
