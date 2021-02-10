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
 * Membership ajax actions controller.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.controllers
 * @since 1.6.0
 */
class MEMBERSHIP_CTRL_Ajax extends OW_ActionController
{
    public function __construct()
    {
        parent::__construct();

        if ( !OW::getRequest()->isPost() || !OW::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }
    }

    public function set()
    {
        if ( !OW::getUser()->isAuthorized('membership') )
        {
            throw new Redirect403Exception();
        }
        
        $form = new MEMBERSHIP_CLASS_SetMembershipForm();

        if ( !$form->isValid($_POST) )
        {
            throw new Redirect403Exception();
        }

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $values = $form->getValues();

        if ( !$values['userId'] || !$values['type'] )
        {
            throw new Redirect403Exception();
        }

        $userId = (int) $values['userId'];
        $typeId = $values['type'];

        $user = BOL_UserService::getInstance()->findUserById($userId);
        if ( !$user )
        {
            throw new Redirect403Exception();
        }

        $result = array(
            'result' => true,
            'msg' => OW::getLanguage()->text('membership', 'user_membership_updated')
        );

        if ( $typeId == "default" )
        {
            $service->setDefaultMembership($userId);

            exit(json_encode($result));
        }

        $membership = $service->findTypeById($typeId);

        if ( !$membership )
        {
            throw new Redirect403Exception();
        }

        if ( empty($values['period']) )
        {
            $result = array(
                'result' => false,
                'msg' => OW::getLanguage()->text('membership', 'period_required')
            );

            exit(json_encode($result));
        }

        $ms = new MEMBERSHIP_BOL_MembershipUser();
        $ms->userId = $userId;
        $ms->typeId = $typeId;
        $ms->recurring = 0;
        $ms->expirationStamp = time() + (int) $values['period'] * 24 * 3600;

        $service->setUserMembership($ms);

        exit(json_encode($result));
    }

    public function deleteType()
    {
        if ( !OW::getUser()->isAdmin() )
        {
            throw new Redirect403Exception();
        }

        if ( empty($_POST['typeId']) )
        {
            throw new Redirect403Exception();
        }

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();

        $typeId = (int) $_POST['typeId'];
        $deleteType = isset($_POST['type']) && in_array($_POST['type'], array('custom', 'default')) ? $_POST['type'] : 'default';
        $newTypeId = isset($_POST['newTypeId']) && (int) $_POST['newTypeId'] ? (int) $_POST['newTypeId'] : null;

        $type = $service->findTypeById($typeId);
        if ( !$type )
        {
            throw new Redirect403Exception();
        }

        $event = new OW_Event('membership.delete_membership_type', array(
            'typeId' => $typeId, 'deleteType' => $deleteType, 'newTypeId' => $newTypeId
        ));
        OW::getEventManager()->trigger($event);
        
        if ( in_array($deleteType, array('default', 'custom')) )
        {
            $page = 1; $limit = 50;
            while ( true )
            {
                $users = $service->getUserObjectListByMembershipType($typeId, $page, $limit);

                if ( !$users )
                {
                    break;
                }

                foreach ( $users as $userMembership )
                {
                    if ( $deleteType == 'custom' && $newTypeId )
                    {
                        $userMembership->id = null;
                        $userMembership->typeId = $newTypeId;
                        $service->setUserMembership($userMembership);
                    }
                    else if ( $deleteType == 'default' )
                    {
                        $service->deleleUserMembership($userMembership);
                        $authService->deleteUserRole($userMembership->userId, $type->roleId);
                        $authService->assignDefaultRoleToUser($userMembership->userId);
                    }
                }

                $page++;
            }
        }

        $service->deleteTypeWithPlans($typeId);
        OW::getFeedback()->info(OW::getLanguage()->text('membership', 'types_deleted'));

        exit(json_encode(array('result' => true)));
    }

    public function deletePlans()
    {
        if ( !OW::getUser()->isAdmin() )
        {
            throw new Redirect403Exception();
        }

        if ( empty($_POST['plans']) )
        {
            throw new Redirect403Exception();
        }

        $plans = $_POST['plans'];

        $event = new OW_Event('membership.delete_membership_plans', array('plans' => $plans));
        OW::getEventManager()->trigger($event);

        $service = MEMBERSHIP_BOL_MembershipService::getInstance();
        foreach ( $plans as $planId )
        {
            $service->deletePlan($planId);
        }

        exit(json_encode(array('result' => true)));
    }
}
