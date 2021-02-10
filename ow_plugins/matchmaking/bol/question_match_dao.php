<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Data Access Object for `matchmaking_question_match` table.
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.bol
 * @since 1.0
 */
class MATCHMAKING_BOL_QuestionMatchDao extends OW_BaseDao
{    
    /**
     * Singleton instance.
     *
     * @var MATCHMAKING_BOL_QuestionMatchDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MATCHMAKING_BOL_QuestionMatchDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'MATCHMAKING_BOL_QuestionMatch';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'matchmaking_question_match';
    }

    /**
     * @return mixed|null|string
     */
    public function getMaxPercentValue()
    {
        $query = "SELECT SUM(`coefficient`) FROM " . $this->getTableName() . " WHERE 1";

        return $this->dbo->queryForColumn($query);
    }

    /**
     * @return array|mixed
     */
    public function findRequiredMatchFields()
    {
        $example = new OW_Example();
        $example->andFieldEqual('required', 1);

        return $this->findListByExample($example);
    }

    /**
     * @return array|mixed
     */
    public function findFieldsExceptRequired()
    {
        $example = new OW_Example();
//        $example->andFieldEqual('required', 0);

        return $this->findListByExample($example);
    }

    /**
     * @param $userId
     * @return mixed|null|string
     */
    public function findMatchCount( $userId )
    {
        $params = $this->getQueryParamsForCountMatches($userId);

        if (empty($params))
        {
            return 0;
        }

        $query = $this->prepareQuerySelectCount($params);

        return $this->dbo->queryForColumn($query);
    }

    /**
     * @param $userId
     * @param $sortOrder
     * @param $first
     * @param $count
     * @return array
     */
    public function findUserIdMatchListByQuestionValues( $userId, $sortOrder, $first, $count )
    {
        $params = $this->getQueryParams($userId, $sortOrder);
        
        $event = new OW_Event('matchmaking.user_id_match_list_by_question_values',
            array('userId' => $userId, 'sortOrder' => $sortOrder, 'first' => $first, 'count' => $count),
            $params
        );

        $trigger = OW::getEventManager()->trigger($event);
        $params = $trigger->getData();

        if (empty($params))
        {
            return array();
        }

        $query = $this->prepareQuerySelect($params);

        return $this->dbo->queryForList($query, array_merge(array('first' => $first, 'count' => $count)));
    }

    /**
     * @param $params
     * @return string
     */
    private function prepareQuerySelectCount( $params )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('user', "id", array(
            "method" => "MATCHMAKING_BOL_QuestionMatchDao::prepareQuerySelectCount"
        ));
        
        $sql = "SELECT COUNT( DISTINCT `user`.id ) 
            FROM {$this->getUsersTableNameWithLimits()} `user`
        " . $queryParts['join'] . $params['join'] . "

        WHERE " . $queryParts['where'] ." AND `user`.`id`<>" . $params['userId'] . " " . $params['where'] . " AND  " . $params['compatibility'] . " > 0 ";

