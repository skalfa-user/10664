<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Video IM event handler
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugins.videoim.classes
 * @since 1.8.1
 */
abstract class VIDEOIM_CLASS_AbstractBaseEventHandler
{
    /**
     * Ping time
     */
    const PING_TIME = 5000;

    /**
     * Service
     *
     * @var VIDEOIM_BOL_VideoImService
     */
    protected $service;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->service = VIDEOIM_BOL_VideoImService::getInstance();
    }

    /**
     * Generic init
     *
     * @return void
     */
    public function genericInit()
    {
        $em = OW::getEventManager();

        // bind all notifications via ping
        $em->bind('base.ping.videoim_ping', array($this, 'getVideoImPingData'));

        // privacy list
        $em->bind('plugin.privacy.get_action_list', array($this, 'onCollectPrivacyActions'));

        // init auth labels
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));

        // init credits
        $em->bind(OW_EventManager::ON_APPLICATION_INIT, array($this, 'afterInit'));
        $em->bind('usercredits.on_action_collect', array($this, 'bindCreditActionsCollect'));

        // skmobileapp events
        $em->bind('skmobileapp.get_translations', array($this, 'onGetApplicationTranslations'));
        $em->bind('skmobileapp.get_application_config', array($this, 'onGetApplicationConfig'));
        $em->bind('skmobileapp.get_application_permissions', array($this, 'onGetApplicationPermissions'));
        $em->bind('skmobileapp.formatted_users_data', array($this, 'onApplicationFormattedUsersData'));
    }

    /**
     * Bind credit actions collect
     *
     * @param BASE_CLASS_EventCollector $e
     * @return void
     */
    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $e )
    {
        $credits = new VIDEOIM_CLASS_Credits();
        $credits->bindCreditActionsCollect($e);
    }

    /**
     * After init
     *
     * @return void
     */
    public function afterInit()
    {
        // add user credits actions
        if ( !OW::getConfig()->getValue('videoim', 'is_credits_initialized') )
        {
            if ( OW::getConfig()->configExists('videoim', 'is_credits_initialized') )
            {
                OW::getConfig()->saveConfig('videoim', 'is_credits_initialized', 1);
            }
            else
            {
                OW::getConfig()->addConfig('videoim', 'is_credits_initialized', 1);
            }

            $credits = new VIDEOIM_CLASS_Credits();
            $credits->triggerCreditActionsAdd();
        }
    }

    /**
     * Add auth labels
     *
     * @param BASE_CLASS_EventCollector $event
     * @return void
     */
    public function addAuthLabels(BASE_CLASS_EventCollector $event)
    {
        $event->add(
            array(
                'videoim' => array(
                    'label' => OW::getLanguage()->text('videoim', 'auth_group_label'),
                    'actions' => array(
                        'video_im_call' => OW::getLanguage()->text('videoim', 'auth_action_label_video_im_call'),
                        'video_im_receive' => OW::getLanguage()->text('videoim', 'auth_action_label_video_im_receive'),
                        'video_im_preferences' => OW::getLanguage()->text('videoim', 'auth_action_label_video_im_preferences')
                    )
                )
            )
        );
    }

    /**
     * Collect privacy actions
     *
     * @param BASE_CLASS_EventCollector $event
     * @return void
     */
    public function onCollectPrivacyActions( BASE_CLASS_EventCollector $event )
    {
        $action = array(
            'key' => 'videoim_send_call_request',
            'pluginKey' => 'videoim',
            'label' => OW::getLanguage()->text('videoim', 'privacy_action_send_call_request'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    /**
     * Get video Im ping data
     *
     * @param OW_Event $e
     * @return void
     */
    public function getVideoImPingData( OW_Event $e )
    {
        if ( OW::getUser()->getId() )
        {
            $notifications = VIDEOIM_BOL_VideoImService::getInstance()->getNotifications(OW::getUser()->getId());

            if ( $notifications )
            {
                usort( $notifications, function( $left, $right ) {
                    $notification = json_decode($right['notification'], true);

                    // The list must be sorted by offer, answer and bye notifications, but bye notification must first of them.
                    return $notification['type'] == 'bye' ? 1 : in_array($notification['type'], ['offer', 'answer'], true) ? 1 : -1;
                });

                $e->setData($notifications);
            }
        }
    }

    /**
     * Add profile action toolbar
     *
     * @param BASE_CLASS_EventCollector $event
     * @return void
     */
    public function addProfileActionToolbar( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        list($isRequestSendAllowed, $errorMessage) =
            VIDEOIM_BOL_VideoImService::getInstance()->isAllowedSendVideoImRequest($params['userId'], true);

        if ( $isRequestSendAllowed )
        {
            $toolbar = array(
                'group' => 'addition',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => 'videoim' . '.send',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('videoim', 'videoim_make_call_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => array(
                    'onclick' => 'videoImRequest.getChatWindow(' . $params['userId'] . ')'
                )
            );

            $event->add($toolbar);
        }
    }

    /**
     * Init video IM request js
     *
     * @param boolean $isMobileContext
     * @return void
     */
    protected function initVideoImRequestJs($isMobileContext)
    {
        $currentPluginBuild = $this->service->getPluginBuild();

        // include and init js and css files
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
                getPlugin('videoim')->getStaticCssUrl() . 'videoim_request.css?build=' . $currentPluginBuild);

        if ( $isMobileContext )
        {
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->
                    getPlugin('videoim')->getStaticCssUrl() . 'videoim_mobile_request.css?build=' . $currentPluginBuild);
        }

        $className = $isMobileContext ? 'VIDEOIM_MCTRL_VideoIm' : 'VIDEOIM_CTRL_VideoIm';

        // include necessary js and css files
        OW::getDocument()->addScript(OW::getPluginManager()->
                getPlugin('videoim')->getStaticJsUrl() . 'howler.min.js?build=' . $currentPluginBuild);

        OW::getDocument()->addScript(OW::getPluginManager()->
                getPlugin('videoim')->getStaticJsUrl() . 'videoim_request.js?build=' . $currentPluginBuild);

        OW::getDocument()->addScriptDeclaration(UTIL_JsGenerator::composeJsString('
            videoImRequest = new VideoImRequest({
                "mobile_context" : {$isMobileContext},
                "urls" : {
                    "base_sounds_url" : {$baseSoundUrl},
                    "mark_accepted_url" : {$markAcceptedUrl},
                    "chat_link_url" : {$chatLinkUrl},
                    "chat_url" : {$chatUrl},
                    "block_url" : {$blockUrl},
                    "decline_url" : {$declineUrl}
                }
        })', array(
            'baseSoundUrl' => OW::getPluginManager()->getPlugin('videoim')->getStaticUrl() . 'sound',
            'markAcceptedUrl' => OW::getRouter()->urlFor($className, 'ajaxNotificationsMarkAccepted'),
            'chatLinkUrl' => OW::getRouter()->urlFor($className, 'ajaxGetChatLink'),
            'chatUrl' => OW::getRouter()->urlFor($className, 'chatWindow'),
            'blockUrl' => OW::getRouter()->urlFor($className, 'ajaxBlockUser'),
            'declineUrl' => OW::getRouter()->urlFor($className, 'ajaxDeclineRequest'),
            'isMobileContext' => $isMobileContext
        )));

        // a handler for ping
        OW::getDocument()->addOnloadScript('
            OW.getPing().addCommand("videoim_ping", {
                after: function(data) {
                    if ( data != null )
                    {
                        OW.trigger("videoim.notifications", [data]);
                    }
                }
            }).start(' . self::PING_TIME . ');
        ');
    }

    /**
     * Add permissions to skmobileapp plugin
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGetApplicationPermissions( OW_Event $event )
    {
        $permissions = $event->getData();

        $permissions[] = [
            'group' => 'videoim',
            'plugin' => 'videoim',
            'actions' => [
                'video_im_call',
                'video_im_receive',
                'video_im_timed_call'
            ],
            'tracking_actions' => [
            ]
        ];

        $event->setData($permissions);
    }

    /**
     * Add translations to skmobileapp plugin
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGetApplicationTranslations( SKMOBILEAPP_CLASS_LanguageEventCollector $event )
    {
        $languageService = BOL_LanguageService::getInstance();

        $langs = array();
        $prefixId = $languageService->findPrefixId( 'videoim' );
        $languageId = $languageService->getCurrent()->getId();

        foreach ( $languageService->findAllPrefixKeys( $prefixId ) as $prefixKey )
        {
            if ( stristr($prefixKey->key, 'auth') ||
                stristr($prefixKey->key, 'admin') ||
                stristr($prefixKey->key, 'usercredits') )
            {
                continue;
            }

            $value = $languageService->findValue( $languageId, $prefixKey->id );

            if ( $value )
            {
                $langs[$prefixKey->key] = $value->getValue();
            }
        }

        $event->add('vim', $langs);
    }

    /**
     * Add translations to skmobileapp plugin
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGetApplicationConfig( OW_Event $event )
    {
        $data = $event->getData();

        if ( !empty($data) )
        {
            $data['videoim_server_list'] = json_decode(
                OW::getConfig()->getValue('videoim', 'server_list'), true
            );
            $data['videoim_track_credits_type'] = OW::getConfig()->getValue('videoim', 'track_credits_type');

            $event->setData( $data );
        }
    }

    /**
     * Add call/answer permissions to skmobileapp video im
     *
     * @param OW_Event $event
     * @return void
     */
    public function onApplicationFormattedUsersData( OW_Event $event )
    {
        $userData = $event->getData();
        
        foreach ( $userData as $index => $userItem )
        {
            list($isAllowed, $errorMessage, $errorCode) = array_pad($this->service->isAllowedSendVideoImRequestForApplication($userItem['id']), 3, null);

            $userData[$index]['videoImCallPermission'] = [
                'isPermitted' => $isAllowed,
                'errorMessage' => $errorMessage,
                'errorCode' => $errorCode
            ];
        }

        $event->setData($userData);
    }
}