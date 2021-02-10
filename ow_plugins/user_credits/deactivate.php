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

BOL_BillingService::getInstance()->deactivateProduct('user_credits_pack');

OW::getConfig()->saveConfig('usercredits', 'is_once_initialized', 0);

BOL_ComponentAdminService::getInstance()->deleteWidget('USERCREDITS_CMP_MyCreditsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('USERCREDITS_CMP_CreditStatisticWidget');

OW::getNavigation()->deleteMenuItem('usercredits', 'subscribe_page_heading');

OW::getNavigation()->deleteMenuItem('usercredits', 'subscribe_page_heading_mobile');