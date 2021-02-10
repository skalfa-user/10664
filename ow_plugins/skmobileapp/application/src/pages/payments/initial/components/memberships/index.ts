import { Component, ChangeDetectionStrategy, OnInit, ChangeDetectorRef } from '@angular/core';
import { NavController, ModalController, Platform } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { PaymentsService, IMembershipResponse } from 'services/payments';
import { ApplicationService } from 'services/application';

// pages
import { CustomPageComponent } from 'shared/components/custom-page';
import { ViewMembershipInAppPage } from 'pages/payments/view/inapp';
import { ViewMembershipMobilePage } from 'pages/payments/view/mobile';

@Component({
    selector: 'memberships',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class MembershipsComponent implements OnInit {
    isPageLoading: boolean = true;
    myMembership: IMembershipResponse;
    memberships: Array<IMembershipResponse> = [];

    /**
     * Constructor
     */
    constructor(
        private nav: NavController,
        private platform: Platform,
        private application: ApplicationService,
        private translate: TranslateService,
        private modal: ModalController,
        private payments: PaymentsService,
        private ref: ChangeDetectorRef) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        this.payments.loadMemberships().subscribe(response => {
            // find an active membership
            this.myMembership = response.
                    find((membership: IMembershipResponse) => membership.isActive === true);

            this.memberships = response;
            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Recurring description
     */
    get recurringDescription(): string {
        if (this.application.isAppRunningInExternalBrowser()) {
            return this.translate.instant('app_mobile_recurring_information_description');
        }

        return this.platform.is('ios')
            ? this.translate.instant('app_ios_recurring_information_description')
            : this.translate.instant('app_android_recurring_information_description');
    }

    /**
     * View membership
     */
    viewMembership(id: number): void {
        this.nav.push((this.payments.isMobilePaymentsAvailable() ? ViewMembershipMobilePage : ViewMembershipInAppPage), {
            membershipId: id,
            isPlanListVisible: true,
            isMyMembershipVisible: false,
            myMembership: this.myMembership
        });
    }

    /**
     * View my membership
     */
    viewMyMembership(): void {
        this.nav.push((this.payments.isMobilePaymentsAvailable() ? ViewMembershipMobilePage : ViewMembershipInAppPage), {
            membershipId: this.myMembership.id,
            isPlanListVisible: false,
            isMyMembershipVisible: true,
            myMembership: this.myMembership,
            title: this.myMembership.id ? this.translate.instant('your_membership') : ''
        });
    }

    /**
     * Show privacy policy modal
     */
    showPrivacyPolicyModal(): void {
        let modal = this.modal.create(CustomPageComponent, {
            title: this.translate.instant('privacy_policy_page_header'),
            pageName: 'privacy_policy_page_content'
        });

        modal.present();
    }

    /**
     * Show terms of use modal
     */
    showTermsOfUseModal(): void {
        let modal = this.modal.create(CustomPageComponent, {
            title: this.translate.instant('tos_page_header'),
            pageName: 'tos_page_content'
        });

        modal.present();
    }
}
