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
class HINT_CLASS_EventParser extends HINT_CLASS_Parser
{
    const ROUTE_NAME = 'event.view';

    public function __construct()
    {
        $routeMask = OW::getRouter()->urlForRoute(self::ROUTE_NAME, array(
            'eventId' => '--PLACEHOLDER--'
        ));

        $parseMask = "^" . str_replace('--PLACEHOLDER--', '([\d]+)$', $routeMask);
        
        parent::__construct($parseMask, array());
     }

    public function parse($url)
    {
        $match = array();
        preg_match('~' . $this->mask . '~', $url, $match);

        $eventId = $match[1];
        $event = HINT_CLASS_EventsBridge::getInstance()->getEventById($eventId);
        
        if ( $event === null )
        {
            return null;
        }

        return array(
            'eventId' => $eventId
        );
    }

    public function renderHint( array $params )
    {
        $hint = new HINT_CMP_EventHint($params['eventId']);

        return array(
            'body' => $hint->render(),
            'topCorner' => $hint->renderTopCover(),
            'rightCorner' => $hint->renderRightCover(),
            'bottomCorner' => $hint->renderBottomCover()
        );
    }
}

