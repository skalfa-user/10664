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

class GDPR_CMP_ThirdPartyWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $config = OW::getConfig()->getValues('gdpr');

        if ( $config['gdpr_third_party_services'] )
        {
            $form = new GDPR_CLASS_EmailForm();
            $this->addForm($form);
        }
        else
        {
            $this->setVisible(false);
        }
    }

    public static function getSettingList()
    {
        return [];
    }

    public static function getStandardSettingValueList()
    {
        return [
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => self::ICON_FRIENDS,
            self::SETTING_TITLE => OW::getLanguage()->text('gdpr', 'gdpr_third_widget_party_label')
        ];
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}