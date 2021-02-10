<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

//temp fix of context switch

$router = OW::getRouter();

$router->removeRoute('base_join');
$router->addRoute(new OW_Route('base_join', 'join', 'SKADATE_MCTRL_Join', 'index'));

SKADATE_CLASS_EventHandler::getInstance()->genericInit();

//if ( OW::getApplication()->getContext() == OW_Application::CONTEXT_MOBILE )
//{
//    OW::getApplication()->redirect(OW_URL_HOME . OW::getRequest()->getRequestUri(), OW_Application::CONTEXT_DESKTOP);
//}