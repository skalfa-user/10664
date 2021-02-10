<?php
/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.components
 * @since 1.0
 */

class GOOGLELOCATION_CMP_MapItem extends OW_Component
{
    protected $avatar = array();
    protected $content = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function setAvatar( $avatar )
    {
        $this->avatar = $avatar;
    }

    public function setContent( $content )
    {
        $this->content = $content;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('avatar', $this->avatar);
        $this->assign('content', $this->content);
    }
}