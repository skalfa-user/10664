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

class SKMOBILEAPP_BOL_PreferencesService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Save preferences
     * 
     * @param integer $userId
     * @param array $preferences
     * @return void
     */
    public function savePreferences($userId, array $preferences)
    {
        $service = BOL_PreferenceService::getInstance();

        foreach( $preferences as $name => $value )
        {
            $service->savePreferenceValue($name, (bool) $value, $userId);
        }
    }

    /**
     * Find preference section
     * 
     * @param string $name
     * @return string
     */
    public function findPreferenceSection($name)
    {
        $preferenceDto = BOL_PreferenceService::getInstance()->findPreference($name);

        if ( $preferenceDto )
        {
            return $preferenceDto->sectionName;
        }
    }

    /**
     * Find preferences questions 
     *
     * @param string $sectionName
     * @param integer $userId
     * @return array
     */
    public function findPreferencesQuestions( $userId, $sectionName )
    {
        $preferenceDao = BOL_PreferenceDao::getInstance();

        $example = new OW_Example();
        $example->andFieldEqual(BOL_PreferenceDao::SECTION, $sectionName);

        $preferenceList = $preferenceDao->findListByExample($example);
        $questions = [];

        if ( $preferenceList )
        {
            $preferenceService = BOL_PreferenceService::getInstance();

            foreach( $preferenceList as $preference )
            {
                $title = OW::getLanguage()->text('skmobileapp', 'preference_label_' . $preference->key);

                $questions[] = [
                    'key' => $preference->key,
                    'label' => OW::getLanguage()->text('skmobileapp', 'preference_label_' . $preference->key),
                    'placeholder' => $title, 
                    'type' => 'checkbox',
                    'value' => (bool) $preferenceService->getPreferenceValue($preference->key, $userId),
                    'validators' => []
                ];
            }
        }

        return $questions;
    }
}
