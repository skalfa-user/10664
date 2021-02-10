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
class HINT_CLASS_UserCreditsBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_UserCreditsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_UserCreditsBridge
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

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive('usercredits');
    }

    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $uniqId = uniqid("hint-uc-");

        $creditsService = USERCREDITS_BOL_CreditsService::getInstance();
        $balance = $creditsService->getCreditsBalance($userId);

        $fbParams = array($userId, false);
        
        $fbSettings = array(
            "width" => 300,
            "title" => null
        );
        
        $showButton = false;
        
        $js = UTIL_JsGenerator::newInstance();
        
        if ( OW::getUser()->isAuthorized('usercredits') ) // moderator can edit credits balance
        {
            $fbSettings["title"] = OW::getLanguage()->text('usercredits', 'profile_toolbar_item_credits', array('credits' => $balance));
            
            $js->jQueryEvent("#" . $uniqId, "click",
                'var self = $(this); OW.ajaxFloatBox("USERCREDITS_CMP_SetCredits", e.data.params , $.extend({}, e.data.settings, {scope: { btn: $(this), callBack: function(r) {
                    if (r.text) self.text(r.text);
                 }}}));',
            array('e'), array(
                "params" => $fbParams,
                "settings" => $fbSettings
            ));
            
            $showButton = true;
        }
        else // all the others can grant some amount of credits, if available
        {
            if ( $userId == OW::getUser()->getId() )
            {
                return;
            }

            $grantorBalance = $creditsService->getCreditsBalance(OW::getUser()->getId());

            if ( $grantorBalance )
            {
                $fbSettings["title"] = OW::getLanguage()->text('usercredits', 'profile_toolbar_grant');
                $fbSettings["width"] = 400;
                
                $js->jQueryEvent("#" . $uniqId, "click",
                    'OW.ajaxFloatBox("USERCREDITS_CMP_GrantCredits", e.data.params , e.data.settings);',
                array('e'), array(
                    "params" => $fbParams,
                    "settings" => $fbSettings
                ));
                
                $showButton = true;
            }
        }

        if ( $showButton )
        {
            OW::getDocument()->addOnloadScript($js);
            
            $button = array(
                "key" => "usercredits",
                "label" => $fbSettings["title"],
                "attrs" => array("id" => $uniqId)
            );

            $event->add($button);
        }
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text('usercredits', 'profile_toolbar_grant');

        $button = array(
            "key" => "usercredits",
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

        $label = OW::getLanguage()->text('usercredits', 'profile_toolbar_grant');
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "usercredits");

        $button = array(
            "key" => "usercredits",
            "active" => $active === null ? false : $active,
            "label" => $label
        );

        $event->add($button);
    }

    public function onHintRender( OW_Event $event )
    {
        
    }

    public function onQuery( OW_Event $event )
    {
        
    }

    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}