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
 * Bookmarks Mark Entity
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.bol
 * @since 1.0
 */
class BOOKMARKS_BOL_Mark extends OW_Entity
{
    public $userId;
    public $markUserId;

    public function getUserId()
    {
        return (int)$this->userId;
    }

    public function setUserId( $value )
    {
        $this->userId = (int)$value;
        
        return $this;
    }
    
    public function getMarkUserId()
    {
        return (int)$this->markUserId;
    }

    public function setMarkUserId( $value )
    {
        $this->markUserId = (int)$value;
        
        return $this;
    }
}
