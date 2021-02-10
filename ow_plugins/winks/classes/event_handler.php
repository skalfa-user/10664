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

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks.classes
 * @since 1.0
 */
class WINKS_CLASS_EventHandler
{
    CONST EVENT_DELETE_EXPIRED_WINKS = 'winks.onDeleteExpiredWinks';
    
    private static $classInstance;

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
    
    public function init()
    {
        if ( !OW::getPluginManager()->isPluginActive('mailbox') )
        {
            return;
        }
        
        $eventManager = OW::getEventManager();
        
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileActionToolbar'));
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
        $eventManager->bind('wink.renderWink', array($this, 'onRenderWink'));
        $eventManager->bind('wink.renderWinkBack', array($this, 'onRenderWinkBack'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'baseUserUnregister'));
        $eventManager->bind(OW_EventManager::ON_USER_SUSPEND, array($this, 'baseUserSuspend'));
        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));

        $this->genericInit();

        WINKS_CLASS_ConsoleEventHandler::getInstance()->init();
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('winks.isWinkSent', array($this, 'getIsWinkSent'));
        OW::getEventManager()->bind('winks.winkBack', array($this, 'winkBack'));
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'mailbox',
            'action' => 'wink_email_notification',
            'sectionIcon' => 'ow_ic_mail',
            'sectionLabel' => OW::getLanguage()->text('winks', 'wink_email_notifications_section_label'),
            'description' => OW::getLanguage()->text('winks', 'wink_email_notifications_new_message'),
            'selected' => true
        ));
    }

    public function addProfileActionToolbar( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userId = OW::getUser()->getId();
        
        if ( !OW::getUser()->isAuthenticated() || $params['userId'] == $userId )
        {
            return;
        }

        $uniqId = uniqid('winks-');
        $language = OW::getLanguage();
        $service = WINKS_BOL_Service::getInstance();

        if ( BOL_UserService::getInstance()->isBlocked($userId, $params['userId']) )
        {
            $event->add(array(
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "winks.send",
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $language->text('winks', 'wink_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 3
            ));
            
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(';
                    $(document.getElementById({$id})).on("click", function()
                    {
                        OW.error({$msg});
                    });', array(
                        'id' => $uniqId,
                        'msg' => $language->text('winks', 'not_in_interact_error_msg')
                    )
                )
            );
        }
        elseif ( $service->isLimited($userId, $params['userId']) || $service->isCompleted($userId, $params['userId']) )
        {
            $event->add(array(
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "winks.send",
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $language->text('winks', 'wink_sent_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 3
            ));
            
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(';
                    $(document.getElementById({$id})).on("click", function()
                    {
                        OW.error({$msg});
                    });', array(
                        'id' => $uniqId,
                        'msg' => $language->text('winks', 'wink_double_sent_error')
                    )
                )
            );
        }
        else
        {
            $event->add(array(
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "winks.send",
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $language->text('winks', 'wink_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 3
            ));
            
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(';
                    $(document.getElementById({$id})).on("click", function()
                    {
                        var self = $(this);
                        
                        $.ajax({
                            url: {$url},
                            type: "POST",
                            dataType: "json",
                            cache: false,
                            data: 
                            {
                                funcName: "sendWink",
                                userId: {$userId},
                                partnerId: {$partnerId}
                            },
                            success: function( data )
                            {
                                self.html({$sentLabel});
                                self.off().on("click", function()
                                {
                                    OW.error({$sentErrorMsg});
                                });
                                
                                if ( data && data.result )
                                {
                                    OW.info({$successMsg});
                                }
                                else
                                {
                                    OW.error(data.msg);
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown )
                            {
                                throw textStatus;
                            }
                        });
                    });', array(
                        'id' => $uniqId,
                        'url' => OW::getRouter()->urlForRoute('winks.rsp'),
                        'userId' => $userId,
                        'partnerId' => $params['userId'],
                        'sentLabel' => $language->text('winks', 'wink_sent_label'),
                        'sentErrorMsg' => $language->text('winks', 'wink_double_sent_error'),
                        'successMsg' => $language->text('winks', 'wink_sent_success_msg'),
                        'msg' => $language->text('winks', 'wink_sent_error')
                    )
                )
            );
        }
    }
    
    public function onFinalize( OW_Event $event )
    {
        OW::getDocument()->addScriptDeclaration(
            UTIL_JsGenerator::composeJsString(';
window.winks = (function( $ )
{
    return {
        winkBack: function( data )
        {
            if ( data.userId === undefined || data.partnerId === undefined || data.messageId === undefined )
            {
                return false;
            }

            $.ajax({
                url: {$rsp},
                type: "POST",
                cache: false,
                dataType: "json",
                data:
                {
                    funcName: "winkBack",
                    userId: data.userId,
                    partnerId: data.partnerId,
                    messageId: data.messageId
                }
            });
            
            OW.trigger("winks.onWinkBack", [data.userId, data.partnerId]);
            $(this).remove();
        }
    };
})(jQuery);', array(
                'rsp' => OW::getRouter()->urlForRoute('winks.rsp')
                )
            )
        );
        
        OW::getDocument()->addStyleDeclaration('
.ow_wink_icon {
    background-image: url(' . OW::getPluginManager()->getPlugin('winks')->getStaticUrl() . 'images/ic_wink.png);
    display: inline-block;
    height: 13px;
    width: 13px;
    margin: 0 0 0 6px;
    vertical-align: text-bottom;
}');
    }
    
    public function onRenderWink( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( ($wink = WINKS_BOL_WinksDao::getInstance()->findById($params['winkId'])) === NULL )
        {
            return;
        }

        $winkBack = '<div class="ow_chat_wink">';
        
        if ( $wink->getUserId() == OW::getUser()->getId() )
        {
            $winkBack .= OW::getLanguage()->text('winks', 'accept_wink_msg');
        }
        else
        {
            $winkBack .= '<b>' . BOL_UserService::getInstance()->getDisplayName($wink->getUserId()) . '</b><div class="ow_wink_icon"></div><br />';
            $winkBack .= OW::getLanguage()->text('winks', 'wink_back_message');
        }
        
        if ( $params['winkBackEnabled'] && $wink->getPartnerId() == OW::getUser()->getId() && empty($params['getPreview']))
        {
            $winkBack .= UTIL_JsGenerator::composeJsString(
                '<br /><span class="ow_lbutton ow_green" onclick="winks.winkBack.call(this, {userId: {$userId}, partnerId: {$partnerId}, messageId: {$messageId}});">' . OW::getLanguage()->text('winks', 'wink_back_label') . '</span>',
                array(
                    'userId' => $wink->getUserId(),
                    'partnerId' => $wink->getPartnerId(),
                    'messageId' => $params['messageId']
                )
            );
        }
        
        $winkBack .= '</div>';

        $event->setData($winkBack);
    }

    public function onRenderWinkBack( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['winkId']) || ($wink = WINKS_BOL_Service::getInstance()->findWinkById($params['winkId'])) === NULL )
        {
            $data = '';
        }
        else
        {
            $data = '<div class="ow_chat_wink">';
            
            if ( $wink->getUserId() == OW::getUser()->getId() )
            {
                $data .= '<b>' . BOL_UserService::getInstance()->getDisplayName($wink->getPartnerId()) . '</b><div class="ow_wink_icon"></div><br />';
                $data .= OW::getLanguage()->text('winks', 'wink_back_message_owner');
            }
            else
            {
                $data .= OW::getLanguage()->text('winks', 'winked_back_msg');
            }
            
            $data .= '</div>';
        }

        $event->setData($data);
    }
    
    public function baseUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();
        
        WINKS_BOL_Service::getInstance()->deleteWinkByUserId($params['userId']);
    }
    
    public function baseUserSuspend( OW_Event $event )
    {
        $params = $event->getParams();
        
        WINKS_BOL_Service::getInstance()->setStatusByUserId($params['userId'], WINKS_BOL_WinksDao::STATUS_IGNORE);
    }
    
    public function getIsWinkSent( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['partnerId']) )
        {
            return false;
        }
        
        $userId = (int)$params['userId'];
        $partnerId = (int)$params['partnerId'];
        
        $wink = WINKS_BOL_WinksDao::getInstance()->findByUserIdAndPartnerId($userId, $partnerId);

        if ( $wink === NULL || $wink->getStatus() == WINKS_BOL_WinksDao::STATUS_ACCEPT )
        {
            return FALSE;
        }
        
        return TRUE;
    }

    public function winkBack( OW_Event $event )
    {
        $params = $event->getParams();
        $service = WINKS_BOL_Service::getInstance();

        if ( empty($params['userId']) || empty($params['partnerId']) || empty($params['messageId']) || ($wink = $service->findWinkByUserIdAndPartnerId($params['userId'], $params['partnerId'])) === NULL )
        {
            $event->setData(array('result' => FALSE, 'msg' => OW::getLanguage()->text('winks', 'wink_back_error')));

            return $event->getData();
        }

        if ( !$service->isWinkBacked($wink->getId()) && $service->setWinkback($wink->getId(), TRUE) )
        {
            if ( empty($params['sendNotification']) && OW::getPluginManager()->isPluginActive('notifications') )
            {
                $rule = NOTIFICATIONS_BOL_Service::getInstance()->findRuleList($wink->userId, array('wink_email_notification'));

                if ( !isset($rule['wink_email_notification']) || (int)$rule['wink_email_notification']->checked )
                {
                    $service->sendWinkEmailNotification($wink->partnerId, $wink->userId, WINKS_BOL_Service::EMAIL_BACK);
                }
            }

            $winkBackEvent = new OW_Event('winks.onWinkBack', array(
                'userId' => $wink->getUserId(),
                'partnerId' => $wink->getPartnerId(),
                'conversationId' => $wink->getConversationId(),
                'content' => array(
                    'entityType' => 'wink',
                    'eventName' => 'renderWinkBack',
                    'params' => array(
                        'winkId' => $wink->id,
                        'messageId' => $params['messageId']
                    )
                )
            ));
            OW::getEventManager()->trigger($winkBackEvent);

            $event->setData(array(
                'result' => true,
                'userId' => $wink->getUserId(),
                'partnerId' => $wink->getPartnerId()
            ));
        }
        else
        {
            $event->setData(array(
                'result' => FALSE,
                'msg' => OW::getLanguage()->text('winks', 'wink_back_error')
            ));
        }

        return $event->getData();
    }
}
