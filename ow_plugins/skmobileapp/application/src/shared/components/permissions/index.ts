import { Component, ChangeDetectionStrategy } from '@angular/core';
import { AlertController, NavController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { PaymentsService } from 'services/payments';

// pages
import { InitialPaymentsPage } from 'pages/payments/initial';

@Component({
    selector: 'permissions',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class PermissionsComponent {
    /**
     * Constructor
     */
    constructor(
        private payments: PaymentsService,
        private nav: NavController,
        private translate: TranslateService,
        private alert: AlertController) {}

    /**
     * Show access denied alert
     */
    showAccessDeniedAlert(isBack: boolean = false): void {
        if (this.payments.isPaymentsAvailable()) {
            const confirm = this.alert.create({
                title: this.translate.instant('permission_denied_alert_title'),
                message: this.translate.instant('permission_denied_alert_message'),
                buttons: [{
                    text: this.translate.instant('cancel')
                }, {
                    text: this.translate.instant('purchase'),
                    handler: () => {
                        this.nav.push(InitialPaymentsPage, {isBack: isBack})
                    }
                }]
            });

            confirm.present();

            return;
        }

        const confirm = this.alert.create({
            title: this.translate.instant('permission_denied_alert_title'),
            buttons: [{
                text: this.translate.instant('cancel')
            }]
        });

        confirm.present();
    }
}
