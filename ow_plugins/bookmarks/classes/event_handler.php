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
 * Bookmarks Event handler
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.classes
 * @since 1.0
 */
class BOOKMARKS_CLASS_EventHandler
{
    const EVENT_NAME = 'bookmarks.is_mark';
    
    private static $classInstance;

    /**
     * 
     * @return BOOKMARKS_CLASS_EventHandler
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
    
    public function addProfileActionToolbar( OW_Event $event )
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
        $label = BOOKMARKS_BOL_Service::getInstance()->isMarked(OW::getUser()->getId(), $params['userId']) ? $unMarkLabel : $markLabel;

        $event->add(array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "bookmark.action",
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $label,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 5
        ));

        OW::getDocument()->addOnloadScript(
            UTIL_JsGenerator::composeJsString(';var bookmarkBtn = $("#" + {$id}).bind("click", function(event)
                {
                    BOOKMARKS.markState({$userId}, function( data, textStatus, jqXHR )
                    {
                        if ( data.mark === true )
                        {
                            OW.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$markedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');
                            bookmarkBtn.html({$unMarkLabel});
                        }
                        else
                        {
                            OW.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$unMarkedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');
                            bookmarkBtn.html({$markLabel});
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
    
    public function onUserLogin( OW_Event $event)
    {
        $params = $event->getParams();
        
        BOOKMARKS_BOL_NotifyLogDao::getInstance()->notifyLogDeleteByUserId($params['userId']);
    }

    public function addQuickLink( BASE_CLASS_EventCollector $event )
    {
        $count = BOOKMARKS_BOL_Service::getInstance()->findBookmarksCount(OW::getUser()->getId());
        
        if ( empty($count) )
        {
            return;
        }
        
        $router = OW_Router::getInstance();

        $event->add(array(
            BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('bookmarks', 'quick_link_index'),
            BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $router->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)),
            BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $count,
            BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $router->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)),
        ));
    }
    
    public function beforeDecorator( BASE_CLASS_PropertyEvent $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        $properties = $event->getProperties();
        
        if ( !empty($properties['name']) && $properties['name'] == 'user_big_list_item' )
        {
            static $settings = NULL;
        
            if ( empty($settings) )
            {
                $settings['bookmarkList'] = BOOKMARKS_BOL_MarkDao::getInstance()->findAllBookmarkIdList(OW::getUser()->getId());
                $handler = OW::getRequestHandler()->getHandlerAttributes();
                $settings['isMarkList'] = $handler[OW_RequestHandler::ATTRS_KEY_CTRL] == 'BOOKMARKS_CTRL_List';
            }

            if ( ($isMarked = in_array($properties['id'], $settings['bookmarkList'])) )
            {
                $label = OW::getLanguage()->text('bookmarks', 'unmark_toolbar_label');
            }
            else
            {
                $label = OW::getLanguage()->text('bookmarks', 'mark_toolbar_label');
            }

            if ( !$settings['isMarkList'] )
            {
                $event->setProperty('isMarked', $isMarked);
            }
            
            $event->setProperty('avatarAction', array(
                'href' => 'javascript://',
                'label' => $label,
                'userId' => $properties['id'])
            );
        }
    }
    
    public function onFinalize( OW_Event $event )
    {
        $document = OW::getDocument();
        $languages = OW::getLanguage();
        
        $document->addStyleSheet(OW::getPluginManager()->getPlugin('bookmarks')->getStaticCssUrl() . 'bookmarks.css');
        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.bookmarksActionUrl = {$url};',
                array(
                    'url' => OW::getRouter()->urlForRoute('bookmarks.mark_state')
                )
            )
        );
        $document->addScript(OW::getPluginManager()->getPlugin('bookmarks')->getStaticJsUrl() . 'bookmarks.js');
        $document->addScriptDeclaration(
            UTIL_JsGenerator::composeJsString(';$(document).on("click", ".ow_ulist_big_avatar_bookmark", function( event )
                {
                    var handler = $(this);

                    BOOKMARKS.markState(handler.attr("data-user-id"), function( data, textStatus, jqXHR )
                    {
                        if ( data.mark === true )
                        {
                            OW.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$markedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');

                            var element;

                            if ( (element = $("#user-avatar-" + handler.attr("data-user-id") + " .ow_usearch_display_name")).length )
                            {
                                $("<div>", {class: "ow_ic_bookmark ow_bookmark_icon_ulist", id: "bookmark-user-" + handler.attr("data-user-id")}).insertAfter(element);
                            }
                            else
                            {
                                $("<div>", {class: "ow_ic_bookmark ow_bookmark_icon_ulist", id: "bookmark-user-" + handler.attr("data-user-id")}).appendTo(element);
                            }

                            handler.html({$unMarkLabel});
                        }
                        else
                        {
                            OW.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$unMarkedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');
                            $("#bookmark-user-" + handler.attr("data-user-id")).remove();
                            handler.html({$markLabel});
                        }
                    });
                });',
                array(
                    'markedNotifyMessage' => $languages->text('bookmarks', 'marked_notify_message', array('bookmarksListURL' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)))),
                    'unMarkedNotifyMessage' => $languages->text('bookmarks', 'unmarked_notify_message', array('bookmarksListURL' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)))),
                    'unMarkLabel' => $languages->text('bookmarks', 'unmark_toolbar_label'),
                    'markLabel' => $languages->text('bookmarks', 'mark_toolbar_label')
                )
            )
        );
    }

    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();
        
        BOOKMARKS_BOL_MarkDao::getInstance()->deleteMarksByUserId($params['userId']);
    }
    
    public function isMark( OW_Event $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        $params = $event->getParams();
        $userId = !empty($params['userId']) ? $params['userId'] : OW::getUser()->getId();
        $data = $event->getData();
        
        static $bookmarkIdList = array();
        
        if ( !array_key_exists($userId, $bookmarkIdList) )
        {
            $bookmarkIdList[$userId] = BOOKMARKS_BOL_MarkDao::getInstance()->findAllBookmarkIdList($userId);
        }
        
        foreach ( array_intersect(array_keys($data), $bookmarkIdList[$userId]) as $id )
        {
            $data[$id]['isMarked'] = TRUE;
        }
        
        $event->setData($data);
    }

    public function getMarksByIdList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        $userIdList = $params["idList"];
        
        $service = BOOKMARKS_BOL_Service::getInstance();
        
        $out = $service->getMarkedListByUserId($userId, $userIdList);
        $event->setData($out);
        
        return $out;
    }
    
    public function markUser( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        $markUserId = $params["markUserId"];
        
        $service = BOOKMARKS_BOL_Service::getInstance();
        $service->mark($userId, $markUserId);
    }
    
    public function unMarkUser( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        $markUserId = $params["markUserId"];
        
        $service = BOOKMARKS_BOL_Service::getInstance();
        $service->unmark($userId, $markUserId);
    }
    
    public function getUserList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $first = empty($params['first']) ? 0 : $params['first'];
        $count = empty($params['count']) ? 1000000 : $params['count'];
        
        $users = BOOKMARKS_BOL_Service::getInstance()->findBookmarksUserIdList($userId, $first, $count);

        $event->setData($users);
        
        return $users;
    }
    
    public function usearchCollectUserActions( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userIdList = $params['userIdList'];
        $viewerId = OW::getUser()->getId();

        $languages = OW::getLanguage();
        $markList = BOOKMARKS_BOL_MarkDao::getInstance()->findAllBookmarkIdList($viewerId);

        $actions = array();
        
        foreach ( $userIdList as $userId )
        {
            if ( $userId == $viewerId )
            {
                continue;
            }

            $key = in_array($userId, $markList) ? 'unmark' : 'mark';
            $id = 'action_' . $key . '_' . $userId;
            $actions[$userId] = array(
                'key' => $key,
                'label' => $languages->text('bookmarks', $key . '_toolbar_label'),
                'id' => $id,
                'href' => 'javascript://',
                'order' => 0,
                'linkClass' => 'ow_ic_bookmark bookmark_action',
                'attributes' => array('data-user-id' => $userId)
            );
        }

        if ( count($actions) )
        {
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(';$(document).on("click", ".bookmark_action", function( event )
                    {
                        var handler = $(this);

                        BOOKMARKS.markState(handler.attr("data-user-id"), function( data, textStatus, jqXHR )
                        {
                            if ( data.mark === true )
                            {
                                OW.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$markedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');

                                var element;

                                if ( (element = $("#user-avatar-" + handler.attr("data-user-id") + " .ow_usearch_display_name")).length )
                                {
                                    $("<div>", {class: "ow_ic_bookmark ow_bookmark_icon_ulist", id: "bookmark-user-" + handler.attr("data-user-id")}).insertAfter(element);
                                }
                                else
                                {
                                    $("<div>", {class: "ow_ic_bookmark ow_bookmark_icon_ulist", id: "bookmark-user-" + handler.attr("data-user-id")}).appendTo(element);
                                }

                                handler.html({$unMarkLabel});
                            }
                            else
                            {
                                OW.info(\'<div class="clearfix bookmarks_wrap"><span>\' + {$unMarkedNotifyMessage} + \'</span><span class="ow_left boomarks_ic_wrap bookmarks_ic_bookmark_white"></span></div>\');
                                $("#bookmark-user-" + handler.attr("data-user-id")).remove();
                                handler.html({$markLabel});
                            }
                        });
                    });',
                    array(
                        'markedNotifyMessage' => $languages->text('bookmarks', 'marked_notify_message', array('bookmarksListURL' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)))),
                        'unMarkedNotifyMessage' => $languages->text('bookmarks', 'unmarked_notify_message', array('bookmarksListURL' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)))),
                        'unMarkLabel' => $languages->text('bookmarks', 'unmark_toolbar_label'),
                        'markLabel' => $languages->text('bookmarks', 'mark_toolbar_label')
                    )
                )
            );
        }

        $event->add($actions);
    }

    public function genericInit()
    {
        $eventManager = OW::getEventManager();
        
        $eventManager->bind(OW_EventManager::ON_USER_LOGIN, array($this, 'onUserLogin'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        $eventManager->bind(self::EVENT_NAME, array($this, 'isMark'));
        
        $eventManager->bind("bookmarks.mark", array($this, 'markUser'));
        $eventManager->bind("bookmarks.unmark", array($this, 'unMarkUser'));
        $eventManager->bind("bookmarks.get_mark_list", array($this, 'getMarksByIdList'));
        $eventManager->bind("bookmarks.get_user_list", array($this, 'getUserList'));
    }
    
    public function init()
    {
        $this->genericInit();
        
        $eventManager = OW::getEventManager();
        
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileActionToolbar'));
        $eventManager->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'addQuickLink'));
        $eventManager->bind('base.before_decorator', array($this, 'beforeDecorator'));
        $eventManager->bind('usearch.collect_user_actions', array($this, 'usearchCollectUserActions'), 0);
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
    }
}
