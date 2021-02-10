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

class BILLINGSTRIPE_CLASS_SettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');

        $lang = OW::getLanguage();
        $configKey = 'billingstripe';

        $sandboxMode = new CheckboxField('sandboxMode');
        $sandboxMode->setLabel($lang->text('billingstripe', 'sandbox_mode'));
        $this->addElement($sandboxMode);

        $requireData = new CheckboxField('requireData');
        $requireData->setLabel($lang->text('billingstripe', 'require_data'));
        $this->addElement($requireData);

        $livePK = new TextField('livePK');
        $livePK->setLabel($lang->text('billingstripe', 'live_public_key'));
        $this->addElement($livePK);

        $liveSK = new TextField('liveSK');
        $liveSK->setLabel($lang->text('billingstripe', 'live_secret_key'));
        $this->addElement($liveSK);

        $testPK = new TextField('testPK');
        $testPK->setLabel($lang->text('billingstripe', 'test_public_key'));
        $this->addElement($testPK);

        $testSK = new TextField('testSK');
        $testSK->setLabel($lang->text('billingstripe', 'test_secret_key'));
        $this->addElement($testSK);

        // font color
        $fontColor = new ColorField('font_color');
        $fontColor->setLabel($lang->text('billingstripe', 'card_detail_font_color'));
        $fontColor->setValue(OW::getConfig()->getValue($configKey, 'card_detail_font_color'));
        $this->addElement($fontColor);

        // font family
        $fontFamily = new TextField('font_family');
        $fontFamily->setLabel($lang->text('billingstripe', 'card_detail_font_family'));
        $fontFamily->setValue(OW::getConfig()->getValue($configKey, 'card_detail_font_family'));
        $this->addElement($fontFamily);

        // font size
        $fontSize = new TextField('font_size');
        $fontSize->setLabel($lang->text('billingstripe', 'card_detail_font_size'));
        $fontSize->setValue(OW::getConfig()->getValue($configKey, 'card_detail_font_size'));
        $this->addElement($fontSize);

        // placeholder color
        $placeholderColor = new ColorField('placeholder_color');
        $placeholderColor->setLabel($lang->text('billingstripe', 'card_detail_placeholder_color'));
        $placeholderColor->setValue(OW::getConfig()->getValue($configKey, 'card_detail_placeholder_color'));
        $this->addElement($placeholderColor);

        // icon color
        $iconColor = new ColorField('icon_color');
        $iconColor->setLabel($lang->text('billingstripe', 'card_detail_icon_color'));
        $iconColor->setValue(OW::getConfig()->getValue($configKey, 'card_detail_icon_color'));
        $this->addElement($iconColor);

        // error font color
        $errorFontColor = new ColorField('error_font_color');
        $errorFontColor->setLabel($lang->text('billingstripe', 'card_detail_error_font_color'));
        $errorFontColor->setValue(OW::getConfig()->getValue($configKey, 'card_detail_error_font_color'));
        $this->addElement($errorFontColor);

        // error icon color
        $errorIconColor = new ColorField('error_icon_color');
        $errorIconColor->setLabel($lang->text('billingstripe', 'card_detail_error_icon_color'));
        $errorIconColor->setValue(OW::getConfig()->getValue($configKey, 'card_detail_error_icon_color'));
        $this->addElement($errorIconColor);

        // submit
        $submit = new Submit('save');
        $submit->setValue($lang->text('billingstripe', 'btn_save'));
        $this->addElement($submit);
    }
}
