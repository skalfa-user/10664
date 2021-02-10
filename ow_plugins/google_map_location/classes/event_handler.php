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
 * @package ow_plugins.google_maps_location.classes
 * @since 1.0
 */
class GOOGLELOCATION_CLASS_EventHandler
{
    public $jsLibAdded = 0;

    public function __construct()
    {

    }

    function onEventDelete( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['eventId']) )
        {
            GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityIdAndEntityType((int) $params['eventId'], GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);
        }
    }

    function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['userId']) )
        {
            $userId = (int) $params['userId'];
            GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityIdAndEntityType($userId, GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER);
        }
    }

    function addUserListData( BASE_CLASS_EventCollector $event )
    {
        $event->add(
            array(
                'label' => OW::getLanguage()->text('googlelocation', 'users_map_menu_item'),
                'url' => OW::getRouter()->urlForRoute('googlelocation_user_map', array('list' => 'map')),
                'iconClass' => 'ow_ic_bookmark',
                'key' => 'map',
                'order' => 6
            )
        );
    }

    // -- question --

    function questionsFieldInit( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['fieldName'] == 'googlemap_location' )
        {
            $formElement = new GOOGLELOCATION_CLASS_Location($params['fieldName']);
            $formElement->setDisplayMap(!GOOGLELOCATION_BOL_LocationService::getInstance()->isMapDisabledOnProfilePages());

            if ( $params['type'] == 'search' )
            {
                $formElement = new GOOGLELOCATION_CLASS_LocationSearch($params['fieldName']);
                $formElement->setInvitation(OW::getLanguage()->text('googlelocation', 'googlemap_location_search_invitation'));
                $formElement->setHasInvitation(true);

                if ( OW::getUser()->isAuthenticated() && OW::getConfig()->getValue('googlelocation', 'auto_fill_location_on_search') )
                {
                    $data = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('googlemap_location'));

                    if ( !empty($data[OW::getUser()->getId()]['googlemap_location']['json']) )
                    {
                        $formElement->setValue($data[OW::getUser()->getId()]['googlemap_location']);
                    }
                }
            }

            $e->setData($formElement);
        }
    }

    function questionsSaveData( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        foreach ( $data as $key => $value )
        {
            if ( $key == 'googlemap_location' )
            {
                $element = new GOOGLELOCATION_CLASS_Location('location');
                $element->setValue($value);
                $valueList = $element->getListValue();

                if ( !empty($valueList['remove']) && $valueList['remove'] == "true" )
                {
                    GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityIdAndEntityType($params['userId'], 'user');
                    $data[$key] = '';
                    continue;
                }

                if ( empty($valueList) || empty($valueList['json']) )
                {
                    unset($data[$key]);
                    continue;
                }

                $json = !empty($valueList['json']) ? json_decode($valueList['json'], true) : array();

                $countryCode = "";
                if ( !empty($json['address_components']) )
                {
                    foreach ( $json['address_components'] as $component )
                    {
                        if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                        {
                            $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                        }
                    }
                }

                $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserId($params['userId']);

                if ( empty($location) )
                {
                    $location = new GOOGLELOCATION_BOL_Location();
                }

                $location->entityId = (int) $params['userId'];
                $location->countryCode = $countryCode;
                $location->address = !empty($valueList['address']) ? $valueList['address'] : "";
                $location->lat = (float) $valueList['latitude'];
                $location->lng = (float) $valueList['longitude'];
                $location->northEastLat = (float) $valueList['northEastLat'];
                $location->northEastLng = (float) $valueList['northEastLng'];
                $location->southWestLat = (float) $valueList['southWestLat'];
                $location->southWestLng = (float) $valueList['southWestLng'];
                $location->json = !empty($valueList['json']) ? $valueList['json'] : "";
                $location->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER;

                GOOGLELOCATION_BOL_LocationService::getInstance()->save($location);

                $data[$key] = $location->address;
            }
        }

        $e->setData($data);
    }

    function questionsFieldGetValue( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['fieldName']) && $params['fieldName'] == 'googlemap_location' && !empty($params['value']) )
        {
            $location = $params['value'];

            if ( !empty($location['json']) )
            {

                $userViewPresentation = OW::getConfig()->getValue('base', 'user_view_presentation');

                $locationDto = new GOOGLELOCATION_BOL_Location();

                $locationDto->entityId = (int) $params['userId'];
                $locationDto->address = !empty($location['address']) ? $location['address'] : "";
                $locationDto->lat = (float) $location['latitude'];
                $locationDto->lng = (float) $location['longitude'];
                $locationDto->northEastLat = (float) $location['northEastLat'];
                $locationDto->northEastLng = (float) $location['northEastLng'];
                $locationDto->southWestLat = (float) $location['southWestLat'];
                $locationDto->southWestLng = (float) $location['southWestLng'];
                $locationDto->json = !empty($location['json']) ? $location['json'] : "";
                $locationDto->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER;

                $location = get_object_vars($locationDto);

                OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));

                $displayHint = !GOOGLELOCATION_BOL_LocationService::getInstance()->isMapDisabledOnProfilePages()
                    ? 'class="map-hint-target" data-location=\''.htmlspecialchars(json_encode($location), ENT_QUOTES).'\'' : '';

                $data = '<div class="ow_googlemap_location_view_presentation">
                            <div class="ow_googlemap_location_address" >
                                <a '.$displayHint.' href="javascript://">' . $location['address'] . '</a><span class="googlemap_pin ic_googlemap_pin"></span>
                            </div>
                            <div class="ow_googlemap_location_map" style="width:65%; display:none;">
                            </div>
                         </div>';
            }
        }

        $e->setData($data);
    }

    function questionsGetData( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['fieldsList']) || empty($params['userIdList']) || !is_array($params['userIdList']) )
        {
            return;
        }

        $locationList = GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserIdList($params['userIdList']);

        foreach ( $data as $userId => $questions )
        {
            foreach ( $params['fieldsList'] as $key )
            {
                if ( $key == 'googlemap_location' )
                {
                    $location = !empty($locationList[$userId]) ? $locationList[$userId] : null;

                    if ( $location )
                    {
                        $data[$userId][$key] = array(
                            'address' => $location->address,
                            'latitude' => $location->lat,
                            'longitude' => $location->lng,
                            'northEastLat' => $location->northEastLat,
                            'northEastLng' => $location->northEastLng,
                            'southWestLat' => $location->southWestLat,
                            'southWestLng' => $location->southWestLng,
                            'json' => $location->json
                        );
                    }
                }
            }
        }

        $e->setData($data);
    }

