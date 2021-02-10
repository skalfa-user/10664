<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakytow <kainisoft@gmail.com>
 * @package ow_plugins.badwords.classes
 * @since 1.1
 */
class BADWORDS_CLASS_HtmlDocument
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $service;
    private $words;
    private $replacement;

    private function __construct()
    {
        $this->service = BADWORDS_BOL_Service::getInstance();
        $this->words = $this->service->findAllBadwords();
        $this->replacement = '<span style="color:' . OW::getConfig()->getValue('badwords', 'censorColor') .'">' . OW::getConfig()->getValue('badwords', 'censorText') . '</span>';
    }
    
    public function cleareContent()
    {
        $document = OW::getDocument();
        
        $title = $document->getTitle();
        $heading = $document->getHeading();
        $body = $document->getBody();
        
        if ( !empty($this->words['php_pattern']) )
        {
            if ( !empty($title) && preg_match($this->words['php_pattern'], $title) === 1 )
            {
                $title = preg_replace($this->words['php_pattern'], OW::getConfig()->getValue('badwords', 'censorText'), $title);
                $document->setTitle($title);
            }
            
            if ( !empty($heading) && preg_match($this->words['php_pattern'], $heading) === 1 )
            {
                $heading = preg_replace($this->words['php_pattern'], $this->replacement, $heading);
                $document->setHeading($heading);
            }
            
            if ( !empty($body) && preg_match($this->words['php_pattern'], $body) === 1 )
            {
                $body = preg_replace($this->words['php_pattern'], $this->replacement, $body);
                $document->setBody($body);
            }
            
            $document->addScriptDeclaration(';OW.bind("base.comments_list_init",function(){$(".ow_comments_content",this.$context).each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ',\'' . $this->replacement . '\'));});});');
            $document->addScriptDeclaration(';OW.bind("base.comments_list_init",function(){$(this.$context).closest(".ow_newsfeed_body").find(".ow_newsfeed_content").each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'));});});');
            $document->addScriptDeclaration(';OW.bind("photo.photo_show",function()
                {
                    var content=$(".ow_photoview_description span:visible");
                    
                    if (content.length)
                    content.html(content.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))
                });
                
                OW.bind("photo.onRenderPhotoItem",function()
                {
                    this.html(this.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))
                });');
            $document->addScriptDeclaration( ';OW.bind("photo.onBeforeLoadFromCache",function(items)
            {
                OW.bind("photo.photo_show",function()
                {
                    var content=$(".ow_photoview_description span:visible");
                    
                    if (content.length)
                    content.html(content.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))
                });
                
                OW.bind("photo.onRenderPhotoItem",function()
                {
                    this.html(this.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))
                });
                
                OW.bind("base.comments_list_init",function(){$(".ow_comments_content",this.$context).each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ',\'' . $this->replacement . '\'));});});
                OW.bind("base.comments_list_init",function(){$(this.$context).closest(".ow_newsfeed_body").find(".ow_newsfeed_content").each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'));});});
            });
            
            OW.bind("photo.onFloatboxClose",function(items)
            {
                OW.bind("photo.photo_show",function()
                {
                    var content=$(".ow_photoview_description span:visible");
                    
                    if (content.length)
                    content.html(content.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))
                });
                
                OW.bind("photo.onRenderPhotoItem",function()
                {
                    this.html(this.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))
                });
                
                OW.bind("base.comments_list_init",function(){$(".ow_comments_content",this.$context).each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ',\'' . $this->replacement . '\'));});});
                OW.bind("base.comments_list_init",function(){$(this.$context).closest(".ow_newsfeed_body").find(".ow_newsfeed_content").each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'));});});
            });');
            $document->addScriptDeclaration(';OW.bind("consoleAddItem",function(items){for(var item in items){items[item].html=items[item].html.replace(' . $this->words['js_pattern'] . ',\'' . $this->replacement . '\');}});');
            
            $pluginManager = OW::getPluginManager();

            if ( $pluginManager->isPluginActive('questions') || $pluginManager->isPluginActive('equestions') )
            {
                $document->addScriptDeclaration(';badwords.getInstance().bindQuestion();');
            }
        }
    }
    
    public function cleareMobilenotificationsItem( OW_Event $event )
    {
        $params = $event->getParams();
    
        if ( !empty($params['data']['content']) && preg_match($this->words['php_pattern'], $params['data']['content']) === 1 )
        {
            $params['data']['content'] = preg_replace($this->words['php_pattern'], $this->replacement, $params['data']['content']);
        }
        
        if ( !empty($params['data']['string']) && !empty($params['data']['string']['vars']) && !empty($params['data']['string']['vars']['status']) && preg_match($this->words['php_pattern'], $params['data']['string']['vars']['status']) === 1 )
        {
            $params['data']['string']['vars']['status'] = preg_replace($this->words['php_pattern'], $this->replacement, $params['data']['string']['vars']['status']);
        }
        
        $event->setData($params['data']);
    }

    public function bindOnItemRender( OW_Event $event )
    {
        if ( !empty($this->words['js_pattern']) )
        {
            OW::getDocument()->addOnloadScript(
                ';var content=$(".qa-text");if(content.length!==0){content.each(function(){$(this).html($(this).html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'));});var title=$(".ow_newsfeed_string:first");title.html(title.html().replace(' . $this->words['js_pattern'] . ', \'' . $this->replacement . '\'))};'
            );
        }
    }
}
