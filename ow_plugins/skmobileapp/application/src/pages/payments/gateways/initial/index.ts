import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit } from '@angular/core';
import { NavParams, NavController } from 'ionic-angular';

// services
import { 
    PaymentsService, 
    IMembershipPlanResponse, 
    ICreditPackResponse, 
    IBillingGatewayResponse 
} from 'services/payments';

import {SiteConfigsService} from "services/site-configs";

//pages
import { RedirectablePaymentsGatewayPage } from 'pages/payments/gateways/redirectable';
import { NotRedirectablePaymentsGatewayPage } from 'pages/payments/gateways/not-redirectable';
import { NotRedirectablePaymentsGatewayPageStripe } from 'pages/payments/gateways/not-redirectable/stripe';

@Component({
    selector: 'view-payments-gateways',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class ViewPaymentsGatewaysPage implements OnInit {
    isPageLoading: boolean = true;
    selectedGateway: string;
    gateways: Array<IBillingGatewayResponse> = [];
    isPurchaseSessionInProcess: boolean = false;

    private product: IMembershipPlanResponse | ICreditPackResponse;
    private pluginKey: string;

    /**
     * Constructor
     */
    constructor(
        private nav: NavController,
        private navParams: NavParams,
        private payments: PaymentsService, 
        private ref: ChangeDetectorRef,
        private siteConfigs: SiteConfigsService,)
    {
        this.product = this.navParams.get('product');
        this.pluginKey = this.navParams.get('pluginKey');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        this.payments.loadBillingGateways().subscribe(response => {
            this.gateways = response;

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Process gateway
     */
    processPayment(gateway: IBillingGatewayResponse): void {
        this.selectedGateway = gateway.name;
        this.isPurchaseSessionInProcess = true;
        this.ref.markForCheck();

        this.payments.initMobilePurchaseSession(this.product, gateway.name, this.pluginKey).subscribe(saleId => {
            this.isPurchaseSessionInProcess = false;
            this.selectedGateway = '';
            this.ref.markForCheck();

            if (gateway.isRedirectable) {
                this.nav.push(RedirectablePaymentsGatewayPage, {
                    saleId: saleId,
                    gatewayKey: gateway.name
                });

                return;
            }

            // gateway Stripe (SCA)
            if (gateway.name === 'billingstripe' && this.siteConfigs.isPluginActive('billingstripe')) {
                this.nav.push(NotRedirectablePaymentsGatewayPageStripe, {
                    saleId: saleId,
                    gatewayKey: gateway.name
                });

                return;
            }

            this.nav.push(NotRedirectablePaymentsGatewayPage, {
                saleId: saleId,
                gatewayKey: gateway.name
            });
        });
    }
}
