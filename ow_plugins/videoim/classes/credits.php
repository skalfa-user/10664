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
 * Video IM credits handler
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugins.videoim.classes
 * @since 1.8.1
 */
class VIDEOIM_CLASS_Credits
{
    /**
     * Actions
     *
     * @var array
     */
    private $actions;

    /**
     * Auth actions
     *
     * @var array
     */
    private $authActions = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        // register credits actions
        $this->actions[] = array('pluginKey' => 'videoim', 'action' => 'video_im_call', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'videoim', 'action' => 'video_im_receive', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'videoim', 'action' => 'video_im_timed_call', 'amount' => 0);

        $this->authActions['video_im_call'] = 'video_im_call';
        $this->authActions['video_im_receive'] = 'video_im_receive';
    }

    /**
     * Bind credit action collect
     *
     * @param BASE_CLASS_EventCollector $e
     * @return void
     */
    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $e )
    {
        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }
    }

    /**
     * Trigger credit actions
     *
     * @return void
     */
    public function triggerCreditActionsAdd()
    {
        $e = new BASE_CLASS_EventCollector('usercredits.action_add');

        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }

        OW::getEventManager()->trigger($e);
    }
}