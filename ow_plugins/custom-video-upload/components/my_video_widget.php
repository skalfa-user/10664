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

class CVIDEOUPLOAD_CMP_MyVideoWidget extends BASE_CLASS_Widget
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
        list($isServerReady) = $this->service->isServerReadyForUploading();

        if ( !OW::getUser()->isAuthenticated() || $userId != OW::getUser()->getId() || !$isServerReady )
        {
            $this->setVisible(false);

            return;
        }

        $video = $this->service->findUserVideo($userId, false);

        $this->assign('isVideoLoad', true);

        // check ownership
        if ( !$video || $video->status == CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_CONFIRMED )
        {
            $this->assign('isVideoLoad', false);

            $this->initButtonJs();

            return;
        }

        $this->initButtonJs(true);

        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        // init css
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'widget.css');

        // init view variables
        $this->assign('video', ($video ? $this->service->getVideoForPreview($video) : ''));
        $this->assign('imagesUrl', $plugin->getStaticUrl() . 'images/');
        $this->assign('editUrl', OW::getRouter()->urlForRoute('cvideoupload.video-upload'));
    }

    protected function initButtonJs( $edit = false )
    {
        list($result, $message) = $this->service->isActionAllowed(CVIDEOUPLOAD_BOL_Service::ACTION_UPLOAD_VIDEO);

        if ( $edit )
        {
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(
                    ';$("#btn-video-preview-edit").on("click", function(e)
                        {
                            document.location.href = {$url};
                        });',
                    [
                        'url' => OW::getRouter()->urlForRoute('cvideoupload.video-upload')
                    ]
                )
            );

            return;
        }

        if ( $result === false )
        {
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(
                    ';$("#btn-video-preview-add").on("click", function(e)
                        {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            OW.authorizationLimitedFloatbox({$msg});
                        });',
                    [
                        'msg' => $message
                    ]
                )
            );
        }
        else
        {
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(
                    ';$("#btn-video-preview-add").on("click", function(e)
                        {
                            document.location.href = {$url};
                        });',
                    [
                        'url' => OW::getRouter()->urlForRoute('cvideoupload.video-upload')
                    ]
                )
            );
        }
    }

    /**
     * Get standard settings value list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return [
            self::SETTING_TITLE => OW::getLanguage()->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'my_video_widget_title'),
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
