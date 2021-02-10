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
 * Set Membership component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.components
 * @since 1.6.0
 */
class MEMBERSHIP_CMP_SetMembership extends OW_Component
{
    /**
     * Class constructor
     */
    public function __construct( $userId )
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthorized('membership') )
        {
            $this->setVisible(false);

            return;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            $this->setVisible(false);

            return;
        }

        $accTypeName = $user->getAccountType();
        $accType = BOL_QuestionService::getInstance()->findAccountTypeByName($accTypeName);

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();

        $types = $service->getTypeList($accType->id);

        /* @var $defaultRole BOL_AuthorizationRole */
        $defaultRole = $authService->getDefaultRole();
        $default = array('value' => 'default', 'label' => $authService->getRoleLabel($defaultRole->name));
        $this->assign('default', $default);

        $memberships = array();
        foreach ( $types as &$ms )
        {
            $memberships[$ms->id] = $service->getMembershipTitle($ms->roleId);
        }
        $this->assign('memberships', $memberships);

        $current = $service->getUserMembership($userId);
        $this->assign('current', $current);

        if ( $current )
        {
            $this->assign('remaining', $service->getRemainingPeriod($current->expirationStamp));
        }

        $form = new MEMBERSHIP_CLASS_SetMembershipForm();
        $this->addForm($form);

        $form->getElement('userId')->setValue($userId);
        $form->bindJsFunction(
            Form::BIND_SUCCESS,
            "function(data){
                if ( data.result ) {
                    OW.info(data.msg);
                    document.setMembershipFloatBox.close();
                }
                else {
                    OW.error(data.msg);
                }
             }"
        );

        $script =
        '$("input[name=type]").change(function(){
            if ( $(this).val() == "default" ) {
                $("#period-cont").css("display", "none");
            }
            else {
                $("#period-cont").css("display", "table-row");
            }
        });';

        OW::getDocument()->addOnloadScript($script);
    }
}