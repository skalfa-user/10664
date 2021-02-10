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
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_plugins.usercredits.classes
 * @since 1.5.1
 */
class USERCREDITS_CLASS_SetCreditsForm extends Form
{
    public function __construct()
    {
        parent::__construct('set-credits-form');

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor('USERCREDITS_CTRL_Ajax', 'setCredits'));

        $lang = OW::getLanguage();

        $userIdField = new HiddenField('userId');
        $userIdField->setRequired(true);
        $this->addElement($userIdField);

        $balance = new TextField('balance');
        $this->addElement($balance);

        $submit = new Submit('save');
        $submit->setValue($lang->text('base', 'edit_button'));
        $this->addElement($submit);

        $js = 'owForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error ){
                OW.error(data.error);
            }
            
            if ( data.message ) {
                OW.info(data.message);
            }

            _scope.floatBox && _scope.floatBox.close();
            _scope.callBack && _scope.callBack(data);
        });';

        OW::getDocument()->addOnloadScript($js);
    }
}