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
 * @package hint.components
 */
class HINT_CMP_EventAttendContext extends OW_Component
{
    public $current;
    
    public function __construct( $eventId, $userId, $uniqId, $inviteBtnId ) 
    {
        parent::__construct();
        
        $language = OW::getLanguage();
        
        $status = HINT_CLASS_EventsBridge::getInstance()->getUserStatus($eventId, $userId);
        
        $statuses = array(
            "1" => array(
                "key" => "yes",
                "label" => $language->text("hint", "event_attend_yes")
            ),
            
            "2" => array(
                "key" => "maybe",
                "label" => $language->text("hint", "event_attend_maybe")
            ),
            
            "3" => array(
                "key" => "no",
                "label" => $language->text("hint", "event_attend_no")
            )
        );
        
        $this->current = empty($status) ? array(
            "label" => $language->text("hint", "button_attend_event_label"),
            "key" => null
        ) : $statuses[$status];
        
        $buttons = array();
        foreach ( $statuses as $s )
        {
            $buttons[] = array(
                "type" => $s["key"],
                "label" => $s["label"],
                "hidden" => $this->current["key"] == $s["key"]
            );
        }
        
        $this->assign("buttons", $buttons);
                
        $js = UTIL_JsGenerator::newInstance();
        
        $js->addScript('new HINT.AttendContext({$uniqId}, {$options}, function(type, typeId) { '
                . ' $("#" + {$inviteBtnId}).show(); '
                . '});', array(
            "uniqId" => $uniqId,
            "inviteBtnId" => $inviteBtnId,
            "options" => array(
                "rsp" => OW::getRouter()->urlFor('EVENT_CTRL_Base', 'attendFormResponder'),
                "eventId" => $eventId,
                "status" => $this->current["key"]
            )
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
}