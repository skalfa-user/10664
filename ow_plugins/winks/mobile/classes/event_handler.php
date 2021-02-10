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
 * @author Kairat Bakytov <kainisoft@gmail.com>
 * @package ow.ow_plugins.winks.mobile.classes
 * @since 1.7.6
 */
class WINKS_MCLASS_EventHandler
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

    private function __construct()
    {

    }

    public function init()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
        $eventManager->bind('wink.renderWink', array($this, 'onRenderWink'));
        $eventManager->bind('wink.renderWinkBack', array($this, 'onRenderWinkBack'));
        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onCollectProfileActions'));
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

    public function onFinalize( OW_Event $event )
    {
        OW::getDocument()->addScriptDeclaration(UTIL_JsGenerator::composeJsString(';
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
                    'rsp' => OW::getRouter()->urlFor('WINKS_MCTRL_Action', 'winkBack')
                )
            )
        );
    }

    public function onRenderWink( OW_Event $event )
    {
        $params = $event->getParams();

        if ( ($wink = WINKS_BOL_WinksDao::getInstance()->findById($params['winkId'])) === NULL )
        {
            return;
        }

        $winkBack = '<div class="owm_wink owm_std_margin_bottom clearfix">';

        if ( $wink->getUserId() == OW::getUser()->getId() )
        {
            $winkBack .= OW::getLanguage()->text('winks', 'accept_wink_msg');
        }
        else
        {
            $winkBack .= '<div class="owm_wink_pic owm_float_left"></div>
                <div class="owm_wink_info">
                    <div class="owm_wink_text owm_small_margin_bottom">' .
                        BOL_UserService::getInstance()->getDisplayName($wink->getUserId()) . ' ' .
                        OW::getLanguage()->text('winks', 'wink_back_message') . ' </div>';

            if ( $params['winkBackEnabled'] && empty($params['getPreview']))
            {
                $winkBack .= UTIL_JsGenerator::composeJsString(
                    '<a href="javascript://" class="owm_wink_back owm_padding owm_lbutton owm_green" onclick="winks.winkBack.call(this, {userId: {$userId}, partnerId: {$partnerId}, messageId: {$messageId}});">' . OW::getLanguage()->text('winks', 'wink_back_label') . '</a>',
                    array(
                        'userId' => $wink->getUserId(),
                        'partnerId' => $wink->getPartnerId(),
                        'messageId' => $params['messageId']
                    )
                );
            }

            $winkBack .= '</div></div>';
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
                $data .= sprintf('<b>%s</b> %s', BOL_UserService::getInstance()->getDisplayName($wink->getPartnerId()), OW::getLanguage()->text('winks', 'wink_back_message_owner'));
            }
            else
            {
                $data .= OW::getLanguage()->text('winks', 'winked_back_msg');
            }

            $data .= '</div>';
        }

        $event->setData($data);
    }

    public function onCollectProfileActions( BASE_CLASS_EventCollector $event )
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
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => 'winks.send',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $language->text('winks', 'wink_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 2,
                'class' => 'owm_green',
                'group' => 'addition'
            ));

            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(';
                    $(document.getElementById({$id})).on("click", function()
                    {
                        OWM.error({$msg});
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
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => 'winks.send',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $language->text('winks', 'wink_sent_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 2,
                'class' => 'owm_green',
                'group' => 'addition'
            ));

            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(';
                    $(document.getElementById({$id})).on("click", function()
                    {
                        OWM.error({$msg});
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
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => 'winks.send',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $language->text('winks', 'wink_label'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 2,
                'class' => 'owm_green',
                'group' => 'addition'
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
                                userId: {$userId},
                                partnerId: {$partnerId}
                            },
                            success: function( data )
                            {
                                self.html({$sentLabel});
                                self.off().on("click", function()
                                {
                                    OWM.error({$sentErrorMsg});
                                });

                                if ( data && data.result )
                                {
                                    OWM.info({$successMsg});
                                }
                                else
                                {
                                    OWM.error(data.msg);
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown )
                            {
                                throw textStatus;
                            }
                        });
                    });', array(
                        'id' => $uniqId,
                        'url' => OW::getRouter()->urlFor('WINKS_MCTRL_Action', 'sendWink'),
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
}
