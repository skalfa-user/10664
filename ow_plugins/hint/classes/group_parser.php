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
class HINT_CLASS_GroupParser extends HINT_CLASS_Parser
{
    const ROUTE_NAME = 'groups-view';

    public function __construct()
    {
        $routeMask = OW::getRouter()->urlForRoute(self::ROUTE_NAME, array(
            'groupId' => '--PLACEHOLDER--'
        ));

        $parseMask = "^" . str_replace('--PLACEHOLDER--', '([\d]+)$', $routeMask);
        
        parent::__construct($parseMask, array());
     }

    public function parse($url)
    {
        $match = array();
        preg_match('~' . $this->mask . '~', $url, $match);

        $groupId = $match[1];
        $group = HINT_CLASS_GroupsBridge::getInstance()->getGroupById($groupId);
        
        if ( $group === null )
        {
            return null;
        }

        return array(
            'groupId' => $groupId
        );
    }

    public function renderHint( array $params )
    {
        $hint = new HINT_CMP_GroupHint($params['groupId']);

        return array(
            'body' => $hint->render(),
            'topCorner' => $hint->renderTopCover(),
            'rightCorner' => $hint->renderRightCover(),
            'bottomCorner' => $hint->renderBottomCover()
        );
    }
}

