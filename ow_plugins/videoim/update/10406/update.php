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

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'videoim');

// delete unused langs
Updater::getLanguageService()->deleteLangKey('videoim', 'webrtc_plugin_website_requires_plugin');
Updater::getLanguageService()->deleteLangKey('videoim', 'webrtc_plugin_does_not_support_webrtc');
Updater::getLanguageService()->deleteLangKey('videoim', 'webrtc_plugin_install_now');
Updater::getLanguageService()->deleteLangKey('videoim', 'webrtc_plugin_please_refresh_page');
Updater::getLanguageService()->deleteLangKey('videoim', 'webrtc_plugin_refresh_page');
Updater::getLanguageService()->deleteLangKey('videoim', 'webrtc_plugin_cancel');