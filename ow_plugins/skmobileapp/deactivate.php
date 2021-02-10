<?php

$pluginKey = 'skmobileapp';

$widgetService = BOL_ComponentAdminService::getInstance();

$widgetService->deleteWidget('SKMOBILEAPP_CMP_MobileExperience');
BOL_MobileWidgetService::getInstance()->deleteWidget('SKMOBILEAPP_MCMP_MobileExperience');

$desktopWidget = $widgetService->addWidget('SKADATE_CMP_MobileExperience', false);

try {
    $widgetService->addWidgetToPosition(
        $widgetService->addWidgetToPlace($desktopWidget, BOL_ComponentAdminService::PLACE_INDEX),
        BOL_ComponentService::SECTION_RIGHT
    );
}
catch (Exception $e) {
    OW::getLogger('skmobileapp.activate.desktop_widget_mobile_experience_index')->addEntry(json_encode($e));
}

try {
    $widgetService->addWidgetToPosition(
        $widgetService->addWidgetToPlace($desktopWidget, BOL_ComponentAdminService::PLACE_DASHBOARD),
        BOL_ComponentService::SECTION_RIGHT
    );
}
catch (Exception $e) {
    OW::getLogger('skmobileapp.activate.desktop_widget_mobile_experience_dashboard')->addEntry(json_encode($e));
}

BOL_BillingService::getInstance()->deactivateGateway($pluginKey);