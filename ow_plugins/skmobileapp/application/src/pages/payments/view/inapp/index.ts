import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { NavParams, NavController, ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { InAppPurchase2, IAPProduct, IAPQueryCallback, IAPError,  } from '@ionic-native/in-app-purchase-2';

// services
import { PaymentsService, IMembershipPlanResponse } from 'services/payments';
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';

// pages
import { DashboardPage } from 'pages/dashboard';

// base view membership page
import { BaseViewMembership } from '../base.view';

@Component({
    selector: 'view-membership-inapp',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class ViewMembershipInAppPage extends BaseViewMembership implements OnInit {
    onProductApproved: IAPQueryCallback = function (product: IAPProduct) {
        product.verify();
    }.bind(this);

    onProductVerified: IAPQueryCallback = function (product: IAPProduct) {
        product.finish();
    }.bind(this);

    onProductCancelled: IAPQueryCallback = function (product: IAPProduct) {
        if (this.buyingPlanId) {
            this.nav.setRoot(DashboardPage, {
                fail: 1,
            });
        }

        this.buyingPlanId = null;
        this.ref.markForCheck();
        product.finish();
    }.bind(this);

    onProductError: IAPQueryCallback = function (error: IAPError) {
        if (this.buyingPlanId) {
            this.showNotification('purchase_error');
        }

        this.buyingPlanId = null;
        this.ref.markForCheck();
    }.bind(this);

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
        private payments: PaymentsService,
        private store: InAppPurchase2)
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
        this.payments.loadMembership(this.membershipId).subscribe(membership => {
            // process plans
            if (membership.plans && membership.plans.length) {
                // get only registered plans from the apple or google store
                this.payments.getRegisteredInAppProducts(membership.plans).subscribe(registeredProducts => {
                    // synchronize received plans with registered ones
                    // we should show only existing plans
                    membership.plans = this.payments
                        .synchronizeItemsWithInAppProducts(membership.plans, registeredProducts);

                    this.membership = membership;
                    this.isPageLoading = false;
                    this.ref.markForCheck();
                });

                return;
            }

            this.membership = membership;
            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Buy product
     */
    buyProduct(plan: IMembershipPlanResponse): void {
        this.buyingPlanId = plan.definedProductId;

        this.registerProduct(this.membership.plans);

        const product = this.store.get(this.buyingPlanId);

        product && product.once('updated', () => {
            this.store.order(plan.definedProductId);
        });

        this.ref.markForCheck();
    }

    registerProduct(items: Array<IMembershipPlanResponse>) {
        this.store.verbosity = this.store.DEBUG;

        items.forEach(item => {
            let isRecurring = item.isRecurring;

            this.store.register({
                id: item.definedProductId,
                alias: item.definedProductId,
                type: isRecurring ? this.store.PAID_SUBSCRIPTION : this.store.CONSUMABLE
            });

            this.configurePurchasing(item.definedProductId);
        });

        this.store.refresh();
    }

    configurePurchasing(definedProductId: string) {
        this.store.validator = function (product: IAPProduct) {
            if (!this.buyingPlanId) {
                return;
            }

            if (this.buyingPlanId == product.id ||
                this.buyingPlanId == product.id.toLowerCase() ||
                this.buyingPlanId == product.id.toUpperCase()) {
                this.payments.purchaseInapp2Product(product, product.id.toUpperCase()).subscribe((purchaseData) => {
                    if (!purchaseData || purchaseData.id < 0) {
                        return;
                    }

                    this.buyingPlanId = null;
                    product.finish();
                    product.once('updated', () => {
                        this.nav.setRoot(DashboardPage, {
                            purchase: 1
                        });
                    });
                });
            }
        }.bind(this);

        try {
            this.store.when(definedProductId).approved(this.onProductApproved);
            this.store.when(definedProductId).verified(this.onProductVerified);
            this.store.when(definedProductId).cancelled(this.onProductCancelled);
            this.store.when(definedProductId).error(this.onProductError);

            this.store.ready(() =>  {
                console.log('Store is ready');
            });
        } catch (err) {
            console.log('Error On Store Issues');
            console.log(err);
        }
    }


    /**
     * Component destroy
     */
    ngOnDestroy() {
      this.store.off(this.onProductApproved);
      this.store.off(this.onProductVerified);
      this.store.off(this.onProductCancelled);
      this.store.off(this.onProductError);
    }
}
