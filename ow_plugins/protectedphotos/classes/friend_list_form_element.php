<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.protected_photos.classes
 * @since 1.8.0
 */
class PROTECTEDPHOTOS_CLASS_FriendListFormElement extends PROTECTEDPHOTOS_CLASS_FormElement
{
    public $friendList = array();
    public $selectedList = array();
    public $input;

    public function __construct( $name, $userId )
    {
        parent::__construct($name);

        $this->input = new HiddenField($this->getName());

        $count = OW::getEventManager()->call('plugin.friends.count_friends', array(
            'userId' => $userId
        ));

        if ( (int) $count > 0 )
        {
            $friendIds = OW::getEventManager()->call('plugin.friends.get_friend_list', array(
                'userId' => $userId,
                'count' => $count
            ));
            $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($friendIds);
            $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($friendIds);

            foreach ( $friendIds as $friendId )
            {
                $this->friendList[$friendId] = array(
                    'id' => $friendId,
                    'displayName' => $displayNames[$friendId],
                    'src' => $avatars[$friendId]
                );
            }
        }
    }

    public function setSelectedList( array $selectedList )
    {
        $this->selectedList = $selectedList;
    }

    public function setValue( $value )
    {
        $this->input->setValue($value);
    }

    public function getValue()
    {
        return $this->input->getValue();
    }

    public function getElementJs()
    {
        return $this->input->getElementJs();
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $markup = '{$input}
        <div class="ow_pass_protected_userlist ow_border">
            <div class="ow_privacy_userlist_main">
                {$friends}
            </div>
        </div>';

        $objRef = $this;
        
        $list = array_reduce($this->friendList, function( $carry, $friend ) use ($objRef)
        {
            $carry .= $objRef->replace('<div class="ow_privacy_userlist_main_item ow_border clearfix" data-id="{$id}" data-display-name="{$displayName}" data-src="{$src}">
                <input type="checkbox" class="ow_privacy_userlist_checkbox" {$checked}>
                <div class="ow_privacy_userlist_item_avatar">
                    <div class="ow_avatar">
                        <img style="max-width: 100%;" title="{$displayName}" alt="{$displayName}" src="{$src}">
                    </div>
                </div>
                <div class="ow_privacy_userlist_item_info">
                    <div class="ow_privacy_userlist_item_info_string ow_small">
                        <b>{$displayName}</b>
                    </div>
                </div>
            </div>', array_merge(
                array(
                    'checked' => (in_array($friend['id'], $objRef->selectedList) ? 'checked="checked"' : '')
                ),
                $friend
            ));

            return $carry;
        }, '');

        return $this->replace($markup, array(
            'input' => $this->input->renderInput(),
            'friends' => $list
        ));
    }
}
