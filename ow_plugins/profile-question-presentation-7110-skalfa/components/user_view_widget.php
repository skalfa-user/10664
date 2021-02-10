<?php

class SKPROFILEQP_CMP_UserViewWidget extends BASE_CMP_UserViewWidget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct( $params );

        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin(SKPROFILEQP_BOL_Service::PLUGIN_KEY);

        $document->addStyleSheet( $plugin->getStaticCssUrl() . 'profile-question-presentation.css' );
    }
}
