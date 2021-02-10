import { NavController, ToastController, AlertController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { ICreditPackResponse } from 'services/payments';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { CreditsInfoPage } from 'pages/payments/credits-info';

export abstract class BaseCreditsComponent {
    isPageLoading: boolean = true;
    creditPacks: Array<ICreditPackResponse> = [];
    myBalance: number = 0;
    isInfoAvailable: boolean = false;
    buyingPackId: number | string;

    /**
     * Constructor
     */
    constructor(
        protected alert: AlertController,
        protected nav: NavController,
        protected toast: ToastController,
        protected translate: TranslateService,
        protected siteConfigs: SiteConfigsService) {}

    /**
     * Get currency
     */
    get currency(): string {
        return this.siteConfigs.getConfig('billingCurrency');
    }

    /**
     * View credits info
     */
    viewCreditsInfo(): void {
        if (!this.isInfoAvailable) {
            const alert = this.alert.create({
                title: this.translate.instant('credits_info_not_available_title'),
                subTitle: this.translate.instant('credits_info_not_available_message'),
                buttons: [this.translate.instant('ok')]
            });

            alert.present();

            return;
        }

        this.nav.push(CreditsInfoPage);
    }

    /**
     * Show notification
     */
    protected showNotification(lang: string, params?: {}): void {
        const notificationToaster = this.toast.create({
            message: this.translate.instant(lang, params),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        notificationToaster.present();
    }
}
