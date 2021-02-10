import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { TranslateService } from 'ng2-translate';
import { ToastController, NavParams, NavController } from 'ionic-angular';

// services
import { SiteConfigsService } from 'services/site-configs';
import { PaymentsService } from 'services/payments';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';

@Component({
    selector: 'initial-payments',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class InitialPaymentsPage implements OnInit {
    activeComponent: string;
    isBackButtonHidden: boolean = false;

    /**
     * Constructor
     */
    constructor(
        public application: ApplicationService,
        public payments: PaymentsService,
        private ref: ChangeDetectorRef,
        protected nav: NavController,
        protected navParams: NavParams,
        protected siteConfigs: SiteConfigsService,
        protected persistentStorage: PersistentStorageService,
        protected translate: TranslateService,
        protected toast: ToastController) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        // init active component
        this.activeComponent = this.payments.isMembershipAvailable()
            ? this.payments.membershipPlugin
            : this.payments.creditsPlugin;

        // show a denied notification
        if (this.navParams.get('isShowNotification') === true) {
            this.showPermissionsDeniedNotification();
        }

        if (this.navParams.get('isBack') === true) {
            this.persistentStorage.setValue('isBackVideoUpload', true);
        } else {
            this.persistentStorage.removeValue('isBackVideoUpload');
        }
    }

    /**
     * Component loaded
     */
    ionViewDidLoad(): void {
        this.ref.markForCheck();
    }

    /**
     * Hide back button
     */
    hideBackButton(): void {
        this.isBackButtonHidden = true;
        this.ref.markForCheck();
    }

    /**
     * Show back button
     */
    showBackButton(): void {
        this.isBackButtonHidden = false;
        this.ref.markForCheck();
    }
 
    /**
     * Show permissions denied notification
     */
    private showPermissionsDeniedNotification(): void {
        const notificationToaster = this.toast.create({
            message: this.translate.instant('permission_denied_alert_message'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        notificationToaster.present();
    }
}
