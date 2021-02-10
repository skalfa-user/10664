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

class CVIDEOUPLOAD_BOL_Preview
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $fileName = null;

    /**
     * @var string
     */
    public $readableFileName = null;

    /**
     * @var string
     */
    public $status = null;

    /**
     * @var string
     */
    public $statusLabel = null;

    /**
     * @var string
     */
    public $coverImage = null;

    /**
     * @var string
     */
    public $privacy;
    /**
     * @var array
     */
    public $videos = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

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
    public function getCoverImage()
    {
        return $this->coverImage;
    }

    /**
     * @param string $coverImage
     */
    public function setCoverImage($coverImage)
    {
        $this->coverImage = $coverImage;
    }

    /**
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * @param array $videos
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->statusLabel;
    }

    /**
     * @param string $statusLabel
     */
    public function setStatusLabel($statusLabel)
    {
        $this->statusLabel = $statusLabel;
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
}
