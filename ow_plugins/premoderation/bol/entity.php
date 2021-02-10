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

class MODERATION_BOL_Entity extends OW_Entity
{
    /**
     *
     * @var string
     */
    public $entityType;
    
    /**
     *
     * @var int
     */
    public $entityId;
    
    /**
     *
     * @var int
     */
    public $timeStamp;
    
    /**
     *
     * @var string
     */
    public $data;
    
    /**
     *
     * @var int
     */
    public $userId;
    
    public function setData( array $data )
    {
        $this->data = json_encode($data);
    }
    
    public function getData()
    {
        if ( empty($this->data) )
        {
            return null;
        }
        
        return json_decode($this->data, true);
    }
}
