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

class CUSTOMINDEX_CMP_QuickSearch extends USEARCH_CMP_QuickSearch
{
    public function render()
    {
        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY);

        $document->addStyleSheet($plugin->getStaticCssUrl() . 'jquery.simpleselect.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'quick_search.css');

        $document->addScript($plugin->getStaticJsUrl() . 'jquery.simpleselect.js');
        $document->addScriptDeclaration('$(".ow_qs select").simpleselect();');

        return parent::render();
    }
}
