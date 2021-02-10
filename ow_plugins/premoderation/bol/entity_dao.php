<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class MODERATION_BOL_EntityDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var MODERATION_BOL_EntityDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MODERATION_BOL_EntityDao
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
        return 'MODERATION_BOL_Entity';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'moderation_entity';
    }
    
    /**
     * 
     * @param string $entityType
     * @param int $entityId
     *
     * @return MODERATION_BOL_Entity
     */
    public function findEntity( $entityType, $entityId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findByEntityTypeList( array $entityTypes, array $limit = null, $ownerId = null )
    {
        $example = new OW_Example();
        $example->andFieldInArray("entityType", $entityTypes);
        
        if ( $ownerId !== null )
        {
            $example->andFieldEqual("userId", $ownerId);
        }
        
        if ( !empty($limit) )
        {
            $example->setLimitClause($limit[0], $limit[1]);
        }
        
        $example->setOrder("timeStamp DESC");
        
        return $this->findListByExample($example);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findCountForEntityTypeList( $entityTypes, $ownerId = null )
    {
        if ( empty($entityTypes) )
        {
            return array();
        }
        
        $userCond = $ownerId === null ? "1" : "`userId`=" . intval($ownerId);
        
        $query = "SELECT count(DISTINCT `entityId`) `count`, `entityType` "
                    . "FROM `" . $this->getTableName() . "` "
                    . "WHERE `entityType` IN ('" . implode("', '", $entityTypes) . "') "
                        . "AND " . $userCond . " "
                    . "GROUP BY `entityType`";
        
        $out = array();
        foreach ( $this->dbo->queryForList($query) as $row )
        {
            $out[$row['entityType']] = $row['count'];
        }
        
        return $out;
    }
    
    public function deleteEntityList( $entityType, array $entityIdList = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        
        if ( !empty($entityIdList) )
        {
            $example->andFieldInArray("entityId", $entityIdList);
        }

        $this->deleteByExample($example);
    }
}