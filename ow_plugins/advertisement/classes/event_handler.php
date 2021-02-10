<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.ads.classes
 * @since 1.6.1
 */
class ADS_CLASS_EventHandler
{
    /**
     * @var ADS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return ADS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function addPageTopBanner( BASE_CLASS_EventCollector $event )
    {
        $cmp = OW::getClassInstance('ADS_CMP_Ads', ['position' => 'top']);
        $event->add($cmp->render());
    }

    public function addPageBottomBanner( BASE_CLASS_EventCollector $event )
    {
        $cmp = OW::getClassInstance('ADS_CMP_Ads', ['position' => 'bottom']);
        $event->add($cmp->render());
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'ads' => array(
                    'label' => $language->text('ads', 'auth_group_label'),
                    'actions' => array(
                        'hide_ads' => $language->text('ads', 'auth_action_label_hide_ads')
                    )
                )
            )
        );
    }


    public function genericInit()
    {
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
    }

    public function init()
    {
        $this->genericInit();

        OW::getEventManager()->bind('base.add_page_top_content', array($this, 'addPageTopBanner'));
        OW::getEventManager()->bind('base.add_page_bottom_content', array($this, 'addPageBottomBanner'));
    }
}