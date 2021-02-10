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

class CUSTOMINDEX_CMP_UsersCounter extends OW_Component
{
    /**
     * Counter refresh time
     */
    const COUNTER_REFRESH_TIME = 5000;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * On before render
     */
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        // add css
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
                getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getStaticCssUrl() . 'counter.css');

        // on load scripts
        OW::getDocument()->addOnloadScript('
            OW.getPing().addCommand("' . CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '_users_count", {
                after: function(data) {
                    // update users count
                    $("#users-counter").text(data.count);
                }
            }).start(' . self::COUNTER_REFRESH_TIME . ');
        ');

        // init view vars
        $this->assign('usersCount', CUSTOMINDEX_BOL_Service::getInstance()->getUsersCount());
    }
}
