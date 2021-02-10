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
 * Delete membership form
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.classes
 * @since 1.6.1
 */
class MEMBERSHIP_CLASS_DeleteMembershipForm extends Form
{
    public function __construct( )
    {
        parent::__construct('delete-membership-form');

        $this->setAjaxResetOnSuccess(false);

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlForRoute('membership_delete_type'));

        $lang = OW::getLanguage();

        $typeId = new HiddenField('typeId');
        $typeId->setRequired(true);
        $this->addElement($typeId);

        $newTypeId = new Selectbox('newTypeId');
        $newTypeId->setHasInvitation(false);
        $this->addElement($newTypeId);

        $types = new RadioGroupItemField('type');
        $types->setRequired(true);
        $types->setLabel($lang->text('membership', 'set_membership'));
        $this->addElement($types);

        $this->bindJsFunction(
            Form::BIND_SUCCESS,
            "function( data ) {
                if ( data.result ) {
                    document.location.reload();
                }
            }"
        );

        $script = '$("#btn-confirm-type-delete").click(function(){
            if ( confirm('.json_encode($lang->text('membership', 'type_delete_confirm')).') ) {
                 $(this).parents("form:eq(0)").submit();
            }
        });
        ';

        OW::getDocument()->addOnloadScript($script);
    }
}