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
class BADWORDS_BOL_Service
{
    CONST PHP_PATTERN = '/\b(?:{$words})\b(?:(?![^<]*?>))/i';
    CONST JS_PATTERN  = '/\b(?:{$words})\b(?:(?![^<]*?>))/ig';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $badwordsDao;
    
    private function __construct()
    {
        $this->badwordsDao = BADWORDS_BOL_BadwordsDao::getInstance();
    }

    public function findBadwords( $first, $count )
    {
        return $this->badwordsDao->findBadwords($first, $count);
    }
    
    public function saveBadwods( array $value )
    {
        return $this->badwordsDao->saveBadwords($value);
    }
    
    public function countBadwords()
    {
        return $this->badwordsDao->countAll();
    }
    
    public function deleteBadwordsByIdList( array $idList )
    {
        return $this->badwordsDao->deleteByIdList($idList);
    }
    
    public function findAllBadwords()
    {
        $result = array();
        $words = $this->badwordsDao->findAll();
        
        if ( !empty($words) )
        {
            $_pattern = array();
            
            foreach ( $words as $word )
            {
                $_pattern[] = preg_quote($word->text, '/');
            }
            
            $result['php_pattern'] = str_replace('{$words}', implode('|', $_pattern), self::PHP_PATTERN);
            $result['js_pattern'] = str_replace('{$words}', implode('|', $_pattern), self::JS_PATTERN);
        }
        
        return $result;
    }
}
