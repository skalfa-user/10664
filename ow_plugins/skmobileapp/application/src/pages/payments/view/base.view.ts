import { NavParams, ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { IMembershipResponse, IMembershipPlanResponse } from 'services/payments';
import { SiteConfigsService } from 'services/site-configs';

export abstract class BaseViewMembership {
    isPageLoading: boolean = true;
    myMembership: IMembershipResponse;
    membership: IMembershipResponse;
    title: string = '';
    isPlanListVisible: boolean = false;
    isMyMembershipVisible: boolean = false;
    buyingPlanId: number | string;

    protected membershipId: number;

    /**
     * Constructor
     */
    constructor(
        protected translate: TranslateService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected navParams: NavParams)
    {
        this.membershipId = this.navParams.get('membershipId');
        this.isPlanListVisible = this.navParams.get('isPlanListVisible');
        this.myMembership = this.navParams.get('myMembership');
        this.isMyMembershipVisible = this.navParams.get('isMyMembershipVisible');
        this.title = this.navParams.get('title');
    }

    /**
     * Get currency
     */
    get currency(): string {
        return this.siteConfigs.getConfig('billingCurrency');
    }

    /**
     * Page title
     */
    get pageTitle(): string {
        if (this.title) {
            return this.title;
        }

        return this.membership ? this.membership.title : '';
    }

    /**
     * Show notification
     */
    protected showNotification(lang: string, params = {}): void {
        const notificationToaster = this.toast.create({
            message: this.translate.instant(lang, params),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        notificationToaster.present();
    }

    /**
     * Buy product
     */
    abstract buyProduct(plan: IMembershipPlanResponse): void;
}
