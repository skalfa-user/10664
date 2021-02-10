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
 * Form element: RadioGroupItemField.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.matchmaking.classes
 * @since 1.0
 */
class MATCHMAKING_CLASS_RadioGroupItemField extends RadioField
{

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( isset($params['checked']) )
        {
            $this->addAttribute(FormElement::ATTR_CHECKED, 'checked');
        }

        $label = isset($params['label']) ? $params['label'] : '';

        $this->addAttribute('value', $params['value']);
        $this->setId(UTIL_HtmlTag::generateAutoId('input'));

        $renderedString = '<label>' . UTIL_HtmlTag::generateTag('input', $this->attributes) . $label . '</label>';

        $this->removeAttribute(FormElement::ATTR_CHECKED);

        return $renderedString;
    }
}