<?php

class ARTICLES_BOL_Article extends OW_Entity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $subtitle;

    /**
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $image;

    /**
     *
     * @var int
     */
    public $timeStamp;

    /**
     *
     * @var int
     */
    public $featured;

    public function getImageNameUrl()
    {
        return OW::getPluginManager()->getPlugin('articles')->getUserFilesUrl() . $this->image;
    }

    public function getImageNamePath()
    {
        return OW::getPluginManager()->getPlugin('articles')->getUserFilesDir() . $this->image;
    }
}
