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

class CVIDEOUPLOAD_CMP_UserVideoWidget extends BASE_CLASS_Widget
{
    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * Constructor
     *
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
        $userId = $params->additionalParamList['entityId'];
        $viewerId = OW::getUser()->getId();

        list($isServerReady) = $this->service->isServerReadyForUploading();

        if ( !OW::getUser()->isAuthenticated() || $userId == OW::getUser()->getId() || !$isServerReady )
        {
            $this->setVisible(false);

            return;
        }

        $video = $this->service->findUserVideo($userId);

        if ( !$video && OW::getUser()->getId() != $userId )
        {
            $this->setVisible(false);

            return;
        }

        $isView = $this->service->isViewVideo($viewerId, $video);

        if ( $isView === false )
        {
            $this->setVisible(false);

            return;
        }

        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        // init css
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'widget.css');

        // init view variables
        $this->assign('video', ($video ? $this->service->getVideoForPreview($video) : ''));
        $this->assign('isOwner', OW::getUser()->getId() == $userId);
        $this->assign('imagesUrl', $plugin->getStaticUrl() . 'images/');
    }

    /**
     * Get standard settings value list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return [
            self::SETTING_TITLE => OW::getLanguage()->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'video_widget_title'),
            self::SETTING_ICON => 'ow_ic_video',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true
        ];
    }

    /**
     * Get access
     *
     * @return string
     */
    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}
