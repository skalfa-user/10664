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
class PROTECTEDPHOTOS_CLASS_FormElement extends FormElement
{
    public function replace( $markup, array $placeholder )
    {
        $vars = array();

        foreach ( $placeholder as $key => $value )
        {
            $vars['{$' . $key .'}'] = $value;
        }

        return str_replace(array_keys($vars), $placeholder, $markup);
    }
}