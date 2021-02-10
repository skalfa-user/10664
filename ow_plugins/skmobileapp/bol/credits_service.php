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

class SKMOBILEAPP_BOL_CreditsService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Get credits info
     *
     * @param integer $userId
     * @return array
     */
    public function getCreditsInfo($userId)
    {
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $showMembershipActions = OW::getConfig()->getValue('skmobileapp', 'inapps_show_membership_actions');
        $accountTypeId = $creditService->getUserAccountTypeId($userId);
        $earning = $creditService->findCreditsActions('earn', $accountTypeId, false);
        $losing = $creditService->findCreditsActions('lose', $accountTypeId, false);

        if ($showMembershipActions == SKMOBILEAPP_BOL_PaymentsService::ALL_MEMBERSHIP_ACTIONS) {
            return [
                'earning' => $earning,
                'losing' => $losing
            ];
        }

        $permissionList = SKMOBILEAPP_BOL_Service::getInstance()->getAppPermissionList();

        return [
            'earning' => $this->getOnlyAppRelatedActions($earning, $permissionList),
            'losing' =>  $this->getOnlyAppRelatedActions($losing, $permissionList)
        ];
    }

    /**
     * Get app related actions
     *
     * @param array $allActions
     * @param array $appPermissionList
     * @return array
     */
    protected function getOnlyAppRelatedActions(array $allActions, array $appPermissionList)
    {
        $proceedActions = array();

        foreach($allActions as $action) {
            foreach($appPermissionList as $permission) {
                if ($action['pluginKey'] == 'base' ||
                        $permission['group'] == $action['pluginKey'] && in_array($action['actionKey'], $permission['actions'])) {

                    $proceedActions[] = [
                        'title' => $action['title'],
                        'amount' => (int) $action['amount']
                    ];

                    break;
                }
            }
        }

        return $proceedActions;
    }
}
