<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CUSTOMINDEX_CMP_Camera extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $service = CUSTOMINDEX_BOL_Service::getInstance();

        $banners = $service->findAllBanners();
        
        $this->assign('banners', $banners);
        $this->assign('banners_count', count($banners));

        $plugin = OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY);

        $this->assign('url', $plugin->getUserFilesUrl());

        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'camera.css');
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'main.css');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'camera.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');
    }
}
