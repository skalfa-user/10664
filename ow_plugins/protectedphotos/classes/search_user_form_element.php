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
class PROTECTEDPHOTOS_CLASS_SearchUserFormElement extends PROTECTEDPHOTOS_CLASS_FormElement
{
    private $input;
    private $url;

    public function __construct( $name )
    {
        parent::__construct($name);

        $this->input = new TextField($name);
    }

    public function setPlaceholder( $placeholder )
    {
        $this->input->addAttribute('placeholder', (String) $placeholder);

        return $this;
    }

    public function setUrl( $url )
    {
        $this->url = (String) $url;

        return $this;
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
        $jsString = $this->input->getElementJs();

        return $jsString . UTIL_JsGenerator::composeJsString(';var timer, request;
        $({$id}).on("input paste", function()
        {
            if ( timer )
            {
                clearTimeout(timer);
            }

            timer = setTimeout(function()
            {
                request && request.abort();
                var resultList = $({$content} + " .ow_searchbar_ac").hide().empty();

                if (this.value.trim().length < 2) return;

                request = $.ajax({
                    url: {$url},
                    dataType: "json",
                    type: "POST",
                    data: {searchText: this.value.trim()},
                    success: function( list )
                    {
                        if ( !Array.isArray(list) || list.length === 0 ) return;

                        var prototype = $({$content} + " .ow_search_ac_item");

                        list.forEach(function( item )
                        {
                            var itemPrototype = prototype.clone();

                            itemPrototype.attr("data-id", item.id);
                            $(".ow_avatar img", itemPrototype).attr({
                                src: item.src,
                                alt: item.displayName
                            });
                            $(".ow_searchbar_username", itemPrototype).text(item.displayName);
                            resultList.append(itemPrototype.show());
                            OW.trigger("search_item_ready", [itemPrototype]);
                        });

                        resultList.find("li").on("click", function()
                        {
                            resultList.hide().empty();
                            $({$id}).val("").focus();
                            OW.trigger("search_item_click", [this]);
                        });
                        resultList.show();
                        OW.trigger("search_list_ready", [resultList]);
                    },
                    error: function( jqXHR, textStatus, errorThrown )
                    {
                        throw textStatus;
                    }
                });
            }.bind(this), 500);
        });', array(
            'id' => '#' . $this->input->getId(),
            'content' => '#' . $this->getId(),
            'url' => $this->url
        ));
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $markup = '<div id="{$id}" class="ow_pass_protected_userlist_search ow_privacy_userlist_search">
            <div class="ow_searchbar clearfix">
                <div class="ow_searchbar_input ow_left">
                    {$searchInput}
                    <div class="ow_searchbar_ac_wrap">
                        <ul style="display: block" class="ow_searchbar_ac">
                        </ul>
                        <div class="ow_hidden">
                            <li style="display: none" class="no_items clearfix">
                                <span class="ow_searchbar_username ow_small"></span>
                            </li>
                            <li style="display: none" class="ow_search_ac_item clearfix">
                                <div class="ow_search_result_user ow_mini_avatar ow_left">
                                    <div class="ow_avatar">
                                        <img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D" alt="" style="max-width: 100%;">
                                    </div>
                                    <span class="ow_searchbar_username ow_small"></span>
                                </div>
                            </li>
                        </div>
                    </div>
                    <div class="ow_btn_close_search"></div>
                </div>
                <span class="ow_searchbar_btn ow_ic_lens ow_cursor_pointer ow_right"></span>
            </div>
        </div>';
        $placeholder = array(
            'id' => $this->getId(),
            'searchInput' => $this->input->renderInput()
        );

        return $this->replace($markup, $placeholder);
    }
}
