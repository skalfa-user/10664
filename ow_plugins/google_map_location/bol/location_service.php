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
 * @package ow_plugins.google_maps_location.bol
 * @since 1.0
 */
final class GOOGLELOCATION_BOL_LocationService
{
    const DEGUG_MODE = false;

    const JQUERY_LOAD_PRIORITY = 100000000;
    
    const STRIP_STR = '#$;?:';
    const MAX_USERS_COUNT = 20;
    const SESSION_VAR_ENTITY_LIST = 'googlelocation_userlist_session_var';
    
    const DISTANCE_UNITS_KM = 'km';
    const DISTANCE_UNITS_MILES = 'miles';
    
    const PROVIDER_GOOGLE = 'google';
    const PROVIDER_BING = 'bing';

    /**
     * @var GOOGLELOCATION_BOL_LocationDao
     */
    private $locationDao;
    /**
     * Class instance
     *
     * @var GOOGLELOCATION_BOL_LocationService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->locationDao = GOOGLELOCATION_BOL_LocationDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return GOOGLELOCATION_BOL_LocationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function save( GOOGLELOCATION_BOL_Location $dto )
    {
        $this->locationDao->save($dto);
    }

    public function findByUserId( $userId )
    {
        return $this->locationDao->findByUserId($userId);
    }

    public function getSearchInnerJoinSql( $prefix, $southWestLat, $southWestLng, $northEastLat, $northEastLng, $countryCode = null, $joinType = 'INNER' )
    {
        return $this->locationDao->getSearchInnerJoinSql($prefix, $southWestLat, $southWestLng, $northEastLat, $northEastLng, $countryCode, $joinType );
    }

    public function findByUserIdList( array $userIdList )
    {
        $list = $this->locationDao->findByUserIdList($userIdList);

        $result = [];

        foreach ( $list as $location )
        {
            /* @var $location GOOGLELOCATION_BOL_Location */
            $result[$location->entityId] = $location;
        }

        return $result;
    }

    public function getAllLocationsForUserMap()
    {
        return $this->locationDao->getAllLocationsForUserMap();
    }

    public function getAllLocationsForEntityType( $entityType )
    {
        return $this->locationDao->getAllLocationsForEntityType($entityType);
    }

    public function getLanguageCode()
    {
        $tag = BOL_LanguageService::getInstance()->getCurrent()->getTag();

        $matches = array();
        preg_match("/^([a-zA-Z]{2})(-[a-zA-Z]{2})?.*$/", $tag, $matches);
        $language = 'en';

        if ( !empty($matches[1]) )
        {
            $language = mb_strtolower($matches[1]);
        }

        return $language;
    }

    public function findUserListByCoordinates( $lat, $lon, $first, $count, $userIdList = array() )
    {
        return $this->locationDao->findUserListByCoordinates($lat, $lon, $first, $count, $userIdList);
    }

    public function findUserCountByCoordinates( $lat, $lon, $userIdList = array() )
    {
        return $this->locationDao->findUserCountByCoordinates($lat, $lon, $userIdList);
    }

    public function getLocationName( $lat, $lon )
    {
        return $this->locationDao->getLocationName($lat, $lon);
    }

    public function findByEntityIdAndEntityType( $entityId, $entityType )
    {
        return $this->locationDao->findEntityIdAndEntityType($entityId, $entityType);
    }

    public function deleteByEntityIdAndEntityType( $entityId, $entityType )
    {
        return $this->locationDao->deleteByEntityIdAndEntityType($entityId, $entityType);
    }
    
    public function deleteByEntityType( $entityType )
    {
        return $this->locationDao->deleteByEntityType($entityType);
    }

    public function getPointList( $locationList )
    {
        $pointList = array();

        foreach ( $locationList as $location )
        {
            $entityId = $location['entityId'];

            if ( !isset($pointList[$location['lat'] . "_" . $location['lng']]['count']) )
            {
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['lat'] = $location['lat'];
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['lng'] = $location['lng'];
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['address'] = $location['address'];
                $pointList[$location['lat'] . "_" . $location['lng']]['count'] = 1;
                $pointList[$location['lat'] . "_" . $location['lng']]['entityIdList'][$entityId] = $entityId;
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['northEastLat'] = $location['northEastLat'];
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['northEastLng'] = $location['northEastLng'];
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['southWestLat'] = $location['southWestLat'];
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['southWestLng'] = $location['southWestLng'];
                $pointList[$location['lat'] . "_" . $location['lng']]['location']['json'] = $location['json'];
            }
            else
            {
                $pointList[$location['lat'] . "_" . $location['lng']]['entityIdList'][$entityId] = $entityId;
                $pointList[$location['lat'] . "_" . $location['lng']]['count']++;
            }
        }
        
        return $pointList;
    }

    public function getEntityListFromSession( $hash )
    {
        $list = OW::getSession()->get(self::SESSION_VAR_ENTITY_LIST);

        return !empty($list[$hash]['data']) && is_array($list[$hash]['data']) ? $list[$hash]['data'] : array();
    }
    
    public function getEntityTypeFromSession( $hash )
    {
        $list = OW::getSession()->get(self::SESSION_VAR_ENTITY_LIST);

        return !empty($list[$hash]['entityType']) ? $list[$hash]['entityType'] : null;
    }

    public function saveEntityListToSession( $entityIdList, $entityType = null )
    {
        $this->clearEntitylist();

        $list = OW::getSession()->get(self::SESSION_VAR_ENTITY_LIST);

        if ( empty($list) )
        {
            $list = array();
        }

        $hash = md5(json_encode($entityIdList));
        $list[$hash]['data'] = $entityIdList;
        $list[$hash]['entityType'] = $entityType;
        $list[$hash]['time'] = time();

        OW::getSession()->set(self::SESSION_VAR_ENTITY_LIST, $list);
        return $hash;
    }

    private function clearEntitylist()
    {
        $list = OW::getSession()->get(self::SESSION_VAR_ENTITY_LIST);

        if ( empty($list) )
        {
            return;
        }

        $timeLimit = 60 * 5;

        foreach ( $list as $key => $item )
        {
            if( ($item['time'] + $timeLimit) < time() )
            {
                unset($list[$key]);
            }
        }
    }
    
    public function getMapProvider()
    {
        $value = OW::getConfig()->getValue('googlelocation', 'map_provider');
        
        if ( !in_array( $value, array( self::PROVIDER_GOOGLE, self::PROVIDER_BING ) ) )
        {
            return self::PROVIDER_GOOGLE;
        }
        
        return $value;
    }
    
    public function getApiKey()
    {
        $apiKey = "";
        switch( $this->getMapProvider() )
        {
            case self::PROVIDER_GOOGLE :
                $apiKey = Ow::getConfig()->getValue('googlelocation', 'api_key');
                break;
            case self::PROVIDER_BING :
                $apiKey = Ow::getConfig()->getValue('googlelocation', 'bing_api_key');
                break;
        }
        
        return $apiKey;
    }

    public function isApiKeyExists()
    {
        $apiKey = GOOGLELOCATION_BOL_LocationService::getInstance()->getApiKey();

        if ( empty($apiKey) || mb_strlen(trim($apiKey)) == 0 )
        {
            return false;
        }

        return true;
    }
    
    /*
     * Return map component
     * @params mixed $params
     * @return GOOGLELOCATION_CMP_Map
     */

    public function getMapComponent($params = array())
    {
        switch( $this->getMapProvider() )
        {
            case self::PROVIDER_GOOGLE :
                $map = new GOOGLELOCATION_GOOGLE_CMP_Map($params);
                break;
            case self::PROVIDER_BING :
                $map = new GOOGLELOCATION_BING_CMP_Map($params);
                break;
        }
        
        return $map;
    }
    
    /*
     * Return map component
     * @params mixed $userIdList
     * $userIdList = array( 1, 2, 3 ) OR $userIdList = 'all'
     * @return GOOGLELOCATION_CMP_Map
     */

    public function getUserListMapCmp( $userIdList, $backUri = null )
    {
        /*@var $map GOOGLELOCATION_CMP_Map*/
        $map = $this->getMapComponent();
        $map->setHeight('600px');
        $map->setZoom(2);
        $map->setCenter(30, 10);
        $map->setMapOption('scrollwheel', 'true');
        $map->displaySearchInput(true);

        $locationObjectList = array();
        $hash = null;

        if ( !empty($userIdList) && is_array($userIdList) )
        {
            $locationObjectList = $this->findByUserIdList($userIdList);            
        }
        else if ( strtolower($userIdList) == 'all' )
        {
            $locationObjectList = $this->locationDao->getAllLocationsForUserMap();
        }
        
        $userList = array();
        $userLocationList = array();
        $locationList = array();

        foreach ( $locationObjectList as $location )
        {
            if ( $location instanceof OW_Entity )
            {
                $userList[$location->entityId] = $location->entityId;
                $locationList[] = get_object_vars($location);
            }

            if ( is_array($location) )
            {
                $userList[$location['entityId']] = $location['entityId'];
                $locationList[] = $location;
            }
        }

        $dtoList = BOL_UserService::getInstance()->findUserListByIdList($userList);

        $userDtoList = array();

        foreach( $dtoList as $userDto )
        {
            $userDtoList[$userDto->id] = $userDto;
        }

        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars($userList, true, true, true, false);
        
        $userUrlList = BOL_UserService::getInstance()->getUserUrlsForList($userList);
        $displayNameList = BOL_UserService::getInstance()->getDisplayNamesForList($userList);
        $displayedFields = $this->getUserFields($userList);
        
        $pointList = $this->getInstance()->getPointList($locationList);

        foreach ( $pointList as $point )
        {
            if ( !empty($point['entityIdList']) )
            {
                $content = "";

                if ( $point['count'] > 1 )
                {
                    $listCmp = OW::getClassInstance('GOOGLELOCATION_CMP_MapUserList', $point['entityIdList'], $point['location']['lat'], $point['location']['lng'], $backUri);
                    $content .= $listCmp->render();
                    unset($listCmp);
                }
                else
                {
                    $userId = current($point['entityIdList']);
                    $content = null;

                    if ( !empty($userDtoList[$userId]) )
                    {
                        $cmp = OW::getClassInstance('GOOGLELOCATION_CMP_MapItem');
                        $cmp->setAvatar($avatarList[$userId]);

                        $url = !empty($userUrlList[$userId]) ? $userUrlList[$userId] : '';
                        $displayName = !empty($displayNameList[$userId]) ? $displayNameList[$userId] : '';
                        $fields = !empty($displayedFields[$userId]) ? $displayedFields[$userId] : '';

                        $content = "<a href='{$url}'>" . $displayName . "</a>
                            <div>$fields</div>
                            <div>{$point['location']['address']}</div>";

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

    public function getMobileUserListMapCmp( $userIdList, $backUri = null )
    {
        $map = $this->getUserListMapCmp( $userIdList, $backUri = null );
        $map->setHeight('400px');
        $map->setMapOption('overviewMapControl', 'true');
        $map->setMapOption('panControl', 'true');
        $map->setMapOption('rotateControl', 'true');
        
        return $map;
    }
    
    private function getUserFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = '';

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid] = $sexValue . ' ' . $age;
            }
        }

        return $fields;
    }

    private function toRad($i)
    {
        return $i * pi() / 180;
    }

    private function toDeg($i)
    {
        return $i * 180 / pi();
    }

    public function getNewCoordinates( $lat, $lng, $heading, $distance )
    {
        if ($heading == 'sw')
        {
            $heading = 225;
        }
        else
        {
            $heading = 45;
        }

        $distance_unit = $this->getDistanseUnits();
        if ( $distance_unit == self::DISTANCE_UNITS_MILES )
        {
            $distance *= 1.609344;
        }
        $distance = $distance * 1000;
        $distance = sqrt((pow($distance, 2))*2);
        $distance = $distance / 6378137;
        $heading = $this->toRad($heading);
        $latitude = $this->toRad($lat);
        $longitude = $this->toRad($lng);
        $d = cos($distance);
        $f = sin($latitude);
        $latitude = cos($latitude);
        $g = $d * $f + $distance * $latitude * cos($heading);
        $coord = array();
        $coord['lat'] = $this->toDeg(asin($g));
        $coord['lng'] = $this->toDeg($longitude+atan2($distance*$latitude*sin($heading),$d-$f*$g));
        return $coord;
    }

    public function getDistanseUnits()
    {
        $unit = OW::getConfig()->getValue('googlelocation', 'distance_units');
        return in_array( $unit, array(self::DISTANCE_UNITS_MILES, self::DISTANCE_UNITS_KM) ) ? $unit : self::DISTANCE_UNITS_MILES;
    }
    
    public function setDistanseUnits( $value )
    {
        $unit = !empty($value) && in_array( $value, array(self::DISTANCE_UNITS_MILES, self::DISTANCE_UNITS_KM) ) ? $value : self::DISTANCE_UNITS_MILES;
        OW::getConfig()->saveConfig('googlelocation', 'distance_units', $unit);
    }
    
    public function getListOrderedByDistance( $userIdList, $first, $count, $lat, $lon )
    {
        return $this->locationDao->getListOrderedByDistance( $userIdList, $first, $count, $lat, $lon );
    }
    
    function distance($lat, $lon, $lat1, $lon1, $unit = null) 
    {
        $start = array($lat, $lon);
        $finish = array($lat1, $lon1);
        
        $theta = $start[1] - $finish[1];
        $distance = (sin(deg2rad($start[0])) * sin(deg2rad($finish[0]))) + (cos(deg2rad($start[0])) * cos(deg2rad($finish[0])) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;

        if ( empty($unit) )
        {
            $unit = $this->getDistanseUnits();
        }
        
        if ( $unit == self::DISTANCE_UNITS_KM )
        {
            $distance *= 1.609344;
        }
        
        return round($distance, 2);

    }
    
    public function getCountryRestriction()
    {
        $reuslt = null;
        
        $country = OW::getConfig()->getValue('googlelocation', 'country_restriction');
        if ( !empty($country) )
        {
            $reuslt = $country;
        }
        
        return $reuslt;
    }
    
    public function getDefaultMarkerIcon()
    {
        return OW::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl().'images/marker_icon.png';
    }

    public function checkApiKey()
    {
        $isAPiKeyValidConfig = OW::getConfig()->getValue('googlelocation', 'is_api_key_exists');

        if ( $this->isApiKeyExists() ) {
            if ( !$isAPiKeyValidConfig ) {
                $dbo = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

                if (!empty($dbo)) {
                    $dbo->onEdit = 1;
                    $dbo->onJoin = 1;
                    $dbo->onSearch = 1;
                    $dbo->onView = 1;

                    BOL_QuestionService::getInstance()->saveOrUpdateQuestion($dbo);
                }

                OW::getConfig()->saveConfig('googlelocation', 'is_api_key_exists', true);
            }
        }
        else {
            if ( $isAPiKeyValidConfig ) {
                $dbo = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

                if (!empty($dbo)) {
                    $dbo->onEdit = 0;
                    $dbo->onJoin = 0;
                    $dbo->onSearch = 0;
                    $dbo->onView = 0;

                    BOL_QuestionService::getInstance()->saveOrUpdateQuestion($dbo);
                }

                OW::getConfig()->saveConfig('googlelocation', 'is_api_key_exists', false);
            }
        }
    }

    public function enableQuestion()
    {
        $dbo = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

        if( !empty($dbo) ) {
            $dbo->onEdit = 1;
            $dbo->onJoin = 1;
            $dbo->onSearch = 1;
            $dbo->onView = 1;

            BOL_QuestionService::getInstance()->saveOrUpdateQuestion($dbo);
        }
    }

    public function isMapDisabledOnProfilePages() {
        return !OW::getConfig()->getValue('googlelocation', 'display_map_on_profile_pages');
    }
}