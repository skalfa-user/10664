import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { NavController, NavParams, ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { PaymentsService, IMembershipPlanResponse } from 'services/payments';
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';

//pages
import { DashboardPage } from 'pages/dashboard';
import { ViewPaymentsGatewaysPage } from 'pages/payments/gateways/initial';

// base view membership page
import { BaseViewMembership } from '../base.view';

@Component({
    selector: 'view-membership-mobile',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class ViewMembershipMobilePage extends BaseViewMembership implements OnInit {
    /**
     * Constructor
     */
    constructor(
        protected translate: TranslateService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected navParams: NavParams, 
        private user: UserService,
        private nav: NavController,
        private ref: ChangeDetectorRef,
        private payments: PaymentsService) 
    {
        super(
            translate,
            toast,
            siteConfigs,
            navParams
        );
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // load the membership
        this.payments.loadMembership(this.membershipId).subscribe(response => {
            this.membership = response;
            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Buy product
     */
    buyProduct(plan: IMembershipPlanResponse): void {
        // add a trial plan
        if (!plan.price) {
            // users can select trial plans only once 
            if (this.myMembership.isActiveAndTrial) {
                this.showNotification('membership_trial_error');

                return;
            }

            // add trial plan
            this.buyingPlanId = plan.id;
            this.ref.markForCheck();
 
            this.payments.addTrialMembership(plan.id).subscribe(() => {
                // update logged user's data (including permissions)
                this.user.loadMe().subscribe(() => {
                    this.showNotification('membership_trial_added', {
                        amountDays: plan.period
                    });
                    this.nav.setRoot(DashboardPage);
                });
            });

            return;
        }

        // show the gateways page
        this.nav.push(ViewPaymentsGatewaysPage, {
            pluginKey: this.payments.membershipPlugin,
            product: plan
        });
    }
}
