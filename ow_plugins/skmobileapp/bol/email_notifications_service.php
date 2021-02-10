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

class SKMOBILEAPP_BOL_EmailNotificationsService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Save settings
     * 
     * @param integer $userId
     * @param array $settings
     * @return void
     */
    public function saveSettings( $userId, array $settings )
    {
        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $userSettingList = $service->findRuleList($userId);

        foreach ( $settings as $settingName => $settingValue )
        {
            if ( empty($userSettingList[$settingName]) )
            {
                $dto = new NOTIFICATIONS_BOL_Rule();
                $dto->userId = $userId;
                $dto->action = $settingName;
            }
            else
            {
                $dto = $userSettingList[$settingName];
            }

            $checked = (int) $settingValue;

            if ( !empty($dto->id) && $dto->checked == $checked )
            {
                continue;
            }

            $dto->checked = $checked;
            $service->saveRule($dto);
        }
    }

    /**
     * Find settings questions 
     *
     * @param integer $userId
     * @param array $allowedSettings
     * @return array
     */
    public function findSettingsQuestions( $userId, array $allowedSettings )
    {
        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $notifications = $service->collectActionList();
        $questions = [];

        foreach ( $notifications as $notification )
        {
            if ( !in_array($notification['action'], $allowedSettings) )
            {
                continue;
            }

            $questions[] = [
                'key' => $notification['action'],
                'label' => OW::getLanguage()->text('skmobileapp', 'email_setting_label_' . $notification['action']),
                'type' => 'checkbox',
                'value' => (bool) $service->isNotificationPermited($userId, $notification['action']),
                'validators' => []
            ];
        }

        return $questions;
    }
}
