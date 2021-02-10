<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Class SKMOBILEAPP_CLASS_LanguageEventCollector
 */
class SKMOBILEAPP_CLASS_LanguageEventCollector extends OW_Event
{
    /**
     * SKMOBILEAPP_CLASS_LanguageEventCollector constructor.
     *
     * @param $name
     * @param array $params
     */
    public function __construct( $name, $params = array() )
    {
        parent::__construct($name, $params);

        $this->data = array();
    }

    /**
     * Add translation
     *
     * @param $prefix
     * @param array $translations
     * @example $translations = ['credits' => 'Credits']
     */
    public function add( $prefix, array $translations )
    {
        if ( !empty($translations) )
        {
            foreach ( $translations as $key => $translate )
            {
                $this->data[$prefix . '_' . $key] = $translate;
            }
        }
    }

    public function setData( $data )
    {
        throw new LogicException("Can't set data in collector event `" . $this->getName() . "`!");
    }
}