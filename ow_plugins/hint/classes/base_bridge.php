<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.classes
 */
class HINT_CLASS_BaseBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_BaseBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_BaseBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {

    }

    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];
        $uniqId = uniqid("hint-af-");
        $label = OW::getLanguage()->text("hint", "button_view_profile_label");
        $url = BOL_UserService::getInstance()->getUserUrl($userId);

        $attrs = array(
            "id" => $uniqId,
            "href" => $url
        );

        $openInNewWindow = HINT_BOL_Service::getInstance()->getActionOption(
            HINT_BOL_Service::ENTITY_TYPE_USER,
            "view",
            "newWindow"
        );

        if ($openInNewWindow) {
            $attrs["target"] = "_blank";
        }

        $button = array(
            "key" => "view",
            "label" => $label,
            "attrs" => $attrs
        );

        $event->add($button);
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("hint", "button_view_profile_label");

        $button = array(
            "key" => "view",
            "label" => $label,
            "attrs" => array("href" => "javascript://")
        );

        $event->add($button);
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("hint", "button_view_profile_config");

        $service = HINT_BOL_Service::getInstance();
        $active = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "view");

        $button = array(
            "key" => "view",
            "active" => $active === null ? false : $active,
            "label" => $label,
            "options" => array(
                array(
                    "key" => "newWindow",
                    "active" => $service->getActionOption(HINT_BOL_Service::ENTITY_TYPE_USER, "view", "newWindow"),
                    "label" => OW::getLanguage()->text("hint", "button_view_profile_option_new_window")
                )
            )
        );

        $event->add($button);
    }

    public function onHintRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
    }

    public function onQuery( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params["command"], array()) )
        {
            return;
        }

        $userId = $params["params"]['userId'];

        $info = null;
        $error = null;


        $event->setData(array(
            "info" => $info,
            "error" => $error
        ));
    }

    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $event->add(array(
            "key" => "base-gender-age",
            "label" => $language->text("hint", "info-gender-age-label")
        ));

        $event->add(array(
            "key" => "base-activity",
            "label" => $language->text("hint", "info-activity-label")
        ));

        if ( $params["line"] != HINT_BOL_Service::INFO_LINE0 )
        {
            $event->add(array(
                "key" => "base-about",
                "label" => $language->text("hint", "info-about-label")
            ));
        }

        $event->add(array(
            "key" => "base-question",
            "label" => $language->text("hint", "info-question-label")
        ));
    }

    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        switch ( $params["key"] )
        {
            case "base-gender-age":
                $event->setData($language->text("hint", "info-gender-age-preview"));
                break;

            case "base-activity":
                $event->setData($language->text("hint", "info-activity-preview"));
                break;

            case "base-about":
                $event->setData('<span class="ow_remark">' . $language->text("hint", "info-about-preview") . '</span>');
                break;

            case "base-question":
                if ( !empty($params["question"]) )
                {
                    $questionLabel = BOL_QuestionService::getInstance()->getQuestionLang($params["question"]);

                    if ( $params["line"] == HINT_BOL_Service::INFO_LINE2 )
                    {
                        $questionLabel = '<span class="ow_remark">' . $questionLabel . '</span>';
                    }

                    $event->setData($questionLabel);
                }
                break;
        }
    }

    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $entityType = $params["entityType"];
        $entityId = $params["entityId"];

        switch ( $params["key"] )
        {
            case "base-gender-age":
                $questionData = BOL_QuestionService::getInstance()->getQuestionData(array($entityId), array("birthdate"));

                $ageStr = "";
                if ( !empty($questionData[$entityId]['birthdate']) )
                {
                    $date = UTIL_DateTime::parseDate($questionData[$entityId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                    $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                    $ageStr = $age . " " . OW::getLanguage()->text('base', 'questions_age_year_old');
                }

                $sex = $this->renderQuestion($entityId, "sex");
                $event->setData($sex . " " . $ageStr );
                break;

            case "base-about":
                $settings = BOL_ComponentEntityService::getInstance()->findSettingList("profile-BASE_CMP_AboutMeWidget", $entityId, array(
                    'content'
                ));

                $content = empty($settings['content']) ? null : UTIL_String::truncate($settings['content'], 100, '...');

                $event->setData('<span class="ow_remark ow_small">' . $content . '</span>');
                break;

            case "base-activity":
                // Check privacy permissions
                $eventParams = array(
                    'action' => 'base_view_my_presence_on_site',
                    'ownerId' => $entityId,
                    'viewerId' => OW::getUser()->getId()
                );
                try
                {
                    OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $e )
                {
                    break;
                }

                $isOnline = BOL_UserService::getInstance()->findOnlineUserById($entityId);

                if ( $isOnline )
                {
                    $event->setData(OW::getLanguage()->text("base", "activity_online"));
                }
                else
                {
                    $user = BOL_UserService::getInstance()->findUserById($entityId);
                    $activity = UTIL_DateTime::formatDate($user->activityStamp);

                    $event->setData(OW::getLanguage()->text("hint", "info-activity", array(
                        "activity" => $activity
                    )));
                }

                break;

            case "base-question":
                if ( !empty($params["question"]) )
                {
                    $renderedQuestion = $this->renderQuestion($entityId, $params["question"]);

                    if ( $params["line"] == HINT_BOL_Service::INFO_LINE2 )
                    {
                        $renderedQuestion = '<span class="ow_remark">' . $renderedQuestion . '</span>';
                    }

                    $event->setData($renderedQuestion);
                }
                break;
        }
    }

    private function renderQuestion( $userId, $questionName )
    {
        $data = BOL_UserService::getInstance()->getUserViewQuestions($userId, OW::getUser()->isAdmin(), array($questionName));
        $out = "";

        if ( !empty($data['data'][$userId][$questionName]) )
        {
            $out = $data['data'][$userId][$questionName];

            if ( is_array($out) )
            {
                $out = $questionName == "googlemap_location" // googlemap_location shortcut
                        ? $out["address"]
                        : implode(', ', $out);
            }
        }

        return $out;
    }

    public function init()
    {
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}