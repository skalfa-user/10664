import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, Output, EventEmitter } from '@angular/core';
import { NavController, ToastController, AlertController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { InAppPurchase2, IAPProduct, IAPQueryCallback, IAPError,  } from '@ionic-native/in-app-purchase-2';

// services
import { PaymentsService, ICreditPackResponse } from 'services/payments';
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';

// pages
import { DashboardPage } from 'pages/dashboard';

// base view membership page
import { BaseCreditsComponent } from '../base.credits';

@Component({
    selector: 'inapp-credits',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class InappCreditsComponent extends BaseCreditsComponent implements OnInit {
    @Output() packetBuying = new EventEmitter<any>();
    @Output() packetBuyingCancelled = new EventEmitter<any>();

    buyingPack: ICreditPackResponse;

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

        this.buyingPackId = null;
        this.ref.markForCheck();
        this.packetBuyingCancelled.emit();
        product.finish();
    }.bind(this);

    onProductError: IAPQueryCallback = function (error: IAPError) {
        this.buyingPackId = 0;
        this.ref.markForCheck();
        this.showNotification('purchase_error');
        this.packetBuyingCancelled.emit();
    }.bind(this);

    /**
     * Constructor
     */
    constructor(
        protected alert: AlertController,
        protected nav: NavController,
        protected toast: ToastController,
        protected translate: TranslateService,
        protected siteConfigs: SiteConfigsService,
        private user: UserService,
        private payments: PaymentsService,
        private ref: ChangeDetectorRef,
        private store: InAppPurchase2)
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
        // load credits packs
        this.payments.loadCreditPacks().subscribe(response => {
            // process packs
            if (response.packs && response.packs.length) {
                // get only registered packs from the apple or google store
                this.payments.getRegisteredInAppProducts(response.packs).subscribe(registeredProducts => {
                    // synchronize received packs with registered ones
                    // we should show only existing packs
                    response.packs = this.payments
                        .synchronizeItemsWithInAppProducts(response.packs, registeredProducts);

                    this.creditPacks = response.packs;
                    this.myBalance = response.balance;
                    this.isInfoAvailable = response.isInfoAvailable;
                    this.isPageLoading = false;

                    this.ref.markForCheck();
                });

                return;
            }

            this.creditPacks = response.packs;
            this.myBalance = response.balance;
            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Buy pack
     */
    buyPack(pack: ICreditPackResponse): void {
        this.registerProduct(this.creditPacks);
        this.buyingPackId = pack.definedProductId;
        this.buyingPack = pack;
        this.ref.markForCheck();
        this.packetBuying.emit();

        const product = this.store.get(pack.definedProductId);

        product && product.once('updated', () => {
            this.store.order(pack.definedProductId);
        });
    }

    /**
     * Register product
     */
    registerProduct(items: Array<ICreditPackResponse>) {
        this.store.verbosity = this.store.DEBUG;

        items.forEach(item => {
            this.store.register({
                id: item.definedProductId,
                alias: item.definedProductId,
                type: this.store.CONSUMABLE
            });

            this.configurePurchasing(item.definedProductId);
        });

        this.store.refresh();
    }

    /**
     * Configure purchasing
     */
    configurePurchasing(definedProductId: string) {
        this.store.validator = function (product: IAPProduct) {
            if (!this.buyingPackId) {
                return;
            }

            if (this.buyingPackId == product.id ||
              this.buyingPackId == product.id.toLowerCase() ||
              this.buyingPackId == product.id.toUpperCase()) {
                this.payments.purchaseInapp2Product(product, product.id.toUpperCase()).subscribe(purchaseData => {
                    if (!purchaseData || purchaseData.id < 0) {
                        return;
                    }

                    this.buyingPackId = null;

                    if (purchaseData) {
                        product.finish();

                        product.once('updated', () => {
                            this.nav.setRoot(DashboardPage, {
                                purchase: 1,
                                credits: this.buyingPack.credits
                            });
                        });

                        return;
                    }

                    this.showNotification('purchase_cancelled');
                    this.packetBuyingCancelled.emit();
                    this.ref.markForCheck();
                });
            }
        }.bind(this);

        try {
            this.store.when(definedProductId).approved(this.onProductApproved);
            this.store.when(definedProductId).verified(this.onProductVerified);
            this.store.when(definedProductId).cancelled(this.onProductCancelled);
            this.store.when(definedProductId).error(this.onProductError);

            this.store.ready((resp) =>  {
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
        this.store.off(this.onProductCancelled);
        this.store.off(this.onProductVerified);
        this.store.off(this.onProductError);
    }
}
