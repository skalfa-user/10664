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
class PROTECTEDPHOTOS_CLASS_PrivacyFormElement extends PROTECTEDPHOTOS_CLASS_FormElement
{
    private $options = array();
    private $privacyFormElement;
    private $albumId;

    public function __construct( $name, $albumId = null )
    {
        parent::__construct($name);

        $this->albumId = $albumId;
        $this->privacyFormElement = new HiddenField($name);
        $this->privacyFormElement->setRequired();

        OW::getLanguage()->addKeyForJs('protectedphotos', 'privacy_label');
    }

    public function getOptions()
    {
        return $this->options;
    }


    public function setOptions( array $options )
    {
        $this->options = $options;

        return $this;
    }

    public function getDefaultOption()
    {
        foreach ( $this->options as $option )
        {
            if ( !empty($option['default']) )
            {
                return $option;
            }
        }

        return null;
    }

    public function setDefaultOption( $defaultOption )
    {
        $oldDef = $newDef = false;

        foreach ( $this->options as $key => $option )
        {
            if ( isset($option['default']) )
            {
                unset($this->options[$key]['default']);
                $oldDef = true;
            }

            if ( $key === $defaultOption )
            {
                $this->options[$key]['default'] = $newDef = true;
            }

            if ( $oldDef && $newDef )
            {
                break;
            }
        }
    }

    public function setValue( $value )
    {
        $this->privacyFormElement->setValue($value);
    }

    public function getValue()
    {
        return $this->privacyFormElement->getValue();
    }

    public function getElementJs()
    {
        $jsString = $this->privacyFormElement->getElementJs();

        return $jsString . UTIL_JsGenerator::composeJsString(';
            $({$id} + " .ow_context_action_list").on("click", "li a", function()
            {
                $({$privacyId}).val(this.getAttribute("data-privacy"));
                $({$id} + " .ow_context_action_value").text(OW.getLanguageText("protectedphotos", "privacy_label", {
                    "default": $(this).text()
                }));
                OW.trigger("protectedphotos_change_privacy", this);
            });', array(
                'id' => '#' . $this->getId(),
                'privacyId' => '#' . $this->privacyFormElement->getId()
            )
        );
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $password = PROTECTEDPHOTOS_BOL_Service::getInstance()->findPasswordForAlbumByAlbumId($this->albumId);

        if ( $password !== null )
        {
            $this->setDefaultOption($password->privacy);
        }

        $inputMarkup = '<div id="{$id}" class="ow_context_action_block ow_context_action_value_block clearfix">
                {$hiddenPrivacy}
                <div class="ow_context_action">
                    <a href="javascript://" class="ow_context_action_value">{$label}</a>
                    <span class="ow_context_more"></span>
                    <div style="opacity: 1; top: 18px;" class="ow_tooltip ow_small ow_tooltip_top_right">
                        <div class="ow_tooltip_tail">
                            <span></span>
                        </div>
                        <div class="ow_tooltip_body">
                            <ul class="ow_context_action_list ow_border">
                                {$options}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>';

        $optionsMarkup = array_reduce($this->options, function( $carry, $option )
        {
            $carry .= sprintf('<li><a href="javascript://" data-privacy="%s">%s</a></li>', $option['name'], $option['label']);

            return $carry;
        }, '');

        $defaultOption = $this->getDefaultOption();
        $this->privacyFormElement->setValue($defaultOption !== null ? $defaultOption['name'] : null);

        $placeholder = array(
            'id' => $this->getId(),
            'hiddenPrivacy' => $this->privacyFormElement->renderInput(),
            'label' => OW::getLanguage()->text('protectedphotos', 'privacy_label', array(
                'default' => ($defaultOption === null ? '' : $defaultOption['label'])
            )),
            'options' => $optionsMarkup
        );

        return $this->replace($inputMarkup, $placeholder);
    }
}
