import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { InAppPurchase } from '@ionic-native/in-app-purchase';
import { Platform } from 'ionic-angular';
import { Subject } from 'rxjs/Subject';
import { IAPProduct } from '@ionic-native/in-app-purchase-2';

// services
import { SiteConfigsService } from 'services/site-configs';
import { ApplicationService } from 'services/application';
import { SecureHttpService } from 'services/http';
import { AppErrorHandlerService } from 'services/error-handler';

// responses
import { 
    IMembershipResponse, 
    IMembershipPlanResponse, 
    ICreditsResponse,
    ICreditsInfoResponse,
    ICreditPackResponse,
    IBillingGatewayResponse,
    IBillingGatewayInfoResponse,
    IPurchaseItemResponse
} from './responses';

export { 
    IMembershipResponse, 
    IMembershipPlanResponse,
    ICreditsResponse,
    ICreditPackResponse,
    ICreditsInfoResponse, 
    IBillingGatewayResponse,
    IBillingGatewayInfoResponse,
    IPurchaseItemResponse
} from './responses';

export interface IInappProductData {
    productId: string;
    title: string;
    description: string;
    price: string;
}

export interface IInappPurchaseData {
    transactionId: string;
    receipt: string;
    signature: string;
    productType: string;
}

@Injectable()
export class PaymentsService {
    membershipPlugin: string = 'membership';
    creditsPlugin: string = 'usercredits';

    /**
     * Constructor
     */
    constructor (
        private appErrorHandler: AppErrorHandlerService,
        private platform: Platform,
        private inapp: InAppPurchase,
        private application: ApplicationService,
        private siteConfigs: SiteConfigsService,
        private http: SecureHttpService) {}

    /**
     * Validate inapp purchase
     */
    validateInappPurchase(purchaseData?: IInappPurchaseData, productId?: string): Observable<any> {
        return this.http.post('/inapps', {
            platform: this.platform.is('android') ? 'android' : 'ios',
            transactionData: purchaseData,
            originalProductId: productId
        }, {}, false);
    }
 
    /**
     * Consume inapp purchase (mark the purchase as renewable)
     */
    consumeInappPurchase(purchaseData: IInappPurchaseData): Observable<any> {
        return Observable.fromPromise(this.inapp.
            consume(purchaseData.productType, purchaseData.receipt, purchaseData.signature));
    }

    /**
     * Purchase inapp2 product
     */
    purchaseInapp2Product(product: IAPProduct, originalProductId: string): Observable<IInappPurchaseData>  {
        const purchaseResult$: Subject<IInappPurchaseData> = new Subject();
        let purchaseData: any;

        if (this.platform.is('android')) {
            purchaseData = product.transaction;
        }
        else {
            purchaseData = {
                transactionId: product.transaction.id,
                receipt: product.transaction.appStoreReceipt
            };
        }

        // validate the purchase on the server
        this.validateInappPurchase(purchaseData, originalProductId).subscribe(() => {
            purchaseResult$.next(purchaseData);
        }, (e) => {
            this.appErrorHandler.handleError(e);
            purchaseResult$.next();
        });

        return purchaseResult$;
    }

    /**
     * Purchase inapp product
     */
    purchaseInappProduct(definedProductId: string, originalProductId: string, isRecurring: boolean): Observable<IInappPurchaseData>  {
        const purchaseResult$: Subject<IInappPurchaseData> = new Subject();

        const purchase$ = isRecurring
            ? Observable.fromPromise(this.inapp.subscribe(definedProductId))
            : Observable.fromPromise(this.inapp.buy(definedProductId))

        purchase$.subscribe(purchaseData => {
            // mark the purchase as renewable (it's really works only for android)
            if (!isRecurring) {
                // mark the purchase as renewable (it's really works only for android)
                this.consumeInappPurchase(purchaseData).subscribe(() => {
                    // validate the purchase on the server
                    this.validateInappPurchase(purchaseData, originalProductId).subscribe(() => {
                        purchaseResult$.next(purchaseData);
                    }, (e) => {
                        this.appErrorHandler.handleError(e);
                        purchaseResult$.next();
                    });
                }, (e) => {
                    this.appErrorHandler.handleError(e);
                    purchaseResult$.next();
                });

                return;
            }

            // validate the purchase on the server
            this.validateInappPurchase(purchaseData, originalProductId).subscribe(() => {
                purchaseResult$.next(purchaseData);
            }, (e) => {
                this.appErrorHandler.handleError(e);
                purchaseResult$.next();
            });
        },  (e) => {
            this.appErrorHandler.handleError(e);
            purchaseResult$.next();
        });

        return purchaseResult$;
    }

