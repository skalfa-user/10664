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
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.bol
 * @since 1.0
 */
class MATCHMAKING_BOL_Service
{
    const MAX_COEFFICIENT = 5;

    private $questionMatchDao;
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MATCHMAKING_BOL_Service
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
        $this->questionMatchDao = MATCHMAKING_BOL_QuestionMatchDao::getInstance();
    }

    /**
     * @param $id
     * @return int
     */
    public function delete( $id )
    {
        $rule = $this->findById($id);

        if (!$rule->required && $rule->questionName != $rule->matchQuestionName)
        {
            $matchQuestion = BOL_QuestionService::getInstance()->findQuestionByName($rule->matchQuestionName);

            if ( $matchQuestion->name == 'match_age' )
            {
                $matchQuestion->removable = 1;
                BOL_QuestionService::getInstance()->saveOrUpdateQuestion($matchQuestion);
            }
            else
            {
                BOL_QuestionService::getInstance()->deleteQuestion(array($matchQuestion->id));
            }
        }

        return $this->deleteRule($id);
    }

    public function deleteRule( $id )
    {
        return $this->questionMatchDao->deleteById($id);
    }

    public function deleteRuleByQuestionName( $questionName )
    {
        $rule = $this->questionMatchDao->findRuleByQuestionName($questionName);

        if (empty($rule))
        {
            return;
        }

        $this->deleteRule($rule->getId());
    }

    /**
     * @param $value
     * @return float
     */
    public function getCompatibilityByValue( $value )
    {
        $maxPercentValue = $this->questionMatchDao->getMaxPercentValue();
        return ceil(($value * 100) / $maxPercentValue);
    }

    /**
     * @param $firstUserId
     * @param $secondUserId
     * @return float|int
     */
    public function getCompatibility( $firstUserId, $secondUserId )
    {
        $questionService = BOL_QuestionService::getInstance();
        $compatibility = 0;
        $userIdList = array($firstUserId, $secondUserId);

        $fieldsList = $this->questionMatchDao->findAll();

        $allFields = array();
        foreach ( $fieldsList as $field )
        {
            $allFields[] = $field->questionName;
            $allFields[] = $field->matchQuestionName;
        }

        if ( empty($allFields) )
        {
            return 0;
        }

        $questionData = $questionService->getQuestionData($userIdList, $allFields);

        /**
         * @var $field MATCHMAKING_BOL_QuestionMatch
         */
        foreach ( $fieldsList as $field )
        {
            $question = $questionService->findQuestionByName($field->questionName);
            $match_question = $questionService->findQuestionByName($field->matchQuestionName);

            if ( $field->required )
            {
                switch ( $question->presentation )
                {
                    case BOL_QuestionService::QUESTION_PRESENTATION_SELECT:

                        if ( empty($questionData[$firstUserId]['match_sex']) && empty($questionData[$secondUserId]['sex']) )
                        {
                            $compatibility += self::MAX_COEFFICIENT;
                        }
                        else if ( isset($questionData[$firstUserId]['match_sex']) && isset($questionData[$secondUserId]['sex']) && ($questionData[$firstUserId]['match_sex'] & $questionData[$secondUserId]['sex'])
                            && isset($questionData[$secondUserId]['match_sex']) && isset($questionData[$firstUserId]['sex']) && ($questionData[$secondUserId]['match_sex'] & $questionData[$firstUserId]['sex']) )
                        {
                            $compatibility += $field->coefficient;
                        }
                        else
                        {
                            return 0;
                        }

                        break;
                    case BOL_QuestionService::QUESTION_PRESENTATION_DATE:
                    case BOL_QuestionService::QUESTION_PRESENTATION_AGE:
                    case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :

                        if ( empty($questionData[$firstUserId]['birthdate']) || empty($questionData[$secondUserId]['birthdate']) )
                        {
                            return 0;
                        }

                        $date = UTIL_DateTime::parseDate($questionData[$firstUserId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                        $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);

                        $date = UTIL_DateTime::parseDate($questionData[$secondUserId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                        $userAge = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);

                        if ( empty($questionData[$secondUserId]['match_age']) || empty($questionData[$firstUserId]['match_age']) )
                        {
                            return 0;
                        }

                        $matchAge = explode("-", $questionData[$firstUserId]['match_age']);
                        $userMatchAge = explode("-", $questionData[$secondUserId]['match_age']);

                        if ( $userAge >= $matchAge[0] && $userAge <= $matchAge[1] )
                        {
                            if ( $age >= $userMatchAge[0] && $age <= $userMatchAge[1] )
                            {
                                $compatibility += $field->coefficient;
                                //$compatibility += self::MAX_COEFFICIENT;
                            }
                            else
                            {
                                return 0;
                            }
                        }
                        else
                        {
                            return 0;
                        }

                        break;
                }
            }
            else if ( !empty($match_question) && !empty($question) &&
                isset($questionData[$firstUserId][$match_question->name]) && isset($questionData[$secondUserId][$question->name]) )
            {
                $userLocation = $questionData[$firstUserId][$match_question->name];
                $matchLocation = $questionData[$secondUserId][$question->name];

                $distanse = MATCHMAKING_BOL_Service::getInstance()->getDistance(OW::getUser()->getId());

                if ( $question->name == 'googlemap_location' )
                {
                    if ( !empty($distanse) && (float) $distanse > 0 )
                    {
                        $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($userLocation['southWestLat'], $userLocation['southWestLng'], 'sw', (float) $distanse);
                        $userLocation['southWestLat'] = $coord['lat'];
                        $userLocation['southWestLng'] = $coord['lng'];

                        $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($userLocation['northEastLat'], $userLocation['northEastLng'], 'ne', (float) $distanse);
                        $userLocation['northEastLat'] = $coord['lat'];
                        $userLocation['northEastLng'] = $coord['lng'];
                    }

                    if ( $this->isCompatibleLocation($userLocation, $matchLocation) )
                    {
                        $compatibility += $field->coefficient;
                    }
                }
                else if ( $question->name == 'birthdate' )
                {
                    $date = UTIL_DateTime::parseDate($questionData[$firstUserId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                    $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);

                    $date = UTIL_DateTime::parseDate($questionData[$secondUserId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                    $userAge = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);

                    if ( !empty($questionData[$secondUserId]['match_age']) && !empty($questionData[$firstUserId]['match_age']) )
                    {
                        $matchAge = explode("-", $questionData[$firstUserId]['match_age']);
                        $userMatchAge = explode("-", $questionData[$secondUserId]['match_age']);

                        if ( $userAge >= $matchAge[0] && $userAge <= $matchAge[1] )
                        {
                            if ( $age >= $userMatchAge[0] && $age <= $userMatchAge[1] )
                            {
                                $compatibility += $field->coefficient;
                            }
                        }
                    }
                }
                else if ( (bool) ((int) $questionData[$firstUserId][$match_question->name] & (int) $questionData[$secondUserId][$question->name]) )
                {
                    $compatibility += $field->coefficient;
                }
                else if ( empty($questionData[$firstUserId][$match_question->name]) )
                {
                    $compatibility += $field->coefficient;
                }
            }
            else
            {
                if ( !empty($match_question) && empty($questionData[$firstUserId][$match_question->name]) )
                {
                    $compatibility += $field->coefficient;
                }
            }
        }

        return $this->getCompatibilityByValue($compatibility);
    }

    /**
     * @param $userId
     * @return bool
     */
    public function sendNewMatchesForUser( $userId )
    {
        $userService = BOL_UserService::getInstance();

        $user = $userService->findUserById($userId);

        $massMailingSubscribe = BOL_PreferenceService::getInstance()
            ->getPreferenceValue('mass_mailing_subscribe', $user->id);

        if ( empty($user) || $massMailingSubscribe !== true)
        {
            return false;
        }

        $event = new OW_Event('matchmaking.send_matches_count',  array(), 10);
        $trigger = OW::getEventManager()->trigger($event);
        $count = $trigger->getData();

        $matchList = $this->questionMatchDao->findUserIdMatchListByQuestionValues($userId, 'mail', 0, $count);

        if ( !empty($matchList) )
        {
            $cmp = OW::getClassInstance('MATCHMAKING_CMP_Notification', $user, $matchList);
            $cmp->sendNotification();
        }
    }

    public function findAll()
    {
        return $this->questionMatchDao->findAll();
    }

    public function findFieldsExceptRequired()
    {
        return $this->questionMatchDao->findFieldsExceptRequired();
    }

    /**
     * @param $ruleId
     * @return MATCHMAKING_BOL_QuestionMatch
     */
    public function findById( $ruleId )
    {
        return $this->questionMatchDao->findById($ruleId);
    }

    /**
     * @param MATCHMAKING_BOL_QuestionMatch $questionMatch
     */
    public function saveQuestionMatch( $questionMatch )
    {
        $this->questionMatchDao->save($questionMatch);
    }

    /**
     * @param $userId
     * @param $first
     * @param $count
     * @param $sortOrder
     * @return array
     */
    public function findMatchList( $userId, $first, $count, $sortOrder = 'newest' )
    {
        $matchList =  $this->questionMatchDao->findUserIdMatchListByQuestionValues($userId, $sortOrder, $first, $count);

        $eventParams = [
            'userId' => $userId,
            'first' => $first,
            'count' => $count
        ];

        $event = OW::getEventManager()->trigger(new OW_Event('matchmaking.on_after_match_list', $eventParams, $matchList));

        return $event->getData();
    }

    public function findCompatibilityByUserIdList( $userId, $userIdList, $first, $count, $sortOrder = 'compatible' )
    {
        return $this->questionMatchDao->findCompatibilityByUserIdList($userId, $userIdList, $first, $count, $sortOrder);
    }

    public function getMaxPercentValue()
    {
        return $this->questionMatchDao->getMaxPercentValue();
    }

    /**
     * @param $userId
     * @return mixed|null|string
     */
    public function findMatchCount( $userId )
    {
        $eventParams = [
            'userId' => $userId,
        ];

        $matchCount =  $this->questionMatchDao->findMatchCount($userId);

        $event = OW::getEventManager()->trigger(new OW_Event('matchmaking.on_after_match_count', $eventParams, $matchCount));

        return $event->getData();
    }

    /**
     * Get match page menu items
     */
    public function getMatchPageMenuItems()
    {
        $menu = [
            'newest' => [
                'label' => OW::getLanguage()->text('matchmaking', 'newest_first'),
                'url' => OW::getRouter()->urlForRoute('matchmaking_members_page_sorted', array('sortOrder'=>'newest')),
                'isActive' => false
            ],
            'compatible' => [
                'label' => OW::getLanguage()->text('matchmaking', 'most_compatible_first'),
                'url' => OW::getRouter()->urlForRoute('matchmaking_members_page_sorted', array('sortOrder'=>'compatible')),
                'isActive' => false
            ]
        ];

        $event = new OW_Event('matchmaking:menu', [], $menu);
        OW::getEventManager()->trigger($event);
        $newMenuItems = $event->getData();

        return $newMenuItems;
    }

    /**
     * @param $userId
     * @param $first
     * @param $count
     * @return array
     */
    public function getUserListData( $userId, $first, $count )
    {
        return array(
            $this->findMatchList($userId, $first, $count),
            $this->findMatchCount($userId)
        );
    }

    public function findQuestionsForNewRules()
    {
        $existingRules = array();
        $matchQuestions = $this->findFieldsExceptRequired();
        foreach ($matchQuestions as $rule)
        {
            $existingRules[] = $rule->questionName;
        }

        $questionDtoList = BOL_QuestionService::getInstance()->findAllQuestions();

        $questionList = array();
        foreach ( $questionDtoList as $id => $question )
        {
            if(in_array($question->name, $existingRules))
            {
                continue;
            }

            if ( $question->name == 'sex' || $question->name == 'match_sex' || $question->name == 'match_age' || $question->name == 'password' )
            {
                continue;
            }

            if (!empty($question->parent) && $question->name != 'googlemap_location')
            {
                continue;
            }

            if ( $question->presentation != 'age' && $question->presentation != 'multicheckbox' && $question->presentation != 'radio' && $question->presentation != 'select' && $question->name != 'googlemap_location' )
            {
                continue;
            }

            if ( $question->presentation == 'age' && $question->name != 'birthdate' )
            {
                continue;
            }

            $questionList[$question->name] = $question;
        }

        return $questionList;
    }


    /**
     * @param $userIdList
     * @return array
     */
    public function getFieldsForMatchList( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

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

    public function getFieldsForEmail( $userId )
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

        $qLocation = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

        if ( !empty($qLocation) && $qLocation->onView )
        {
            $qs[] = 'googlemap_location';
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData(array($userId), $qs);

        $question = $questionList[$userId];

        if ( !empty($question['birthdate']) )
        {
            $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

            $fields['age'] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
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
                $fields['sex'] = substr($sexValue, 0, -2);
            }
        }

        if (!empty($question['googlemap_location']))
        {
            $fields['googlemap_location'] = $question['googlemap_location']['address'];
        }

        return $fields;
    }

    public function findMatchQuestionsForUser($userId)
    {
        return $this->questionMatchDao->findMatchQuestionsForUser($userId);
    }
    
    public function getDistance($userId)
    {
        if( empty($userId) )
        {
            return null;
        }
        
        return  BOL_PreferenceService::getInstance()->getPreferenceValue('matchmaking_distance_from_my_location', $userId);
    }

    /*
     * Check that user location equal or include match location
     *
     * return boolean
     *
     */

    public function isCompatibleLocation($userLocation, $matchLocation)
    {
        if ( !$userLocation || !$matchLocation  )
        {
            return false;
        }

        return (float)$userLocation['southWestLat'] <= (float)$matchLocation['southWestLat'] &&
        (float)$userLocation['northEastLat'] >= (float)$matchLocation['southWestLat'] &&

        (float)$userLocation['southWestLat'] <= (float)$matchLocation['northEastLat'] &&
        (float)$userLocation['northEastLat'] >= (float)$matchLocation['northEastLat'] &&

        (float)$userLocation['southWestLng'] <= (float)$matchLocation['southWestLng'] &&
        (float)$userLocation['northEastLng'] >= (float)$matchLocation['southWestLng'] &&

        (float)$userLocation['southWestLng'] <= (float)$matchLocation['southWestLng'] &&
        (float)$userLocation['northEastLng'] >= (float)$matchLocation['northEastLng'];
    }
    
    public function saveDistance($userId, $distance)
    {
        if( empty($userId) )
        {
            return;
        }
        
         BOL_PreferenceService::getInstance()->savePreferenceValue('matchmaking_distance_from_my_location', (float)$distance, $userId);
    }

    public function countActiveUsers()
    {
        return $this->questionMatchDao->countActiveUsers();
    }

    public function findActiveUsersList( $first, $count )
    {
        return $this->questionMatchDao->findActiveUsersList( $first, $count );
    }
}