// -- question --
// -- search --

    function questionSearchSql( BASE_CLASS_QueryBuilderEvent $e )
    {
        $params = $e->getParams();
        $question = !empty($params['question']) ? $params['question'] : null;
        $questionValue = !empty($params['value']) ? $params['value'] : null;

        if ( !empty($question) && $question->name == 'googlemap_location' )
        {
            if ( empty($questionValue) || empty($questionValue['json']) )
            {
                $e->addWhere(" 1 ");
                return;
            }

            $element = new GOOGLELOCATION_CLASS_Location('location');
            $element->setValue($params['value']);
            $value = $element->getListValue();

            $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

            $countryCode = "";
            if ( !empty($json['address_components']) )
            {
                foreach ( $json['address_components'] as $component )
                {
                    if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                    {
                        $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                    }
                }
            }

            if ( !empty($value['distance']) && (float) $value['distance'] > 0 )
            {
                $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($value['southWestLat'], $value['southWestLng'], 'sw', (float) $value['distance']);
                $value['southWestLat'] = $coord['lat'];
                $value['southWestLng'] = $coord['lng'];

                $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($value['northEastLat'], $value['northEastLng'], 'ne', (float) $value['distance']);
                $value['northEastLat'] = $coord['lat'];
                $value['northEastLng'] = $coord['lng'];
            }

            if ( GOOGLELOCATION_BOL_LocationService::DEGUG_MODE ) {
                OW::getSession()->set('googlemaps_search_bounds', $value);
            }

            $sql = GOOGLELOCATION_BOL_LocationService::getInstance()->getSearchInnerJoinSql('user', $value['southWestLat'], $value['southWestLng'], $value['northEastLat'], $value['northEastLng'], $countryCode);
            $e->addJoin($sql);
        }
    }

