<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Bookmarks Notify Entity
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.bol
 * @since 1.0
 */
class BOOKMARKS_BOL_NotifyLog extends OW_Entity
{
    public $userId;

    public function getUserId()
    {
        return (int)$this->userId;
    }

    public function setUserId( $value )
    {
        $this->userId = (int)$value;
        
        return $this;
    }
    
    public $timestamp;
    
    public function getTimestamp()
    {
        return (int)$this->timestamp;
    }
    
    public function setTimestamp( $value )
    {
        $this->timestamp = (int)$value;
        
        return $this;
    }
}
