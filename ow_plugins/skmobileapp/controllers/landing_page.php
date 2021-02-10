<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Sergey Filipovich <psiloscop@gmail.com>
 * @package ow_plugins.skmobileapp.controllers
 * @since 1.8.4
 */
class SKMOBILEAPP_CTRL_LandingPage extends LPAGE_CTRL_Base
{
    public function index()
    {
        $config = OW::getConfig();
        $pluginManager = OW::getPluginManager();
        $viewRenderer = OW_ViewRenderer::getInstance();

        $viewRenderer->assignVar('cssUrl', $pluginManager->getPlugin('lpage')->getStaticCssUrl() . 'style.css');

        $data = json_decode($config->getValue('lpage', 'settings'), true);

        if ( !empty($data['logoFile']) )
        {
            $viewRenderer->assignVar('logoFileUrl', $pluginManager->getPlugin('lpage')->getUserFilesUrl() . $data['logoFile']);
        }

        if ( !empty($data['bgFile']) )
        {
            $viewRenderer->assignVar('bgFileUrl', $pluginManager->getPlugin('lpage')->getUserFilesUrl() . $data['bgFile']);
        }

        $viewRenderer->assignVar('data', $data);
        $viewRenderer->assignVar('iosUrl', $config->getValue('skmobileapp', 'ios_app_url'));
        $viewRenderer->assignVar('androidUrl', $config->getValue('skmobileapp', 'android_app_url'));

        $this->addMetaInfo($viewRenderer);

        exit($viewRenderer->renderTemplate($pluginManager->getPlugin('lpage')->getCtrlViewDir() . 'base_index.html'));
    }
}
