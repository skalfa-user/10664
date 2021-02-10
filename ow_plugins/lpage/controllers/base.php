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

class LPAGE_CTRL_Base extends OW_ActionController
{

    public function index()
    {
        $viewRenderer = OW_ViewRenderer::getInstance();

        $viewRenderer->assignVar("cssUrl", OW::getPluginManager()->getPlugin("lpage")->getStaticCssUrl() . "style.css");

        $data = json_decode(OW::getConfig()->getValue("lpage", "settings"), true);

        if ( !empty($data["logoFile"]) )
        {
            $viewRenderer->assignVar("logoFileUrl", OW::getPluginManager()->getPlugin("lpage")->getUserFilesUrl() . $data["logoFile"]);
        }

        if ( !empty($data["bgFile"]) )
        {
            $viewRenderer->assignVar("bgFileUrl", OW::getPluginManager()->getPlugin("lpage")->getUserFilesUrl() . $data["bgFile"]);
        }

        $viewRenderer->assignVar("data", $data);

        if ( OW::getPluginManager()->isPluginActive("skadateios") )
        {
            $viewRenderer->assignVar("iosUrl", OW::getConfig()->getValue("skadateios", "app_url"));
        }

        if ( OW::getPluginManager()->isPluginActive("skandroid") )
        {
            $viewRenderer->assignVar("androidUrl", OW::getConfig()->getValue("skandroid", "app_url"));
        }

        //$this->addMetaInfo($viewRenderer);

        exit($viewRenderer->renderTemplate(OW::getPluginManager()->getPlugin("lpage")->getCtrlViewDir() . "base_index.html"));
    }

    /**
     * @param OW_ViewRenderer $viewRenderer
     */
    public function addMetaInfo( OW_ViewRenderer $viewRenderer )
    {
        $document = OW::getDocument();
        $headData = null;

        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "index",
            "title" => "base+meta_title_index",
            "description" => "base+meta_desc_index",
            "keywords" => "base+meta_keywords_index"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        if ( $document->getDescription() )
        {
            $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => "description", "content" => $document->getDescription())) . PHP_EOL;
        }

        if ( $document->getKeywords() )
        {
            $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => "keywords", "content" => $document->getKeywords())) . PHP_EOL;
        }

        if ( $document->getTitle() )
        {
            $viewRenderer->assignVar('title', $document->getTitle());
        }


        if( BOL_SeoService::getInstance()->isMetaDisabledForEntity($params["sectionKey"], $params["entityKey"]) )
        {
            $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => "robots", "content" => "noindex")) . PHP_EOL;
        }

        $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => "viewport", "content" =>"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0")) . PHP_EOL;

        $headData .= UTIL_HtmlTag::generateTag('meta', array("http-equiv" => OW_HtmlDocument::META_CONTENT_TYPE, "content" => $document->getMime() . '; charset=' . $document->getCharset())) . PHP_EOL;
        $headData .= UTIL_HtmlTag::generateTag('meta', array("http-equiv" => OW_HtmlDocument::META_CONTENT_LANGUAGE, "content" => $document->getLanguage())) . PHP_EOL;

        $ogMetaInfo = $document->getMeta();

        // adds og meta tags
        foreach( $ogMetaInfo['name'] as $metaKey => $metaValue )
        {
            if( empty($metaValue) ) continue;

            $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => $metaKey, "content" => $metaValue)) . PHP_EOL;
        }

        $viewRenderer->assignVar('headData', $headData);

    }

}
