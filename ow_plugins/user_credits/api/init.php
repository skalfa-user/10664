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

OW::getRouter()->addRoute(
    new OW_Route('usercredits.buy_credits', 'user-credits/buy-credits', 'USERCREDITS_CTRL_BuyCredits', 'index')
);

USERCREDITS_CLASS_EventHandler::getInstance()->genericInit();