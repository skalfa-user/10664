<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.classes
 */
abstract class HINT_CLASS_Parser
{
    protected $mask, $ignoreSelectors = array();

    public function __construct( $mask, $ignoreSelectors = array() )
    {
        $this->mask = $mask;
        $this->ignoreSelectors = $ignoreSelectors;
        
        $this->ignoreSelectors[] = ".hint-no-hint";
    }

    public function getMask()
    {
        return $this->mask;
    }
    
    public function getIgnoreSelectors()
    {
        return $this->ignoreSelectors;
    }

    public function test( $url )
    {
        return preg_match('~' . $this->mask . '~', $url);
    }

    abstract public function parse( $url );
    abstract public function renderHint( array $params );
}