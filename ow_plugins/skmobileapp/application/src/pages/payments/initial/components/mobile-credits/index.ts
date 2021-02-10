import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit } from '@angular/core';
import { NavController, ToastController, AlertController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { PaymentsService, ICreditPackResponse } from 'services/payments';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { ViewPaymentsGatewaysPage } from 'pages/payments/gateways/initial';

// base view membership page
import { BaseCreditsComponent } from '../base.credits';

@Component({
    selector: 'mobile-credits',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class MobileCreditsComponent extends BaseCreditsComponent  implements OnInit {
    /**
     * Constructor
     */
    constructor(
        protected alert: AlertController,
        protected nav: NavController,
        protected toast: ToastController,
        protected translate: TranslateService, 
        protected siteConfigs: SiteConfigsService,
        private payments: PaymentsService, 
        private ref: ChangeDetectorRef) 
    {
        super(
            alert,
            nav,
            toast,
            translate,
            siteConfigs
        );
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        this.payments.loadCreditPacks().subscribe(response => {
            this.creditPacks = response.packs;
            this.myBalance = response.balance;
            this.isInfoAvailable = response.isInfoAvailable;

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Open gateways page
     */
    openGatewaysPage(pack: ICreditPackResponse): void {
        // show the gateways page
        this.nav.push(ViewPaymentsGatewaysPage, {
            pluginKey: this.payments.creditsPlugin,
            product: pack
        });
    }
}