// -- search --

    function addJsLib( OW_Event $e )
    {
        if ( !$this->jsLibAdded )
        {
            $languageCode = GOOGLELOCATION_BOL_LocationService::getInstance()->getLanguageCode();

            $apiKey = GOOGLELOCATION_BOL_LocationService::getInstance()->getApiKey();
            $key = "";
            if ( !empty($apiKey) )
            {
                $key = '&key=' . $apiKey;
            }

            $isSSL = false;
            $protocol = "//";

            if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) )
            {
                $isSSL = true;
            }

            $baseJsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
            $plugin = OW::getPluginManager()->getPlugin('googlelocation');
            $build = OW::getPluginManager()->getPlugin('googlelocation')->getDto()->getBuild();
            OW::getDocument()->addScript($baseJsDir . "jquery-ui.min.js");

            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.js?b='.(int)$build, null, GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.migrate.js?b='.(int)$build, null, GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.ui.js?b='.(int)$build, null, GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY);

            OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'location.css?b='.(int)$build);


            $staticUrl = $plugin->getStaticUrl();
            OW::getDocument()->addOnloadScript(" window.marckerClusterImagesUrl = ".json_encode($staticUrl.'img').";", -1000);

            $apiLoadUrl = '';
            switch( GOOGLELOCATION_BOL_LocationService::getInstance()->getMapProvider() )
            {
                case GOOGLELOCATION_BOL_LocationService::PROVIDER_GOOGLE:

                    $apiLoadUrl = $protocol.'maps.google.com/maps/api/js?' . $key . '&language=' . $languageCode. '&callback=googlemaplocation_api_loading_complete';

                    OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'InfoBubble.js?b='.(int)$build);
                    OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'markerclusterer.js?b='.(int)$build);

                    // MAP
                    OW::getDocument()->addScript($plugin->getStaticJsUrl().'google/map.js?b='.(int)$build, "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);

                    break;

                case GOOGLELOCATION_BOL_LocationService::PROVIDER_BING:

                    $apiLoadUrl = $protocol.'www.bing.com/api/maps/mapcontrol?callback=googlemaplocation_api_loading_complete' . $key . '&mkt=' . $languageCode;
                    $credentials = $apiKey;

                    // MAP
                    OW::getDocument()->addScript($plugin->getStaticJsUrl().'bing/map.js?b='.(int)$build, "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);
                    OW::getDocument()->addOnloadScript(" GOOGLELOCATION.credentials = ".json_encode($credentials).";");

                    break;
            }

            OW::getDocument()->addOnloadScript("             
                        const mapScriptUrl = ".json_encode($apiLoadUrl).";
                        const script = document.createElement(\"script\");
                        script.setAttribute('defer', '');
                        script.setAttribute('async', '');
                        script.setAttribute(\"type\", \"text/javascript\");
                        script.setAttribute(\"src\", mapScriptUrl);
                        document.body.appendChild(script); 
                    ", 1000);

            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'autocomplete.js?b='.(int)$build,  "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'map_hint.js?b='.(int)$build);

            $hintTemplate = new GOOGLELOCATION_CMP_MapHintTemplate();
            $template = $hintTemplate->render();

            OW::getDocument()->addOnloadScript('
                if(!GOOGLELOCATION_INIT_SCOPE) {
                    window.GOOGLELOCATION_INIT_SCOPE = [];
                }                
            
                window.GOOGLELOCATION_INIT_SCOPE.push(function() {
                    $("head").append('.json_encode($template).')

                    if( !window.map )
                    {
                        window.map = {};
                    }
                    
                    if ( !window.googlelocation_hint_init )
                    {
                        GoogleMapLocationHint.LAUNCHER().init('.json_encode(GOOGLELOCATION_BOL_LocationService::getInstance()->getDefaultMarkerIcon()).');
                        window.googlelocation_hint_init = true;
                    }
                });
            ');

            $this->jsLibAdded = 1;
        }
    }

    function addFakeQuestions( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['name']) && $params['name'] == 'googlemap_location' )
        {
            $e->setData(false);
        }
    }

    function getMapComponent( OW_Event $e )
    {

        $params = $e->getParams();

        $userIdList = !empty($params['userIdList']) ? $params['userIdList'] : array();
        $backUri = !empty($params['backUri']) ? $params['backUri'] : OW::getRouter()->getUri();

        $map = GOOGLELOCATION_BOL_LocationService::getInstance()->getUserListMapCmp($userIdList, $backUri);

        $e->setData($map);
    }

    function eventEditLocationInit( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['name'] == 'location' )
        {
            /* @var $formElement TextField  */
            $formElement = $e->getData();
            $label = $formElement->getLabel();

            $uriParams = OW::getRequest()->getUriParams();

            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setLabel($label);
            $locationFormElement->setDisplayMap(!GOOGLELOCATION_BOL_LocationService::getInstance()->isMapDisabledOnProfilePages());

            if ( !empty($uriParams['eventId']) )
            {
                $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByEntityIdAndEntityType((int) $uriParams['eventId'], GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);

                if ( !empty($location) && $location instanceof GOOGLELOCATION_BOL_Location )
                {
                    $value = array(
                        'address' => $location->address,
                        'latitude' => $location->lat,
                        'longitude' => $location->lng,
                        'northEastLat' => $location->northEastLat,
                        'northEastLng' => $location->northEastLng,
                        'southWestLat' => $location->southWestLat,
                        'southWestLng' => $location->southWestLng,
                        'json' => $location->json
                    );

                    $locationFormElement->setValue($value);
                }
            }

            $e->setData($locationFormElement);
        }
    }

    function beforeEventEdit( OW_Event $e )
    {
        $data = $e->getData();
        $params = $e->getParams();

        if ( !empty($data['location']) && !empty($params['eventId']) )
        {
            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setValue($data['location']);
            $value = $locationFormElement->getValue();

            if ( !empty($value) )
            {

                $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

                $countryCode = "";
                if ( !empty($json['address_components']) )
                {
                    foreach ( $json['address_components'] as $component )
                    {
                        if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                        {
                            $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                        }
                    }
                }

                $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByEntityIdAndEntityType((int) $params['eventId'], 'event');

                if ( empty($location) )
                {
                    $location = new GOOGLELOCATION_BOL_Location();
                }

                $location->entityId = (int) $params['eventId'];
                $location->countryCode = $countryCode;
                $location->address = !empty($value['address']) ? $value['address'] : "";
                $location->lat = (float) $value['latitude'];
                $location->lng = (float) $value['longitude'];
                $location->northEastLat = (float) $value['northEastLat'];
                $location->northEastLng = (float) $value['northEastLng'];
                $location->southWestLat = (float) $value['southWestLat'];
                $location->southWestLng = (float) $value['southWestLng'];
                $location->json = !empty($value['json']) ? $value['json'] : "";
                $location->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT;

                GOOGLELOCATION_BOL_LocationService::getInstance()->save($location);

                $data['location'] = $location->address;
            }
//            else
//            {
//                $data['location'] = "";
//            }
        }
//        else
//        {
//            $data['location'] = "";
//        }

        $e->setData($data);
    }

    function beforeEventCreate( OW_Event $e )
    {
        $data = $e->getData();

        if ( !empty($data['location']) )
        {
            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setDisplayMap(!GOOGLELOCATION_BOL_LocationService::getInstance()->isMapDisabledOnProfilePages());
            $locationFormElement->setValue($data['location']);
            $value = $locationFormElement->getValue();

            if ( !empty($value) )
            {
                OW::getSession()->set('googlelocation_tmp_event_data', $value);
                $data['location'] = !empty($value['address']) ? $value['address'] : "";
            }
//            else
//            {
//                $data['location'] = "";
//            }
        }
//        else
//        {
//            $data['location'] = "";
//        }

        $e->setData($data);
    }

    function afterEventCrate( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['eventDto']) && $params['eventDto'] instanceof EVENT_BOL_Event )
        {
            /* @var $eventDto EVENT_BOL_Event */
            $eventDto = $params['eventDto'];

            $locationValue = OW::getSession()->get('googlelocation_tmp_event_data');
            OW::getSession()->delete('googlelocation_tmp_event_data');

            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setValue($locationValue);
            $value = $locationFormElement->getValue();

            if ( !empty($value) )
            {

                $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

                $countryCode = "";
                if ( !empty($json['address_components']) )
                {
                    foreach ( $json['address_components'] as $component )
                    {
                        if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                        {
                            $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                        }
                    }
                }

                $location = new GOOGLELOCATION_BOL_Location();

                $location->entityId = $eventDto->id;
                $location->countryCode = $countryCode;
                $location->address = !empty($value['address']) ? $value['address'] : "";
                $location->lat = (float) $value['latitude'];
                $location->lng = (float) $value['longitude'];
                $location->northEastLat = (float) $value['northEastLat'];
                $location->northEastLng = (float) $value['northEastLng'];
                $location->southWestLat = (float) $value['southWestLat'];
                $location->southWestLng = (float) $value['southWestLng'];
                $location->json = !empty($value['json']) ? $value['json'] : "";
                $location->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT;

                GOOGLELOCATION_BOL_LocationService::getInstance()->save($location);
            }
        }
        OW::getSession()->delete('googlelocation_tmp_event_data');
    }

    function addEventContentMenuItem( BASE_CLASS_EventCollector $e )
    {
        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('events_map');
        $menuItem->setUrl(OW::getRouter()->urlForRoute('googlelocation_event_map'));
        $menuItem->setLabel(OW::getLanguage()->text('googlelocation', 'events_map_label'));
        $menuItem->setIconClass('ow_ic_bookmark');
        $menuItem->setOrder(5);

        $e->add($menuItem);
    }

    function addEventMapOnViewPage( BASE_CLASS_EventCollector $e )
    {
        $uriParams = OW::getRequest()->getUriParams();

        if ( !empty($uriParams['eventId']) )
        {
            $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByEntityIdAndEntityType((int) $uriParams['eventId'], GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);

            if ( !empty($location) && $location instanceof GOOGLELOCATION_BOL_Location )
            {
                $value = array(
                    'address' => $location->address,
                    'lat' => $location->lat,
                    'lng' => $location->lng,
                    'northEastLat' => $location->northEastLat,
                    'northEastLng' => $location->northEastLng,
                    'southWestLat' => $location->southWestLat,
                    'southWestLng' => $location->southWestLng,
                    'json' => $location->json
                );

                $map = GOOGLELOCATION_BOL_LocationService::getInstance()->getMapComponent();
                $map->setHeight('180px');
                $map->setZoom(9);
                $map->disableDefaultUI(true);
                $map->disableInput(true);
                $map->disablePanning(true);
                $map->disableZooming(false);

                $map->setCenter($value['lat'], $value['lng']);
                $map->setBounds($value['southWestLat'], $value['southWestLng'], $value['northEastLat'], $value['northEastLng']);
                $map->addPoint($value, $value['address']);

                $map->setBox(OW::getLanguage()->text('googlelocation', 'events_widget_label'), 'ow_ic_bookmark', 'ow_std_margin clearfix');

                $mapHtml = $map->render();

                $e->add($mapHtml);
            }
        }
    }

    public function calcDistance( OW_Event $e )
    {
        $params = $e->getParams();

        $lat = !empty($params['lat']) ? (double)$params['lat'] : 0;
        $lon = !empty($params['lon']) ? (double)$params['lon'] : 0;
        $lat1 = !empty($params['lat1']) ? (double)$params['lat1'] : 0;
        $lon1 = !empty($params['lon1']) ? (double)$params['lon1'] : 0;

        $distance = GOOGLELOCATION_BOL_LocationService::getInstance()->distance($lat, $lon, $lat1, $lon1);
        $units = GOOGLELOCATION_BOL_LocationService::getInstance()->getDistanseUnits();

        $data = array(
            'distance' => $distance,
            'units' => $units
        );

        $e->setData($data);
    }

    public function onBeforePluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['pluginKey']) && $params['pluginKey'] == 'event' )
        {
            GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityType(GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);
        }
    }

    function addAdminNotification( BASE_CLASS_EventCollector $event )
    {
        $event->add(OW::getLanguage()->text('googlelocation', 'plugin_require_configuration',
            ['link' => OW::getRouter()->urlForRoute('googlelocation_admin')]));
    }

    public function disableProfileQuestion( OW_Event $e )
    {
        $params = $e->getParams();

        if (!empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'googlemap_location') {
            $disableActionList = array(
                'disable_account_type' => false,
                'disable_answer_type' => false,
                'disable_presentation' => false,
                'disable_column_count' => false,
                'disable_display_config' => false,
                'disable_possible_values' => false,
                'disable_required' => false,
                'disable_on_join' => true,
                'disable_on_view' => true,
                'disable_on_search' => true,
                'disable_on_edit' => true
            );

            $e->setData($disableActionList);
        }
    }

    public function genericInit()
    {
        if ( OW::getPluginManager()->getPlugin('googlelocation')->getDto()->build > 80 ) {
            GOOGLELOCATION_BOL_LocationService::getInstance()->checkApiKey();
            if (!GOOGLELOCATION_BOL_LocationService::getInstance()->isApiKeyExists()) {
                OW::getEventManager()->bind('admin.disable_fields_on_edit_profile_question', array($this, 'disableProfileQuestion'));
                return;
            }
        }

        //add package pointer
        $rootDir = OW::getPluginManager()->getPlugin('googlelocation')->getRootDir();
        $autoloader = OW::getAutoloader();

        $autoloader->addPackagePointer('GOOGLELOCATION_BING_CMP', $rootDir.'providers'.DS.'bing'.DS.'components');
        $autoloader->addPackagePointer('GOOGLELOCATION_BING_CTRL', $rootDir.'providers'.DS.'bing'.DS.'controllers');
        $autoloader->addPackagePointer('GOOGLELOCATION_BING_CLASS', $rootDir.'providers'.DS.'bing'.DS.'class');

        $autoloader->addPackagePointer('GOOGLELOCATION_GOOGLE_CMP', $rootDir.'providers'.DS.'google'.DS.'components');
        $autoloader->addPackagePointer('GOOGLELOCATION_GOOGLE_CTRL', $rootDir.'providers'.DS.'google'.DS.'controllers');
        $autoloader->addPackagePointer('GOOGLELOCATION_GOOGLE_CLASS', $rootDir.'providers'.DS.'google'.DS.'class');
        //--------------------

        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        OW::getEventManager()->bind('base.add_user_list', array($this, 'addUserListData'));
        OW::getEventManager()->bind('base.questions_field_init', array($this, 'questionsFieldInit'));
        OW::getEventManager()->bind('base.questions_save_data', array($this, 'questionsSaveData'));

        OW::getEventManager()->bind('base.questions_get_data', array($this, 'questionsGetData'));
        OW::getEventManager()->bind('base.question.search_sql', array($this, 'questionSearchSql'));

        OW::getEventManager()->bind('base.questions_field_add_fake_questions', array($this, 'addFakeQuestions'));
        OW::getEventManager()->bind('googlelocation.get_map_component', array($this, 'getMapComponent'));

        //// ----------------- Events plugin integation ------------------------------
        OW::getEventManager()->bind('event.event_add_form.get_element', array($this, 'eventEditLocationInit'));
        OW::getEventManager()->bind('events.before_event_edit', array($this, 'beforeEventEdit'));
        OW::getEventManager()->bind('events.before_event_create', array($this, 'beforeEventCreate'));
        OW::getEventManager()->bind('event_after_create_event', array($this, 'afterEventCrate'));
        OW::getEventManager()->bind('event.add_content_menu_item', array($this, 'addEventContentMenuItem'));
        OW::getEventManager()->bind('events.view.content.after_event_description', array($this, 'addEventMapOnViewPage'));
        OW::getEventManager()->bind('event_on_delete_event', array($this, 'onEventDelete'));
        OW::getEventManager()->bind('googlelocation.calc_distance', array($this, 'calcDistance'));

        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onBeforePluginUninstall'));
    }

    public function mobileInit()
    {
        if ( !GOOGLELOCATION_BOL_LocationService::getInstance()->isApiKeyExists() )
        {
            return;
        }

        OW::getEventManager()->bind('googlelocation.add_js_lib', array($this, 'addJsLib'));
        OW::getEventManager()->bind('base.questions_field_get_value', array($this, 'questionsFieldGetValue'));
    }

    public function init()
    {
        if ( !GOOGLELOCATION_BOL_LocationService::getInstance()->isApiKeyExists() )
        {
            OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
            return;
        }

        OW::getEventManager()->bind('googlelocation.add_js_lib', array($this, 'addJsLib'));
        OW::getEventManager()->bind('base.questions_field_get_value', array($this, 'questionsFieldGetValue'));
    }
}