    /**
     * Synchronize membership plans or credits packs with inapp products
     */
    synchronizeItemsWithInAppProducts(items: Array<IMembershipPlanResponse | ICreditPackResponse>, inappProducts: Array<IInappProductData>): Array<IMembershipPlanResponse | ICreditPackResponse> {
        // return only item that registered either google or apple store
        return items.filter(item => {
            for (let inAppKey in inappProducts) {
                const inappProductRegexp: RegExp = new RegExp('^' + inappProducts[inAppKey].productId + '$', 'i');

                if (inappProductRegexp.test(item.productId)) {
                    item.definedProductId = inappProducts[inAppKey].productId;
    
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Get registered inapp products
     */
    getRegisteredInAppProducts(items: Array<IMembershipPlanResponse | ICreditPackResponse>): Observable<Array<IInappProductData>> {
        const ids: Array<string> = [];

        items.forEach(item => {
            // try to find the product using both upper and lower cases (both apple and google store products ids using different ways)
            ids.push(item.productId);
            ids.push(item.productId.toLowerCase());
            ids.push(item.productId.toUpperCase());
        });

        return Observable.fromPromise(this.inapp.getProducts(ids));
    }

    /**
     * Is payments available
     */
    isPaymentsAvailable(): boolean {
        return this.siteConfigs.isPluginActive([
            this.membershipPlugin, 
            this.creditsPlugin
        ]);
    }

    /**
     * Is credits available
     */
    isCreditsAvailable(): boolean {
        return this.siteConfigs.isPluginActive(this.creditsPlugin);
    }

    /**
     * Is membership available
     */
    isMembershipAvailable(): boolean {
        return this.siteConfigs.isPluginActive(this.membershipPlugin);
    }

    /**
     * Is inapp payments available
     */
    isInapPaymentsAvailable(): boolean {
        return !this.application.isAppRunningInExternalBrowser();
    }

    /**
     * Is mobile payments available
     */
    isMobilePaymentsAvailable(): boolean {
        return this.application.isAppRunningInExternalBrowser();
    }

    /**
     * Load credit packs
     */
    loadCreditPacks(): Observable<ICreditsResponse> {
        return this.http.get('/credits');
    }

    /**
     * Load credits info
     */
    loadCreditsInfo(): Observable<ICreditsInfoResponse> {
        return this.http.get('/credits/info');
    }

    /**
     * Load memberships
     */
    loadMemberships(): Observable<Array<IMembershipResponse>> {
        return this.http.get('/memberships');
    }

    /**
     * Load membership
     */
    loadMembership(id: number): Observable<IMembershipResponse> {
        return this.http.get('/memberships/' + id);
    }

    /**
     * Add trial membership
     */
    addTrialMembership(planId: number): Observable<any> {
        return this.http.post('/memberships/trial/' + planId);
    }

    /**
     * Load billing gateways
     */
    loadBillingGateways(): Observable<Array<IBillingGatewayResponse>> {
        return this.http.get('/billing-gateways');
    }

    /**
     * Load billing gateway info
     */
    loadBillingGatewayInfo(id: string): Observable<IBillingGatewayInfoResponse> {
        return this.http.get('/billing-gateways/' + id);
    }

    /**
     * Init a mobile purchase session
     */
    initMobilePurchaseSession(product: IMembershipPlanResponse | ICreditPackResponse, gateway: string, pluginKey: string): Observable<number> {
        return this.http.post('/mobile-billings/inits', {
            product: product,
            gatewayKey: gateway,
            pluginKey: pluginKey
        });
    }

    /**
     * Load a mobile purchase session
     */
    loadMobilePurchaseSession(id: number): Observable<Array<IPurchaseItemResponse>> {
        return this.http.get('/mobile-billings/' + id);
    }

    /**
    * Process a mobile purchase session
    */
    processMobilePurchaseSession(gateway: string, saleId: number, purchaseOptions: Object): Observable<any> {
        return this.http.post('/mobile-billings/process', {
            gatewayKey: gateway,
            saleId: saleId,
            ...purchaseOptions
        });
    }

    /**
     * Finish a mobile purchase session
     */
    finishMobilePurchaseSession(gateway: string, saleId: number, purchaseOptions: Object): Observable<any> {
        return this.http.post('/mobile-billings/finishes', {
            gatewayKey: gateway,
            saleId: saleId,
            ...purchaseOptions
        });
    }
}
