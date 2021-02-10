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
class PROTECTEDPHOTOS_CLASS_SelectedFriendListFormElement extends PROTECTEDPHOTOS_CLASS_FormElement
{
    private $selectedFriendList = array();

    private $selectedInput;

    public function __construct( $name, $albumId )
    {
        parent::__construct($name);

        $this->selectedInput = new HiddenField($this->getName());

        $friends = PROTECTEDPHOTOS_BOL_Service::getInstance()->getFriendIds($albumId);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($friends);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($friends);

        foreach ( $friends as $friendId )
        {
            $this->selectedFriendList[$friendId] = array(
                'id' => $friendId,
                'displayName' => $displayNames[$friendId],
                'src' => $avatars[$friendId]
            );
        }

        OW::getLanguage()->addKeyForJs('protectedphotos', 'individual_friends_error');
    }

    public function setValue( $value )
    {
        $this->selectedInput->setValue($value);
    }

    public function getValue()
    {
        return $this->selectedInput->getValue();
    }

    public function addValidator( $validator )
    {
        $this->selectedInput->addValidator($validator);
    }

    public function getElementJs()
    {
        return $this->selectedInput->getElementJs();
    }

    public function getSelectedFriends()
    {
        return array_keys($this->selectedFriendList);
    }

    public function renderInput( $params = null )
    {
        $markup = '<div class="ow_pass_protected_selected_list ow_privacy_userlist_selected ow_right">
            {$input}
            <h3 class="ow_smallmargin">{$title}</h3>
            <div class="ow_privacy_userlist_items">
                <div class="ow_privacy_userlist_selected_content">
                    {$list}
                </div>
            </div>
            <div class="ow_hidden">
                <div class="ow_privacy_userlist_selected_item ow_border ow_bg_color ow_smallmargin clearfix">
                    <div class="ow_privacy_userlist_item_avatar">
                        <div class="ow_avatar">
                            <img style="max-width: 100%;" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D">
                        </div>
                    </div>
                    <div class="ow_privacy_userlist_item_info">
                        <div class="ow_privacy_userlist_item_info_string ow_small">
                            <b></b>
                        </div>
                        <div class="ow_privacy_userlist_item_close ow_border"><a href="javascript://"></a></div>
                    </div>
                </div>
            </div>
        </div>';

        $objRef = $this;
        
        $friendsMarkup = array_reduce($this->selectedFriendList, function( $carry, $friend ) use ($objRef)
        {
            $carry .= $objRef->replace('<div class="ow_privacy_userlist_selected_item ow_border ow_bg_color ow_smallmargin clearfix" data-id="{$id}" data-display-name="{$displayName}" data-src="{$src}">
                <div class="ow_privacy_userlist_item_avatar">
                    <div class="ow_avatar">
                        <img style="max-width: 100%;" title="{$displayName}" alt="{$displayName}" src="{$src}">
                    </div>
                </div>
                <div class="ow_privacy_userlist_item_info">
                    <div class="ow_privacy_userlist_item_info_string ow_small">
                        <b>{$displayName}</b>
                    </div>
                    <div class="ow_privacy_userlist_item_close ow_border"><a href="javascript://"></a></div>
                </div>
            </div>', $friend);

            return $carry;
        }, '');

        return $this->replace($markup, array(
            'input' => $this->selectedInput->renderInput(),
            'title' => OW::getLanguage()->text('protectedphotos', 'selected_title'),
            'list' => $friendsMarkup
        ));
    }
}
