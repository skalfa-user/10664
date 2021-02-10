<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Podiachev Evgenii <joker.OW2@gmail.com>
 * @package ow.ow_plugins.bookmarks.classes
 * @since 1.7.5
 */

class BOOKMARKS_MCLASS_EventHandler
{
    private static $classInstance;

    /**
     * 
     * @return BOOKMARKS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private function __construct()
    {

    }
    
    public function onActionToolbarAddUserBookmarkTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() || $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }
        
        $uniqId = uniqid('bookmarks-');
        $languages = OW::getLanguage();
        $markLabel = $languages->text('bookmarks', 'mark_toolbar_label');
        $unMarkLabel = $languages->text('bookmarks', 'unmark_toolbar_label');
        
        $isMarked = BOOKMARKS_BOL_Service::getInstance()->isMarked(OW::getUser()->getId(), $params['userId']);
        
        $label = $isMarked ? $unMarkLabel : $markLabel;
        
        $event->add(array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "bookmark.action",
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $label,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 1,
            'group' => 'addition',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => array("data-command" => $isMarked),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://'
        ));
        
        OW::getDocument()->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.bookmarksActionUrl = {$url};',
                array(
                    'url' => OW::getRouter()->urlForRoute('bookmarks.mark_state')
                )
            )
        );
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('bookmarks')->getStaticJsUrl() . 'bookmarks.js');
        
        OW::getDocument()->addOnloadScript(
            UTIL_JsGenerator::composeJsString(';var bookmarkBtn = $("#" + {$id}).on("click", function(event)
                {
                    BOOKMARKS.markState({$userId}, function( data, textStatus, jqXHR )
                    {
                        if ( data.mark === true )
                        {
                            OWM.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$markedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');
                            bookmarkBtn.find("span").text({$unMarkLabel});
                        }
                        else
                        {
                            OWM.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$unMarkedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');
                            bookmarkBtn.find("span").text({$markLabel});
                        }
                    });
                });',
                array(
                    'id' => $uniqId,
                    'userId' => $params['userId'],
                    'markedNotifyMessage' => $languages->text('bookmarks', 'marked_notify_message', array('bookmarksListURL' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)))),
                    'unMarkedNotifyMessage' => $languages->text('bookmarks', 'unmarked_notify_message', array('bookmarksListURL' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)))),
                    'unMarkLabel' => $unMarkLabel,
                    'markLabel' => $markLabel
                )
            )
        );
    }
    
    public function onBeforeDecoratorRender( BASE_CLASS_PropertyEvent $event )
    {
        $prop = $event->getProperties();

        if ( OW::getUser()->isAuthenticated() && !empty($prop['name']) && $prop['name'] == 'avatar_item' && !empty($prop['data']['userId']) )
        {
            $isMarked = BOOKMARKS_BOL_Service::getInstance()->isMarked(OW::getUser()->getId(), $prop['data']['userId']);
            
            if ( $isMarked )
            {
                $event->setProperty('isMarked', $isMarked);
            }
        }
    }
    
    public function init() {
        BOOKMARKS_CLASS_EventHandler::getInstance()->genericInit();
        
        OW::getEventManager()->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBookmarkTool'));
        OW::getEventManager()->bind('base.before_decorator', array($this, 'onBeforeDecoratorRender'));        
    }
}
