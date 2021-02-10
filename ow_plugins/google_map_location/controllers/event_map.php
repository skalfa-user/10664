<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.controllers
 * @since 1.0
 */


class GOOGLELOCATION_CTRL_EventMap extends OW_ActionController
{
    const MAX_EVENT_COUNT = 16;
    
    private function getEventMapCmp($backUri = null)
    {
        if( !OW::getPluginManager()->isPluginActive('event') )
        {
            throw new Redirect404Exception();
        }
        
        $map = GOOGLELOCATION_BOL_LocationService::getInstance()->getMapComponent();
        $map->setHeight('600px');
        $map->setZoom(2);
        $map->setCenter(30,10);
        //$map->setMapOption('scrollwheel', 'false');

        $locationList = GOOGLELOCATION_BOL_LocationService::getInstance()->getAllLocationsForEntityType('event');

        $entityIdList = array();
        $entityLocationList = array();

        foreach( $locationList as $location )
        {
            $entityIdList[$location['entityId']] = $location['entityId'];
            $entityLocationList[$location['entityId']] = $location;
        }
        $locationList = $entityLocationList;
        
        $eventsList = EVENT_BOL_EventService::getInstance()->findPublicEvents(null, 1000);
        $publicEventsId = array();
        $tmpEventList = array();
        
        foreach( $eventsList as $event )
        {
            $publicEventsId[$event->id] = $event->id;
            $tmpEventList[$event->id] = $event;
        }
        $eventsList = $tmpEventList;
        
        $entityIdList = array_intersect($entityIdList, $publicEventsId);
         
        $publicLocationList = array();
        $publicEventList = array();
        
        foreach( $entityIdList as $entityId )
        {
            $publicLocationList[$entityId] = $locationList[$entityId];
            $publicEventList[$entityId] = $eventsList[$entityId];
        }
        
        $events = EVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($publicEventList);
                
        $pointList = GOOGLELOCATION_BOL_LocationService::getInstance()->getPointList($publicLocationList);
        
        foreach( $pointList as $point )
        {
            if( !empty( $point['entityIdList'] ) )
            {
                $content = "";
                
                if ( $point['count'] > 1 ) 
                {
                    $listCmp = new GOOGLELOCATION_CMP_MapEventList($point['entityIdList'], $point['location']['lat'], $point['location']['lng'], $backUri);
                    $content .= $listCmp->render();
                    unset($listCmp);
                }
                else 
                {
                    $eventId = current($point['entityIdList']);
                    
                    if( !empty($events[$eventId]) )
                    {
                        $cmp = new GOOGLELOCATION_CMP_MapItem();
                        $cmp->setAvatar(array('src' => $events[$eventId]['imageSrc'] ));
                        $content = "<a href='{$events[$eventId]['eventUrl']}'>".$events[$eventId]['title']."</a>
                            <div>{$events[$eventId]['content']}</div>
                            <div>{$publicLocationList[$eventId]['address']}</div> ";
                        $cmp->setContent($content);

                        $content = $cmp->render();
                    }
                }
                
                if ( !empty($content) )
                {
                    $map->addPoint($point['location'], '', $content);
                }
            }
        }
        
        return $map;
    }
    
    public function map()
    {
        $event = new OW_Event('event.is_plugin_active');
        OW::getEventManager()->trigger($event);
        
        $data = $event->getData();
        
        if ( !$data )
        {
            throw new Redirect404Exception();
        }
        
        $event = new OW_Event('event.get_content_menu');
        OW::getEventManager()->trigger($event);
        
        $menu = $event->getData();
        
        $menu = EVENT_BOL_EventService::getInstance()->getContentMenu();
        $menu->getElement('events_map')->setActive(true);
        $this->addComponent('menu', $menu);

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('googlelocation', 'map_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_bookmark');
        
        $this->addComponent("map", $this->getEventMapCmp(OW::getRouter()->getUri()));
        
        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));
    }
}
