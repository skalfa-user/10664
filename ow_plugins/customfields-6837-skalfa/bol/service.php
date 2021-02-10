<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class CUSTOMFIELDS_BOL_Service
{
    const PLUGIN_KEY = 'customfields';

    /**
     * @var OW_Plugin
     */
    static private $plugin;

    /**
     * Get plugin instance
     *
     * @return OW_Plugin
     */
    static public function getPlugin()
    {
        if (!self::$plugin) {
            self::$plugin = OW::getPluginManager()->getPlugin(self::PLUGIN_KEY);
        }

        return self::$plugin;
    }
}