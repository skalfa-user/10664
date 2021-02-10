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

BOL_BillingService::getInstance()->activateProduct('user_credits_pack');

USERCREDITS_BOL_CreditsService::getInstance()->addActionPriceToAccountTypes();

$subscribeMenuMobile = BOL_NavigationService::getInstance()->findMenuItem('membership', 'subscribe_page_heading_mobile');

if( empty($subscribeMenuMobile) )
{
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'usercredits.buy_credits', 'usercredits', 'subscribe_page_heading_mobile', OW_Navigation::VISIBLE_FOR_MEMBER);
}

$subscribeMenuDesktop = BOL_NavigationService::getInstance()->findMenuItem('membership', 'subscribe_page_heading');

if( empty($subscribeMenuMobile) )
{
    OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'usercredits.buy_credits', 'usercredits', 'subscribe_page_heading', OW_Navigation::VISIBLE_FOR_MEMBER);
}

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('USERCREDITS_CMP_MyCreditsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('USERCREDITS_CMP_CreditStatisticWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLASE_ADMIN_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_TOP);



