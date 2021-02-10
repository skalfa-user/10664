<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class VIDEOIM_CMP_VideoCallWidget extends BASE_CLASS_Widget
{
    /**
     * User id
     *
     * @var integer
     */
    protected $userId;

    /**
     * User
     *
     * @var BOL_User
     */
    protected $user;

    /**
     * Class constructor
     *
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->userId = !empty($params->additionalParamList['entityId'])
            ? (int) $params->additionalParamList['entityId']
            : -1;

        list($isRequestSendAllowed, $errorMessage) =
                VIDEOIM_BOL_VideoImService::getInstance()->isAllowedSendVideoImRequest($this->userId, true);

        if ( $this->userId == OW::getUser()->getId() || !$isRequestSendAllowed )
        {
            $this->setVisible(false);
        }
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // init view variables
        $this->assign('userId', $this->userId);
    }

    /**
     * Get standard setting values list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('videoim', 'videoim'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => self::ICON_CHAT,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    /**
     * Get widget access
     *
     * @return string
     */
    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}