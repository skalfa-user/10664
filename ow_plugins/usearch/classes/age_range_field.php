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
 * Age range form field
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.5.3
 */
class USEARCH_CLASS_AgeRangeField extends AgeRange
{
    public function __construct( $name )
    {
        parent::__construct($name);
    }

    public function renderInput( $params = null )
    {
        $defaultAgeFrom = isset($this->value['from']) ? (int) $this->value['from'] : $this->minAge;
        $defaultAgeTo = isset($this->value['to']) ? (int) $this->value['to'] : $this->maxAge;

        $fromAgeAttrs = $this->attributes;
        $fromAgeAttrs['name'] = $this->getAttribute('name') . '[from]';
        $fromAgeAttrs['type'] = 'text';
        $fromAgeAttrs['maxlength'] = 3;
        $fromAgeAttrs['style'] = 'width: 40px;';
        $fromAgeAttrs['value'] = $defaultAgeFrom;

        if ( isset($fromAgeAttrs['id']) )
        {
            unset($fromAgeAttrs['id']);
        }

        $toAgeAttrs = $this->attributes;
        $toAgeAttrs['name'] = $this->getAttribute('name') . '[to]';
        $toAgeAttrs['type'] = 'text';
        $toAgeAttrs['maxlength'] = 3;
        $toAgeAttrs['style'] = 'width: 40px;';
        $toAgeAttrs['value'] = $defaultAgeTo;

        if ( isset($toAgeAttrs['id']) )
        {
            unset($toAgeAttrs['id']);
        }

        $language = OW::getLanguage();

        $result = '<span id="' . $this->getAttribute('id') . '"class="' . $this->getAttribute('name') . '">
            ' . UTIL_HtmlTag::generateTag('input', $fromAgeAttrs) . '
            ' . '<span class="ow_agerange_to">' . $language->text('base', 'form_element_to') . '</span>
            ' . UTIL_HtmlTag::generateTag('input', $toAgeAttrs) . '</span>';

        return $result;
    }

    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        $js .= "
			formElement.getValue = function(){
				var value = {};
				value.from = $(this.input).find(\"input[name='\" + this.name + \"[from]']\").val();
				value.to = $(this.input).find(\"input[name='\" + this.name + \"[to]']\").val();

                return value;
			};
		";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $js;
    }
}
