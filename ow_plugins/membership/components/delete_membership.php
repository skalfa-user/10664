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
 * Delete Membership component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.components
 * @since 1.6.1
 */
class MEMBERSHIP_CMP_DeleteMembership extends OW_Component
{
    /**
     * Class constructor
     */
    public function __construct( $typeId )
    {
        parent::__construct();

        if ( !OW::getUser()->isAdmin() )
        {
            $this->setVisible(false);

            return;
        }

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $type = $service->findTypeById($typeId);

        if ( !$type )
        {
            $this->setVisible(false);

            return;
        }

        $types = $service->getTypeList($type->accountTypeId);
        $availableTypes = array();
        if ( $types )
        {
            foreach ( $types as $mType )
            {
                if ( $mType->id == $typeId )
                {
                    continue;
                }
                $availableTypes[$mType->id] = $service->getMembershipTitle($mType->roleId);
            }
        }
        $this->assign('availableTypes', $availableTypes);

        $form = new MEMBERSHIP_CLASS_DeleteMembershipForm();
        $this->addForm($form);

        $form->getElement('typeId')->setValue($typeId);
        if ( $availableTypes )
        {
            $form->getElement('newTypeId')->addOptions($availableTypes);
        }
    }
}