        return $sql;
    }

    /**
     * @param $params
     * @return string
     */
    private function prepareQuerySelect( $params )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('user', "id", array(
            "method" => "MATCHMAKING_BOL_QuestionMatchDao::prepareQuerySelect"
        ));
        
        $sql = " SELECT DISTINCT `user`.id, " . $params['compatibility'] . " as `compatibility` 
        
        FROM {$this->getUsersTableNameWithLimits()} `user`
            
        " . $queryParts['join'] . $params['join'] . "
            
        WHERE (" . $params['compatibility'] . ") <> 0 AND " . $queryParts['where'] ." AND `user`.`id`<>" . $params['userId'] . " " . $params['where'] . "
        ORDER BY " . $params['order'] . " `user`.`activityStamp` DESC
        LIMIT :first, :count ";
        
        return $sql;
    }

    /**
     * @param $params
     * @return string
     */
    private function prepareQuerySelectForUserIdList( $params )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('user', "id", array(
            "method" => "MATCHMAKING_BOL_QuestionMatchDao::prepareQuerySelect"
        ));
        
        $table = $this->getUsersTableNameWithLimits();
        
        if( !empty($params['userIdList']) )
        {
            $table = "`".BOL_UserDao::getInstance()->getTableName()."`";
        }
        
        $sql = " SELECT DISTINCT `user`.`id` as userId, " . $params['compatibility'] . " as `compatibility` 
            
        FROM {$table} `user`
            
        " . $queryParts['join'] . $params['join'] . "

        WHERE " . $queryParts['where'] ." AND `user`.`id`<>" . $params['userId'] . " " . $params['where'] . " 
        ORDER BY " . $params['order'] . " `user`.`activityStamp` DESC
        LIMIT :first, :count ";

        return $sql;
    }

    protected function getUsersTableNameWithLimits()
    {
        $usersTableName = "`".BOL_UserDao::getInstance()->getTableName()."`";
        
        if ( OW_SQL_LIMIT_USERS_COUNT > 0 )
        {
            $orderFieldname = BOL_UserDao::ACTIVITY_STAMP;
            $usersTableName = "( SELECT * FROM {$usersTableName} ORDER BY `{$orderFieldname}` DESC LIMIT " . OW_SQL_LIMIT_USERS_COUNT . " )";
        }
        
        return $usersTableName;
    }

        /**
     * @param $userId
     * @return array
     */
    private function getQueryParamsForCountMatches( $userId )
    {
        return $this->getQueryParamsForUserIdList($userId, array(),null, true);
    }
    
    /**
     * @param $userId
     * @param string $sortOrder
     * @return array
     */
    private function getQueryParams( $userId, $sortOrder = 'newest', $countMatches = false )
    {
        return $this->getQueryParamsForUserIdList($userId, array(),$sortOrder, $countMatches);
    }

    private function getQueryParamsForUserIdList( $userId, $userIdList, $sortOrder = 'newest', $countMatches = false )
    {
        $questionService = BOL_QuestionService::getInstance();
        $userService = BOL_UserService::getInstance();
        
        $matchFields = array();
        
        if ( $countMatches && empty($userIdList) )
        {
            $matchFields = $this->findRequiredMatchFields();
        }
        else
        {
            $matchFields = $this->findAll();
        }

        $prefix = 'qd';
        $counter = 0;
        $innerJoin = '';
        $leftJoin = '';
        $where = '';
        $compatibility = ' 0 +';
        $requiredCompatibility = ' 1 *';
        $location = null;
        $distance = MATCHMAKING_BOL_Service::getInstance()->getDistance($userId);
        
        if ( OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserId($userId);
            if ( $location ) {
                $location = get_object_vars($location);
            }
        }

        foreach ( $matchFields as $field )
        {
            $joinType = " INNER ";

            if ( !$countMatches )
            {
                $joinType = " LEFT ";
            }
            
            $question = $questionService->findQuestionByName($field->questionName);
            if (!$question)
            {
                continue;
            }
            
            // add location
            if ( $field->questionName == 'googlemap_location' && !empty($location) )
            {
                $locationSqlData = $this->getLocationSql($field, $location, $distance, $joinType);
                $leftJoin .= $locationSqlData['join'];
                $compatibility .= $locationSqlData['compatibility'];
                continue;
            }
            
            $matchQuestion = $questionService->findQuestionByName($field->matchQuestionName);
            if (!$matchQuestion)
            {
                continue;
            }
            $checkData = $questionService->getQuestionData(array($userId), array($field->matchQuestionName));

            if ( empty($checkData[$userId][$field->matchQuestionName]) )
            {
                if ($field->required)
                {
                    return array();
                }
            }

            $value1 = null;

            if (isset($checkData[$userId][$field->matchQuestionName]))
            {
                $value1 = $checkData[$userId][$field->matchQuestionName];
            }
            
            if ( !empty($value1) )
            {
                $tableAlias = $prefix . $counter;

                if ( $field->required )
                {
                    // calculate compatibility for required fields
                    $questionString = $this->prepareQuestionWhere($question, $value1, $tableAlias);

                    if ( !empty($questionString) )
                    {                             
                        $innerJoin .= $joinType . "JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $tableAlias . "`
                        ON ( `user`.`id` = `" . $tableAlias . "`.`userId` AND `" . $tableAlias . "`.`questionName` = '" . $this->dbo->escapeString($question->name) . "' AND " . $questionString . " ) ";

                        $compatibility .= ' IF ( `' . $tableAlias . '`.`questionName` IS NOT NULL, ' . $field->coefficient . ', 0 ) +';
                        // if users don't match by required field than compatibility = 0
                        $requiredCompatibility .=  ' IF ( `' . $tableAlias . '`.`id` IS NOT NULL, 1, 0 ) *';

                        $counter++;
                    }

                    $checkData2 = $questionService->getQuestionData(array($userId), array($field->questionName));

                    if ( empty($checkData2[$userId][$field->questionName]) )
                    {
                        continue;
                    }
                    $value2 = $checkData2[$userId][$field->questionName];
                    $tableAlias2 = $prefix . $counter;
                    $questionString = $this->prepareQuestionWhere($matchQuestion, $value2, $tableAlias2);
                    if ( !empty($questionString) )
                    {
                        $innerJoin .= "
                            ".$joinType." JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $tableAlias2 . "`
                        ON ( `user`.`id` = `" . $tableAlias2 . "`.`userId` AND `" . $tableAlias2 . "`.`questionName` = '" . $this->dbo->escapeString($matchQuestion->name) . "' AND " . $questionString . " ) ";

                        // if users don't match by required field than compatibility = 0
                        $requiredCompatibility .=  ' IF( `' . $tableAlias2 . '`.`questionName` IS NOT NULL, 1, 0 ) *';
                        
                        $counter++;
                    }
                }
                else
                {
                    if ( $question->name == 'birthdate' )
                    {
                        // calculate compatibility for not required field birthdate
                        $checkData2 = $questionService->getQuestionData(array($userId), array($field->questionName));

                        if ( empty($checkData2[$userId][$field->questionName]) )
                        {
                            continue;
                        }

                        $tableAlias2 = $prefix . ($counter + 1);
                        $value2 = $checkData2[$userId][$field->questionName];
                        $questionString = $this->prepareQuestionWhere($question, $value1, $tableAlias);
                        $questionString2 = $this->prepareQuestionWhere($matchQuestion, $value2, $tableAlias2);

                        if ( !empty($questionString) && !empty($questionString2) )
                        {
                            $leftJoin .= "
                            ".$joinType." JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $tableAlias
                                . "` ON ( `user`.`id` = `" . $tableAlias . "`.`userId` AND `" . $tableAlias . "`.`questionName` = '" . $this->dbo->escapeString($question->name) . "' AND " . $questionString . " ) ";

                            $innerJoin .= "
                            ".$joinType." JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $tableAlias2
                                . "` ON ( `user`.`id` = `" . $tableAlias2 . "`.`userId` AND `" . $tableAlias2 . "`.`questionName` = '" . $this->dbo->escapeString($matchQuestion->name) . "' AND " . $questionString2 . " ) ";

//                            // if users don't match by required field than compatibility = 0
                            $compatibility .= ' IF( `' . $tableAlias . '`.`questionName` IS NOT NULL AND  `' . $tableAlias2 . '`.`questionName` IS NOT NULL, ' . $field->coefficient . ', 0 ) +';
                            $counter = $counter + 2;
                        }
                    }
                    else
                    {
                        // calculate compatibility for not required fields
                        $questionString = $this->prepareQuestionWhere($question, $value1, $tableAlias);

                        if ( !empty($questionString) )
                        {
                            $leftJoin .= "
                            ".$joinType." JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $tableAlias
                                . "` ON ( `user`.`id` = `" . $tableAlias . "`.`userId` AND `" . $tableAlias . "`.`questionName` = '" . $this->dbo->escapeString($question->name) . "' AND " . $questionString . " ) ";

                            $compatibility .= ' IF( `' . $tableAlias . '`.`questionName` IS NOT NULL, ' . $field->coefficient . ', 0 ) +';
                            $counter++;
                        }
                    }
                }
            }
            else
            {
                $compatibility .= ' ' . $field->coefficient . ' +';
            }
        }

        /**/

        $order = "";
        if ( $countMatches == false )
        {
            if ( $sortOrder == 'newest' )
            {
                $order = ' `user`.`joinStamp` DESC, ';
            }
            else if ( $sortOrder == 'mail' )
            {
                $order = ' `user`.`joinStamp` DESC, ';

                $matchmaking_lastmatch_userid = BOL_PreferenceService::getInstance()->getPreferenceValue('matchmaking_lastmatch_userid', $userId );

                $where = ' AND `user`.`id` > '.$matchmaking_lastmatch_userid;
            }
            else if ( $sortOrder == 'compatible' )
            {
                //$order = '(' . substr($compatibility, 0, -1) . ') DESC , ';
                $order = ' `compatibility` DESC , ';
            }
        }
        
        if ( !empty($userIdList) )
        {
            $listOfUserIds = $this->dbo->mergeInClause($userIdList);
            $where .= ' AND `user`.`id` IN ( '.$listOfUserIds.' ) ';
        }

        $result = array(
            'userId' => $userId,
            'join' => $innerJoin . $leftJoin,
            'where' => $where,
            'order' => $order,
            'userIdList' => $userIdList,
            'compatibility' => ' (' . substr($compatibility, 0, -1) . ') * (' . substr($requiredCompatibility, 0, -1) . ') '
        );
        
        if ( !empty($userIdList) )
        {
            $result['userIdList'] = $userIdList;
        }

        return $result;
    }
    
    private function getLocationSql($field, $location, $distanse, $joinType = 'INNER')
    {
        $result = array(
            'join' => "",
            'where' => "",
            'compatibility' => ""
        );
        
        if ( OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            $json = !empty($location['json']) ? json_decode($location['json'], true) : array();

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

            if ( !empty($distanse) && (float) $distanse > 0 )
            {
                $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($location['southWestLat'], $location['southWestLng'], 'sw', (float) $distanse);
                $location['southWestLat'] = $coord['lat'];
                $location['southWestLng'] = $coord['lng'];

                $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($location['northEastLat'], $location['northEastLng'], 'ne', (float) $distanse);
                $location['northEastLat'] = $coord['lat'];
                $location['northEastLng'] = $coord['lng'];
            }

            $sql = GOOGLELOCATION_BOL_LocationService::getInstance()->getSearchInnerJoinSql('user', $location['southWestLat'], $location['southWestLng'], $location['northEastLat'], $location['northEastLng'], $countryCode, $joinType);

            $result["join"] = " \n ".$sql;
            $result["compatibility"] = ' IF( `location`.`id` IS NOT NULL, ' . $field->coefficient . ', 0 ) +';
        }
        
        return $result;
    }
    
    /**
     * @param BOL_Question $question
     * @param $value
     * @param string $prefix
     * @return array|string
     */
    private function prepareQuestionWhere( BOL_Question $question, $value, $prefix = '' )
    {
        $result = '';
        $prefix = $this->dbo->escapeString($prefix);

        $event = new OW_Event('matchmaking.on_prepare_question_where', array(), array($question));

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if( empty($data) )
        {
            return;
        }

        switch ( $question->presentation )
        {
            case BOL_QuestionService::QUESTION_PRESENTATION_URL :
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXT :
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA :
                if ( $question->base )
                {
                    $result = ' `user`.`' . $this->dbo->escapeString($question->name) . '` LIKE \'' . $this->dbo->escapeString($value) . '%\'';
                }
                else
                {
                    $result = " LCASE(`" . $prefix . "`.`textValue`) LIKE '" . $this->dbo->escapeString(strtolower($value)) . "%'";
                }
                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX :
                if ( $question->base )
                {
                    $result = ' `user`.`' . $this->dbo->escapeString($question->name) . '` = ' . (boolean) $value;
                }
                else
                {
                    $result = " `" . $prefix . "`.`intValue` = " . (boolean) $value;
                }
                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX :
                if ( $question->base )
                {
                    $result = ' `user`.`' . $this->dbo->escapeString($question->name) . '` & ' . $value . ' ';
                }
                else
                {
                    $result = ' `' . $this->dbo->escapeString($prefix) . '`.`intValue` & ' . $value . ' ';
                }

                break;


            case BOL_QuestionService::QUESTION_PRESENTATION_RADIO :
            case BOL_QuestionService::QUESTION_PRESENTATION_SELECT :
                $questionValues = BOL_QuestionService::getInstance()->findQuestionValues($question->name);

                if ( !empty($questionValues) )
                {
                    $result = array();
                    foreach ( $questionValues as $val )
                    {
                        if ( (bool)($val->value & (int)$value) )
                        {
                            $result[] = $val->value;
                        }
                    }
                    $value = $result;
                }

                if ( isset($value) && is_array($value) && !empty($value) )
                {
                    if ( $question->base )
                    {
                        $result = ' `user`.`' . $this->dbo->escapeString($question->name) . '` IN ( ' . $this->dbo->mergeInClause($value) . ' ) ';
                    }
                    else
                    {
                        $result = ' `' . $this->dbo->escapeString($prefix) . '`.`intValue` IN ( ' . $this->dbo->mergeInClause($value) . ' ) ';
                    }
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_DATE :
            case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :

                $value = explode('-', $value);
                $value = array('from' => $value[0], 'to' => $value[1]);

                if ( isset($value['from']) && isset($value['to']) )
                {
                    $maxDate = date('Y-m-d', mktime(23, 59, 59, 12, 31, ( date('Y') - (int) $value['from'] )));
                    $minDate = date('Y-m-d', mktime(0, 0, 0, 1, 1, ( date('Y') - (int) $value['to'] )));

                    if ( $question->base )
                    {
                        $result = " `user`.`" . $this->dbo->escapeString($question->name) . "` BETWEEN  '" . $this->dbo->escapeString($minDate) . "' AND '" . $this->dbo->escapeString($maxDate) . "'";
                    }
                    else
                    {
                        $result = " `" . $prefix . "`.`dateValue` BETWEEN  '" . $this->dbo->escapeString($minDate) . "' AND '" . $this->dbo->escapeString($maxDate) . "'";
                    }
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_AGE :

                $value = explode('-', $value);
                $value = array('from' => $value[0], 'to' => $value[1]);

                if ( isset($value['from']) && isset($value['to']) )
                {
                    if ( $question->base )
                    {
                        $result = " TIMESTAMPDIFF(YEAR, `user`.`" . $this->dbo->escapeString($question->name) . "`, CURDATE()) >= " . $this->dbo->escapeString((int) $value['from']) . " AND TIMESTAMPDIFF(YEAR,`user`.`" . $this->dbo->escapeString($question->name) . "`, CURDATE()) <= " . $this->dbo->escapeString((int) $value['to']) . " ";
                    }
                    else
                    {
                        $result = " TIMESTAMPDIFF(YEAR, `" . $prefix . "`.`dateValue`, CURDATE()) >= " . $this->dbo->escapeString((int) $value['from']) . " AND TIMESTAMPDIFF(YEAR,`" . $prefix . "`.`dateValue`, CURDATE()) <= " . $this->dbo->escapeString((int) $value['to']) . " ";
                    }
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_RANGE:
                $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $value = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                if ( $question->base )
                {
                    $result = " " . $value . " BETWEEN SUBSTRING(`user`.`" . $this->dbo->escapeString($question->name) . "`, 1, LOCATE('-', `user`.`" . $this->dbo->escapeString($question->name) . "`)-1 ) AND SUBSTRING(`user`.`" . $this->dbo->escapeString($question->name) . "`, LOCATE('-', `user`.`" . $this->dbo->escapeString($question->name) . "`)+1 ) ";
                }
                else
                {
                    $result = " " . $value . " BETWEEN SUBSTRING(`" . $prefix . "`.`textValue`, 1, LOCATE('-', `" . $prefix . "`.`textValue`)-1 ) AND SUBSTRING(`" . $prefix . "`.`textValue`, LOCATE('-', `" . $prefix . "`.`textValue`)+1 )  ";
                }
                break;
        }

        return $result;
    }

    public function findRuleByQuestionName( $questionName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionName', $questionName);

        return $this->findObjectByExample($example);
    }

    public function findMatchQuestionsForUser( $userId )
    {
        if ( $userId === null )
        {
            return array();
        }

        if (!OW::getPluginManager()->isPluginActive('skadate'))
        {
            return array();
        }

        $genderAccTypes = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        $lookingForValue = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array('match_sex'));
        $lookingForValues = array();
        foreach($genderAccTypes as $type)
        {
            if ($lookingForValue[$userId]['match_sex'] & $type->genderValue)
            {
                $lookingForValues[] = $type->genderValue;
            }
        }

        if (empty($lookingForValues))
        {
            return array();
        }

        $locationWhere = "";
        $locationJoin = "";
        $lookingForValues = $this->dbo->mergeInClause($lookingForValues);
        
        if ( OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            $locationWhere = " OR  ( `question`.name = 'googlemap_location' AND `atg1`.`genderValue` IN ( {$lookingForValues} ) AND match.id IS NOT NULL ) ";
            $locationJoin = " LEFT JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta1` ON ( `question`.`name` = `qta1`.`questionName` )
                     LEFT JOIN `". SKADATE_BOL_AccountTypeToGenderDao::getInstance()->getTableName() ."` as `atg1` ON ( `qta1`.`accountType` = `atg1`.`accountType` )
                     LEFT JOIN `". MATCHMAKING_BOL_QuestionMatchDao::getInstance()->getTableName() ."` as `match` ON ( `match`.`questionName` = `question`.`name` ) ";
        }
        
        $sql = " SELECT DISTINCT `question`.* FROM `" . BOL_QuestionDao::getInstance()->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    LEFT JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON ( `question`.`parent` = `qta`.`questionName` )
                    LEFT JOIN `". SKADATE_BOL_AccountTypeToGenderDao::getInstance()->getTableName() ."` as `atg` ON ( `qta`.`accountType` = `atg`.`accountType` )
                        
                    {$locationJoin}
                    
                    WHERE ( `question`.`sectionName`='about_my_match' AND `atg`.`genderValue` IN ( {$lookingForValues} ) ) {$locationWhere} 

                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql);
    }

    public function findCompatibilityByUserIdList($userId, $userIdList, $first, $count, $sortOrder)
    {
        $params = $this->getQueryParamsForUserIdList($userId, $userIdList, $sortOrder);

        $list = array();
        
        if ( !empty($params['compatibility']) )
        {
            $query = $this->prepareQuerySelectForUserIdList($params);

            $list = $this->dbo->queryForList($query, array_merge(array('first' => $first, 'count' => $count)));
            $maxValue = $this->getMaxPercentValue();

            foreach($list as $key => $value)
            {
                $list[$key]['compatibility'] = ceil(($list[$key]['compatibility'] * 100)/$maxValue);
            }
        }

        return $list;
    }

    public function countActiveUsers()
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("users", "id", array(
            "method" => "MATCHMAKING_BOL_QuestionMatchDao::countActiveUsers"
        ));

        $query = "SELECT COUNT(*) FROM `" . BOL_UserDao::getInstance()->getTableName() ."` as `users`
                        {$queryParts["join"]}
                        WHERE " . $queryParts["where"] . " AND `users`.`emailVerify` = 1 ";
        return $this->dbo->queryForColumn($query);
    }

public function findActiveUsersList( $first, $count )
    {

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("users", "id", array(
            "method" => "MATCHMAKING_BOL_QuestionMatchDao::findActiveUsersList"
        ));

        $query = "SELECT `users`.* FROM `" . BOL_UserDao::getInstance()->getTableName() ."` as `users`
                        {$queryParts["join"]}
                        WHERE " . $queryParts["where"] . "  AND  `users`.`emailVerify` = 1 ORDER BY `users`.`joinStamp` DESC LIMIT ?,? ";
        return $this->dbo->queryForObjectList($query, BOL_UserDao::getInstance()->getDtoClassName(), array($first, $count));
    }
}
