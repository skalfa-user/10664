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
 * Set membership form
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.classes
 * @since 1.6.0
 */
class MEMBERSHIP_CLASS_SetMembershipForm extends Form
{
    public function __construct( )
    {
        parent::__construct('set-membership-form');

        $this->setAjaxResetOnSuccess(false);

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlForRoute('membership_set'));

        $lang = OW::getLanguage();

        $userId = new HiddenField('userId');
        $userId->setRequired(true);
        $this->addElement($userId);

        $types = new RadioGroupItemField('type');
        $types->setRequired(true);
        $types->setLabel($lang->text('membership', 'set_membership'));
        $this->addElement($types);

        $period = new TextField('period');
        $period->setLabel($lang->text('membership', 'set_period'));
        $this->addElement($period);

        $submit = new Submit('set');
        $submit->setValue($lang->text('membership', 'set'));
        $this->addElement($submit);
    }
}