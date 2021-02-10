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
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks.bol
 * @since 1.0
 */
class WINKS_BOL_Winks extends OW_Entity
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
    
    public $partnerId;
    
    public function getPartnerId()
    {
        return (int)$this->partnerId;
    }
    
    public function setPartnerId( $value )
    {
        $this->partnerId = (int)$value;
        
        return $this;
    }
    
    public $timestamp;
    
    public function getTimestamp()
    {
        return (int)$this->timestamp;
    }
    
    public function setTimeStamp( $value )
    {
        $this->timestamp = (int)$value;
        
        return $this;
    }
    
    public $status;
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus( $value )
    {
        $this->status = $value;
        
        return $this;
    }

    public $viewed;
    
    public function getViewed()
    {
        return (int)$this->viewed;
    }
    
    public function setViewed( $value )
    {
        $this->viewed = (int)$value;
        
        return $this;
    }
    
    public $conversationId;
    
    public function getConversationId()
    {
        return (int)$this->conversationId;
    }
    
    public function setConversationId( $value )
    {
        $this->conversationId = (int)$value;
        
        return $this;
    }
    
    public $winkback;
    
    public function getWinkback()
    {
        return (int)$this->winkback;
    }
    
    public function setWinkback( $value )
    {
        $this->winkback = (int)$value;
        
        return $this;
    }

    public $messageType;

    public function setMessageType( $type )
    {
        $this->messageType = $type;
        
        return $this;
    }
}
