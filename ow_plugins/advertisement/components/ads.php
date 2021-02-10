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
 * User console component class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class ADS_CMP_Ads extends OW_Component
{

    /**
     * @return Constructor.
     */
    public function __construct( $params )
    {
        parent::__construct();
        
        $adsService = ADS_BOL_Service::getInstance();

        $rhandlerAttrs = OW::getRequestHandler()->getHandlerAttributes();

        $pluginKey = OW::getAutoloader()->getPluginKey($rhandlerAttrs['controller']);
     
        if ( empty($params['position']) || OW::getUser()->isAuthorized('ads', 'hide_ads') )
        {
            $this->setVisible(false);
            return;
        }

        $position = trim($params['position']);

        if ( !in_array($position, array(ADS_BOL_Service::BANNER_POSITION_TOP, ADS_BOL_Service::BANNER_POSITION_SIDEBAR, ADS_BOL_Service::BANNER_POSITION_BOTTOM)) )
        {
            $this->setVisible(false);
            return;
        }

        $location = BOL_GeolocationService::getInstance()->ipToCountryCode3(OW::getRequest()->getRemoteAddress());
        $banners = ADS_BOL_Service::getInstance()->findPlaceBannerList($pluginKey, $params['position'], $location);
        
        if ( empty($banners) )
        {
            $this->setVisible(false);
            return;
        }

        $banner = $banners[array_rand($banners)];

        $event = new OW_Event('ads_get_banner_code', array('pluginKey' => $pluginKey, 'position' => $params['position'], 'location' => $location));
        $result = OW::getEventManager()->trigger($event);


        $data = $result->getData();

        $this->assign('code', ( empty($data) ? $banner->getCode() : $data));
        $this->assign('position', $params['position']);
    }
}