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

/**
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow_plugins.smsverification.classes
 * @since 1.7.6
 */
class SMSVERIFICATION_CLASS_InputCodeForm extends Form
{
    public function __construct()
    {
        parent::__construct('inputCode-form');

        $lang = OW::getLanguage();

        $userCode = new TextField('userCode');
        $this->addElement($userCode);        

        // submit
        $confirm = new Submit('confirm_btn');
        $confirm->setValue($lang->text('smsverification', 'confirm_btn'));
        $this->addElement($confirm);
        
        $changeNumber = new Submit('changeNumber_btn');
        $changeNumber->setValue($lang->text('smsverification', 'changeNumber_btn'));
        $this->addElement($changeNumber);
        
    }    
    
}

