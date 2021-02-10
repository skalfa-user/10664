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
class VIDEOIM_CLASS_EventHandler extends VIDEOIM_CLASS_AbstractBaseEventHandler
{
    /**
     * Class instance
     *
     * @var VIDEOIM_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Get instance
     *
     * @return VIDEOIM_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->genericInit();

        // force usage of SSL
        OW::getApplication()->addHttpsHandlerAttrs('VIDEOIM_CTRL_VideoIm', 'chatWindow');

        $em = OW::getEventManager();

        // generate a profile action toolbar
        $em->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileActionToolbar'));

        // init videoIm js
        $em->bind(OW_EventManager::ON_FINALIZE, array($this, 'initVideoImRequest'));

        // render preferences
        $em->bind(BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array($this, 'onPreferenceAddFormElement'));
        $em->bind(BOL_PreferenceService::PREFERENCE_SECTION_LABEL_EVENT, array($this, 'onAddPreferenceSectionLabels'));
    }

    /**
     * Init video IM request
     *
     * @return void
     */
    public function initVideoImRequest()
    {
        parent::initVideoImRequestJs(false);
    }

    /**
     * Add preference section labels
     *
     * @param BASE_CLASS_EventCollector $event
     * @return void
     */
    public function onAddPreferenceSectionLabels( BASE_CLASS_EventCollector $event )
    {
        $sectionLabels = array(
            'videoim' => array(
                'label' => OW::getLanguage()->text('videoim', 'preference_section_videoim'),
                'iconClass' => 'ow_ic_script'
            )
        );

        $event->add($sectionLabels);
    }

    /**
     * Add preference form element
     *
     * @param BASE_CLASS_EventCollector $event
     * @return void
     */
    public function onPreferenceAddFormElement( BASE_CLASS_EventCollector $event )
    {
        // check permissions
        $promotionMessage = null;
        $isPromoted = false;
        $isAuthorized = OW::getUser()->isAuthorized('videoim', 'video_im_preferences');

        // check promotion status
        if ( !$isAuthorized )
        {
            $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus('videoim', 'video_im_preferences');
            $isPromoted = !empty($promotedStatus['status'])
                && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

            if ( $isPromoted )
            {
                $promotionMessage = $promotedStatus['msg'];
            }
        }

        // draw a form element
        if ( $isAuthorized || $isPromoted )
        {
            $params = $event->getParams();
            $values = $params['values'];

            $fromElement = new CheckboxField('videoim_decline_calls');
            $fromElement->setLabel(OW::getLanguage()->text('videoim', 'preference_decline_calls_label'));

            if ( $isPromoted )
            {
                $fromElement->addAttribute('disabled', 'disabled');
                $fromElement->setDescription($promotionMessage);
                $fromElement->setValue(0);
            }
            else {
                // init a default value
                if (isset($values['videoim_decline_calls'])) {
                    $fromElement->setValue($values['videoim_decline_calls']);
                }
            }

            $event->add(array($fromElement));
        }
    }
}