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
 * Edit Membership component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.components
 * @since 1.6.0
 */
class MEMBERSHIP_CMP_EditMembership extends OW_Component
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

        $plans = $service->getPlanList($typeId);
        $this->assign('plans', $plans);
        $this->assign('membership', $service->getMembershipTitle($type->roleId));
        $this->assign('typeId', $typeId);
        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());

        $script =
        '$("#btn_add_plan").click(function(){
            $(".paid-plan-template:first").clone().insertBefore($(this).closest("tr")).show();
        });

        $("#btn_add_trial_plan").click(function(){
            $(".trial-plan-template:first").clone().insertBefore($(this).closest("tr")).show();
        });

        $("#plans-form").submit(function(){
            $(".paid-plan-template:first").remove();
            $(".trial-plan-template:first").remove();
        });

        $("body")
            .on("change", "#check_all", function(){
                $("#plans .plan_id, #plans .new_plan_id").prop("checked", $(this).prop("checked"));
            });

        $("#btn_delete").click(function(){
            var $plans = $("#plans input.plan_id:checked");
            if ( $plans.length ) {
                var plans = $plans.map(function(){
                    return $(this).data("pid");
                }).get();
                $.ajax({
                    type: "POST",
                    url: ' . json_encode(OW::getRouter()->urlForRoute('membership_delete_plans')) . ',
                    data: { plans : plans },
                    dataType: "json",
                    success : function(data){
                        $plans.each(function(){
                            $(this).closest("tr").remove();
                        });
                    }
                });
            }
            var $newPlans = $("#plans input.new_plan_id:checked:visible");
            if ( $newPlans.length )
            {
                $newPlans.each(function(){
                    $(this).closest("tr").remove();
                });
            }
        });
        ';
        
        $this->assign('periodUnitsList', MEMBERSHIP_BOL_MembershipService::getInstance()->getPeriodUnitsList());
        
        OW::getDocument()->addOnloadScript($script);
    }
}