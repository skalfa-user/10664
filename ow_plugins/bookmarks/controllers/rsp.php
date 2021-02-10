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
 * Bookmarks RSP controller
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.controllers
 * @since 1.0
 */
class BOOKMARKS_CTRL_Rsp extends OW_ActionController
{
    protected $service;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->service = BOOKMARKS_BOL_Service::getInstance();
    }

    public function markState( array $params = array() )
    {
        if ( !OW::getRequest()->isAjax() || empty($_POST['userId']) || !OW::getUser()->isAuthenticated() )
        {
            exit();
        }
        
        if ( !$this->service->isMarked(OW::getUser()->getId(), $_POST['userId']) )
        {
            $this->service->mark(OW::getUser()->getId(), $_POST['userId']);
            
            exit(json_encode(array('mark' => TRUE)));
        }
        else
        {
            $this->service->unmark(OW::getUser()->getId(), $_POST['userId']);
            
            exit(json_encode(array('mark' => FALSE)));
        }
    }
}
