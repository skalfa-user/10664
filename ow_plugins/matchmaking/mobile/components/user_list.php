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

class MATCHMAKING_MCMP_UserList extends BASE_MCMP_BaseUserList
{
    public function __construct($listKey, $list, $showOnline) 
    {
        parent::__construct($listKey, $list, $showOnline);
        
        $this->listKey = 'matchmaking-'.$listKey;
    }
}