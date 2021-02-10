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
 * @package hint.bol
 */
class HINT_BOL_Service
{
    const EVENT_COLLECT_BUTTONS = 'hint.collect_buttons';
    const EVENT_COLLECT_BUTTONS_CONFIG = 'hint.collect_buttons_config';
    
    const EVENT_COLLECT_INFO_CONFIG = 'hint.collect_info_config';
    const EVENT_INFO_PREVIEW = 'hint.info_preview';
    const EVENT_INFO_RENDER = 'hint.info_render';

    const EVENT_COLLECT_BUTTONS_PREVIEW = 'hint.collect_buttons_preview';
    const EVENT_HINT_RENDER = 'hint.hint_render';
    const EVENT_QUERY = 'hint.query';
    
    const INFO_LINE0 = "line0";
    const INFO_LINE1 = "line1";
    const INFO_LINE2 = "line2";

    const ENTITY_TYPE_USER = 'user';
    const ENTITY_TYPE_GROUP = 'group';
    const ENTITY_TYPE_EVENT = 'event';

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    private function fetchButtons( $entityType, $buttons )
    {
        $out = array();
        
        $_order = $this->getButtonsOrder($entityType);
        $order = array_flip($_order);
        
        $ordered = array();
        $notOrdered = array();
        
        foreach ( $buttons as $button )
        {
            if ( empty($button["key"]) )
            {
                continue;
            }

            $key = $button["key"];

            if ( isset($order[$key]) )
            {
                $ordered[$order[$key]] = $button;
            }
            else
            {
                $notOrdered[] = $button;
            }
        }

        ksort($ordered);
        
        foreach ( $notOrdered as $btn )
        {
            $ordered[] = $btn;
        }
        
        return $ordered;
    }

    public function getButtonsOrder( $entityType )
    {
        $order = $this->getConfig($entityType . "_order");
        
        if ( $order === null )
        {
            return array();
        }
        
        return json_decode($order, true);
    }
    
    public function setButtonsOrder( $entityType, array $order )
    {
        $this->saveConfig($entityType . "_order", json_encode($order));
    }

    public function getButtonList( $entityType, $entityId )
    {
        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_BUTTONS, array(
            "entityType" => $entityType,
            "entityId" => $entityId
        ));

        OW::getEventManager()->trigger($event);
        $buttons = $this->fetchButtons($entityType, $event->getData());

        $settings = $this->getButtonsSettings($entityType);
        
        $out = array();
        foreach ( $buttons as $btn )
        {
            if ( empty($settings[$btn["key"]]["active"]) )
            {
                continue;
            }
            
            $out[] = $btn;
        }
        
        return $out;
    }

    public function getPreviewButtonList( $entityType )
    {
        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_BUTTONS_PREVIEW, array(
            "entityType" => $entityType
        ));

        OW::getEventManager()->trigger($event);
        $buttons = $this->fetchButtons($entityType, $event->getData());

        return $buttons;
    }
    
    public function getButtonsSettings( $entityType )
    {
        $event = new BASE_CLASS_EventCollector(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array(
            "entityType" => $entityType
        ));

        OW::getEventManager()->trigger($event);
        
        $out = array();
        
        foreach ( $event->getData() as $button )
        {
            if ( empty($button["key"]) )
            {
                continue;
            }

            $key = $button["key"];

            $out[$key] = $button;
        }

        return $out;
    }

    public function getConfig( $name )
    {
        return OW::getConfig()->getValue("hint", $name);
    }

    public function saveConfig( $name, $value )
    {
        if ( OW::getConfig()->configExists("hint", $name) )
        {
            OW::getConfig()->saveConfig("hint", $name, $value);
        }
        else
        {
            OW::getConfig()->addConfig("hint", $name, $value);
        }
    }

    public function isActionActive( $entityType, $action )
    {
        return $this->getConfig("action-" . $entityType . "-" . $action);
    }

    public function setActionActive( $entityType, $action, $active = true )
    {
        $this->saveConfig("action-" . $entityType . "-" . $action, $active ? 1 : 0);
    }

    public function setActionOption($entityType, $action, $option, $value) {
        $this->saveConfig("option-" . $entityType . "-" . $action . "-" . $option, $value);
    }

    public function getActionOption($entityType, $action, $option) {
        return $this->getConfig("option-" . $entityType . "-" . $action . "-" . $option);
    }

	public function getInfoLineSettings( $entityType, $line = null )
    {
        $event = new BASE_CLASS_EventCollector(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array(
            "entityType" => $entityType,
            "line" => $line
        ));

        OW::getEventManager()->trigger($event);
        
        $out = array();
        $system = array();
        
        foreach ( $event->getData() as $info )
        {
            if ( empty($info["key"]) )
            {
                continue;
            }

            $key = $info["key"];

            if ( $key == "base-question" )
            {
                $system[$key] = $info;
            }
            else
            {
                $out[$key] = $info;
            }
            
        }

        return $out + $system;
    }
    
    public function getInfoLinePreview( $entityType, $key, $question, $line )
    {
        $event = new OW_Event(HINT_BOL_Service::EVENT_INFO_PREVIEW, array(
            "entityType" => $entityType,
            "key" => $key,
            "question" => $question,
            "line" => $line
        ));
        
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }
    
    public function getInfoLine( $entityType, $entityId, $line )
    {
        $infoConfig = $this->getInfoConfig($entityType, $line);
        
        $key = empty($infoConfig["key"]) ? null : $infoConfig["key"];
        $question = empty($infoConfig["question"]) ? null : $infoConfig["question"];
        
        if ( $key != "base-question" )
        {
            $question = null;
        }
        
        $event = new OW_Event(HINT_BOL_Service::EVENT_INFO_RENDER, array(
            "entityType" => $entityType,
            "entityId" => $entityId,
            "key" => $key,
            "question" => $question,
            "line" => $line
        ));
        
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }
    
    public function saveInfoConfig( $entityType, $line, $key, $question )
    {
        $value = array(
            "key" => $key,
            "question" => $question
        );
        
        $this->saveConfig("info_" . $entityType . "_" . $line, json_encode($value));
    }
    
    public function getInfoConfig( $entityType, $line )
    {
        $value = $this->getConfig("info_" . $entityType . "_" . $line);
        
        if ( $value === null )
        {
            return array(
                "key" => null,
                "question" => null
            );
        }
        
        return json_decode($value, true);
    }
}