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
 * Form element: RadioGroupItemField.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.classes
 * @since 1.0
 */
class RadioGroupItemField extends RadioField
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