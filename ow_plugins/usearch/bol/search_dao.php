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

class USEARCH_BOL_SearchDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var USEARCH_BOL_SearchDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return USEARCH_BOL_SearchDao
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
        return 'BOL_User';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return null;
    }

    public function findSearchResultListByLatestActivity( $userIdList, $first, $count )
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("user", "id",
            array(
            "method" => "USEARCH_BOL_SearchDao::findSearchResultListByLatestActivity"
        ));

        $where = '';

        $sql = "SELECT `user`.* FROM `" . BOL_UserDao::getInstance()->getTableName() . "` `user`
            {$queryParts["join"]}
            WHERE `user`.`id` IN (" . $this->dbo->mergeInClause($userIdList) . ") $where
            ORDER BY " . (!empty($queryParts["order"]) ? $queryParts["order"] . ", " : "" ) . " `user`.`activityStamp` DESC LIMIT :from, :count ";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(),
                array('from' => (int) $first, 'count' => (int) $count));
    }

    public function findSearchResultListOrderedByRecentlyJoined( $userIdList, $first, $count )
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("user", "id",
            array(
            "method" => "USEARCH_BOL_SearchDao::findSearchResultListOrderedByRecentlyJoined"
        ));

        $where = '';

        $sql = "SELECT `user`.* FROM `" . BOL_UserDao::getInstance()->getTableName() . "` `user`
            {$queryParts["join"]}
                
            WHERE `user`.`id` IN (" . $this->dbo->mergeInClause($userIdList) . ") $where
            ORDER BY user.joinStamp DESC, `user`.`activityStamp` DESC  LIMIT :from, :count  ";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(),
                array('from' => (int) $first, 'count' => (int) $count));
    }

    /**
     * Return search result item count
     *
     * @param int $listId
     * @param int $first
     * @param int $count
     * return array
     */
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        $example = new OW_Example();
        $example->andFieldEqual('searchId', (int) $listId);
        $example->setOrder(' sortOrder ');
        $example->setLimitClause($first, $count);

        if ( !empty($excludeList) )
        {
            $example->andFieldNotInArray('userId', $excludeList);
        }

        $results = BOL_SearchResultDao::getInstance()->findListByExample($example);

        $userIdList = array();

        foreach ( $results as $result )
        {
            $userIdList[] = $result->userId;
        }

        return $userIdList;
    }

    public function findUserIdListByQuestionValues( $questionValues, $first, $count, $isAdmin = false,
        $aditionalParams = array() )
    {
        $questionNameList = array_keys($questionValues);

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);

        $prefix = 'qd';
        $counter = 0;
        $innerJoin = '';
        $where = '';

        foreach ( $questions as $question )
        {
            if ( !empty($questionValues[$question->name]) && $question->name != 'password' )
            {
                if ( $question->base == 1 )
                {
                    $where .= ' AND `user`.`' . $this->dbo->escapeString($question->name) . '` LIKE \'' . $this->dbo->escapeString($questionValues[$question->name]) . '%\'';
                }
                else
                {
                    $params = array(
                        'question' => $question,
                        'value' => $questionValues[$question->name],
                        'prefix' => $prefix . $counter
                    );

                    $event = new BASE_CLASS_QueryBuilderEvent("base.question.search_sql", $params);

                    OW::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if ( !empty($data['join']) || !empty($data['where']) )
                    {
                        $innerJoin .= $event->getJoin();
                        $where .= ' AND ' . $event->getWhere();
                    }
                    else
                    {
                        $questionString = BOL_UserDao::getInstance()->getQuestionWhereString($question,
                            $questionValues[$question->name], $prefix . $counter);
                        if ( !empty($questionString) )
                        {
                            $innerJoin .= " INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $prefix . $counter . "`
                                ON ( `user`.`id` = `" . $prefix . $counter . "`.`userId` AND `" . $prefix . $counter . "`.`questionName` = '" . $this->dbo->escapeString($question->name) . "' AND " . $questionString . " ) ";
                        }
                    }

                    $counter++;
                }
            }
        }

        if ( !empty($aditionalParams['join']) )
        {
            $innerJoin .= $aditionalParams['join'];
        }

        if ( !empty($aditionalParams['where']) )
        {
            $where = $aditionalParams['where'];
        }

        if ( !empty($questionValues['accountType']) )
        {
            $where .= " AND `user`.`accountType` = '" . $this->dbo->escapeString($questionValues['accountType']) . "' ";
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("user", "id",
            array(
            "method" => "BOL_UserDao::findUserIdListByQuestionValues"
        ));

        $order = '`user`.`activityStamp` DESC';

        if ( !empty($aditionalParams['order']) )
        {
            $order = $aditionalParams['order'];
        }

        $usersTableName = "`".BOL_UserDao::getInstance()->getTableName()."`";
        
        if ( OW_SQL_LIMIT_USERS_COUNT > 0 )
        {
            $orderFieldname = BOL_UserDao::ACTIVITY_STAMP;
            $usersTableName = "( SELECT * FROM {$usersTableName} ORDER BY `{$orderFieldname}` DESC LIMIT " . OW_SQL_LIMIT_USERS_COUNT . " )";
        }

        $fields = '`user`.id, `user`.`activityStamp`';

        if ( !empty($aditionalParams['fields']) )
        {
            $fields = $aditionalParams['fields'];
        }

        $query = "SELECT DISTINCT {$fields} FROM {$usersTableName} `user`
                {$innerJoin}
                {$queryParts["join"]}

                WHERE {$queryParts["where"]} {$where}
                ORDER BY {$order}
                LIMIT :first, :count ";

        if ( $isAdmin === true )
        {
            $fields = '`user`.id';

            if ( !empty($aditionalParams['fields']) )
            {
                $fields = $aditionalParams['fields'];
            }

            $query = "SELECT DISTINCT {$fields} FROM {$usersTableName} `user`
                {$innerJoin}
                WHERE 1 {$where}
                ORDER BY {$order}
                LIMIT :first, :count ";
        }

        return $this->dbo->queryForColumnList($query, array_merge(array('first' => $first, 'count' => $count)));
    }


//    public function findSearchResultListOrderedByMatchCompatibility( $listId, $first, $count )
//    {
//        $userIdList = BOL_SearchService::getInstance()->getUserIdList($listId, $first, $count);
//
//        if ( empty($userIdList) )
//        {
//            return array();
//        }
//
//        $queryParts = $this->getUserQueryFilter("user", "id", array(
//            "method" => "BOL_UserDao::findSearchResultList"
//        ));
//
//        $join = '';
//        $order = '';
//        
//
//        
//        $sql = "SELECT `user`.* FROM `" . BOL_UserDao::getInstance()->getTableName() . "` `user`
//            {$queryParts["join"]}
//            {$join}
//            WHERE `user`.`id` IN (" . $this->dbo->mergeInClause($userIdList) . ")
//            ORDER BY user.joinStamp DESC, `user`.`activityStamp` DESC";
//
//        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
//    }
}
