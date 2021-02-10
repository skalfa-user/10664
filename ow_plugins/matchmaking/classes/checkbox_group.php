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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.matchmaking.classes
 * @since 1.0
 */
class MATCHMAKING_CLASS_CheckboxGroup extends CheckboxGroup
{
    /**
     *  @see FormElement::getElementJs()
     */
    public function getElementJs()
    {

        $id = json_encode($this->getId());
        $name = json_encode($this->getName());
        $js = "var formElement = new MatchmakingCheckboxGroup({$id}, {$name});";

        return $js.$this->generateValidatorAndFilterJsCode("formElement");
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->options === null || empty($this->options) )
        {
            return '';
        }

        $columnWidth = floor(100 / $this->columnsCount);

        $renderedString = '<ul class="ow_checkbox_group clearfix">';

        $noValue = true;
        foreach ( $this->options as $key => $value )
        {
            if ( $this->value !== null && is_array($this->value) && in_array($key, $this->value) )
            {
                $this->addAttribute(FormElement::ATTR_CHECKED, 'checked');
                $noValue = false;
            }

            $this->setId(UTIL_HtmlTag::generateAutoId('input'));

            $this->addAttribute('value', $key);

            $renderedString .= '<li style="width:' . $columnWidth . '%">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '&nbsp;<label for="' . $this->getId() . '">' . $value . '</label></li>';

            $this->removeAttribute(FormElement::ATTR_CHECKED);
        }

        $language = OW::getLanguage();

        $attributes = $this->attributes;
        $attributes['id'] = $this->getName().'_unimportant';
        $attributes['name'] = $this->getName().'_unimportant';

        if ($noValue)
        {
            $attributes[FormElement::ATTR_CHECKED] = 'checked';
        }
        $renderedString .= '<li class="matchmaking_unimportant_checkbox" style="display:block;border-top: 1px solid #bbb; margin-top: 12px;padding-top:6px; width:100%">' . UTIL_HtmlTag::generateTag('input', $attributes) . '&nbsp;<label for="' . $this->getId() . '">' . $language->text('matchmaking', 'this_is_unimportant') . '</label></li>';

        return $renderedString . '</ul>';
    }
}