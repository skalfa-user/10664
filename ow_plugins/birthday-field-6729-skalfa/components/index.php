<?php

/**
 * Class BIRTHDAYF_CMP_Index
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 * @package ow_plugins.pluginkey
 * @since 1.8.4
 */
class BIRTHDAYF_CMP_Index extends HOTLIST_CMP_Index
{
    public function __construct( array $params = array() )
    {
        parent::__construct( $params );

        $this->setTemplate(OW::getPluginManager()->getPlugin(BIRTHDAYF_BOL_Service::PLUGIN_KEY)->getCmpViewDir().'index.html');
    }
}