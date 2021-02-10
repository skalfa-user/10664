<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class GDPR_CLASS_SettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('gdpr-settings-form');
        $configs = OW::getConfig()->getValues('gdpr');

        $checkbox = new CheckboxField('gdpr_third_party_services');
        $checkbox->setValue($configs['gdpr_third_party_services']);
        $this->addElement($checkbox->setLabel(OW::getLanguage()->text('gdpr', 'gdpr_third_party_services_title')));

        $submit = new Submit('gdpr-save');
        $submit->setValue(OW::getLanguage()->text('gdpr', 'gdpr_save_btn'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();
        $config = OW::getConfig();
        $config->saveConfig('gdpr', 'gdpr_third_party_services', $values['gdpr_third_party_services']);
    }
}
