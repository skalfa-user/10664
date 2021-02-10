<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakytow <kainisoft@gmail.com>
 * @package ow_plugins.badwords.bol
 * @since 1.1
 */
class BADWORDS_BOL_BadwordsDao extends OW_BaseDao
{
    CONST FIELD_TEXT = 'text';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'BADWORDS_BOL_BadwordsDto';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'badwords_badwords';
    }

    public function findBadwords( $page, $limit )
    {
        $first = ($page - 1) * $limit;
        
        $example = new OW_Example();
        $example->setOrder(self::FIELD_TEXT);
        $example->setLimitClause($first, $limit);

        return $this->findListByExample($example);
    }
    
    public function saveBadwords( array $value )
    {
        if ( empty($value) )
        {
            return;
        }
        
        $str = implode('"),("', array_filter(array_map('trim', $value), 'strlen'));
        
        return OW::getDbo()->query('INSERT IGNORE INTO `' . $this->getTableName() .'` (`text`) VALUES("' . $str . '")');
    }
}
