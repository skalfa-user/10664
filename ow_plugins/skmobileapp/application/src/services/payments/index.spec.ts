import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { InAppPurchase } from '@ionic-native/in-app-purchase';
import { Platform } from 'ionic-angular';

// services
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';
import { AppErrorHandlerService } from 'services/error-handler';

import { 
    PaymentsService, 
    IInappProductData, 
    IMembershipPlanResponse, 
    IInappPurchaseData, 
    ICreditPackResponse, 
    IBillingGatewayResponse,
    IBillingGatewayInfoResponse,
    IPurchaseItemResponse
} from './'

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake, 
    PersistentStorageMemoryAdapterFake,
    StringUtilsFake,
    DeviceFake,
    SiteConfigsServiceFake
} from 'test/fake';

// responses
import { IMembershipResponse, ICreditsResponse, ICreditsInfoResponse } from './responses'; 

describe('Payments service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;
    let fakeInAppPurchase: InAppPurchase;
    let fakePlatform: Platform;
    let fakeErrorHandler: AppErrorHandlerService;

    let payments: PaymentsService; // testable service

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [{
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new DeviceFake, new StringUtilsFake, fakePlatform),
                    deps: [PersistentStorageService, Platform]
                }, {
                    provide: PersistentStorageService,
                    useFactory: () => new PersistentStorageService(new PersistentStorageMemoryAdapterFake),
                    deps: []
                }, {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
                }, {
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                }, {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: SiteConfigsService,
                    useFactory: (fakeHttp) => new SiteConfigsServiceFake(new ReduxFake(), fakeHttp),
                    deps: [SecureHttpService]
                }, {
                    provide: AppErrorHandlerService,
                    useFactory: (fakeHttp) => new AppErrorHandlerService(fakeHttp),
                    deps: [SecureHttpService]
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                },
                PaymentsService,
                InAppPurchase
            ]}
        );

        // init service's fakes
        fakeHttp  = TestBed.get(SecureHttpService);
        fakeInAppPurchase = TestBed.get(InAppPurchase);
        fakePlatform = TestBed.get(Platform);
        fakeErrorHandler = TestBed.get(AppErrorHandlerService);

        // init service
        payments = TestBed.get(PaymentsService);
    });

    it('loadMemberships should return correct result', () => {
        const membershipId: number = 1;

        const response: Array<IMembershipResponse> = [{
            id: membershipId
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadMemberships().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/memberships');
            expect(data).toEqual(response);
        });
    });

    it('loadMembership should return correct result', () => {
        const membershipId: number = 1;

        const response: IMembershipResponse = {
            id: membershipId
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadMembership(membershipId).subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/memberships/' + membershipId);
            expect(data).toEqual(response);
        });
    });

    it('addTrialMembership should return correct result', () => {
        const planId: number = 1;
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        payments.addTrialMembership(planId).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/memberships/trial/' + planId);
            expect(data).toEqual(response);
        });
    });

    it('getRegisteredInAppProducts should return correct result', () => {
        const planId1: number = 1;
        const product1: string = 'membership_1';

        const planId2: number = 2;
        const product2: string = 'membership_2';

        const plans: Array<IMembershipPlanResponse> = [{
            id: planId1,
            productId: product1
        }, {
            id: planId2,
            productId: product2
        }];

        const response: Array<IInappProductData> = [{
            productId: product1,
            title: 'test',
            description: 'test',
            price: '10'
        }];

        // fake inapp purchase
        spyOn(fakeInAppPurchase, 'getProducts').and.returnValue(
            Promise.resolve(response)
        );

        payments.getRegisteredInAppProducts(plans).subscribe(data => {
            expect(fakeInAppPurchase.getProducts).toHaveBeenCalledWith([
                product1,
                product1.toLowerCase(),
                product1.toUpperCase(),
                product2,
                product2.toLowerCase(),
                product2.toUpperCase()
            ]);

            expect(data).toEqual(response);
        });
    });

    it('synchronizeItemsWithInAppProducts should return correct result', () => {
        const planId1: number = 1;
        const product1: string = 'membership_1';
        const plan1: IMembershipPlanResponse = {
            id: planId1,
            productId: product1
        };

        const planId2: number = 2;
        const product2: string = 'membership_2';
        const plan2: IMembershipPlanResponse = {
            id: planId2,
            productId: product2
        };

        const plans: Array<IMembershipPlanResponse> = [
            plan1,
            plan2
        ];

        const inappProducts: Array<IInappProductData> = [{
            productId: product1,
            title: 'test',
            description: 'test',
            price: '10'
        }];

        const newPlans: Array<IMembershipPlanResponse> = payments.synchronizeItemsWithInAppProducts(plans, inappProducts);

        expect(newPlans).toEqual([{
            ...plan1,
            definedProductId: product1
        }]);
    });

    it('synchronizeItemsWithInAppProducts should compare the plans using case insensitive mode', () => {
        const planId1: number = 1;
        const product1: string = 'membership_1';
        const plan1: IMembershipPlanResponse = {
            id: planId1,
            productId: product1
        };

        const planId2: number = 2;
        const product2: string = 'membership_2';
        const plan2: IMembershipPlanResponse = {
            id: planId2,
            productId: product2
        };

        const plans: Array<IMembershipPlanResponse> = [
            plan1,
            plan2
        ];

        const inappProducts: Array<IInappProductData> = [{
            productId: product1.toUpperCase(),
            title: 'test',
            description: 'test',
            price: '10'
        }];

        const newPlans: Array<IMembershipPlanResponse> = payments.synchronizeItemsWithInAppProducts(plans, inappProducts);

        expect(newPlans).toEqual([{
            ...plan1,
            definedProductId: product1.toUpperCase()
        }]);
    });

    it('synchronizeItemsWithInAppProducts should return an empty array if there are no any equal plans', () => {
        const planId: number = 1;
        const product: string = 'membership_11';
        const plan1: IMembershipPlanResponse = {
            id: planId,
            productId: product
        };

        const plans: Array<IMembershipPlanResponse> = [
            plan1
        ];

        const inappProducts: Array<IInappProductData> = [{
            productId: 'membership_1',
            title: 'test',
            description: 'test',
            price: '10'
        }];

        const newPlans: Array<IMembershipPlanResponse> = payments.synchronizeItemsWithInAppProducts(plans, inappProducts);

        expect(newPlans).toEqual([]);
    });

    it('validateInappPurchase should return correct result', () => {
        const platform: string = 'ios';
        const productId: string = 'membership_1';
        const inappPurchase: IInappPurchaseData = {
            transactionId: 'test',
            receipt: 'test',
            signature: 'test',
            productType: 'test'
        };

        const response: string = 'ok';

        // fake platform
        const spyMethod: any = fakePlatform.is;
        spyMethod.and.returnValue(false);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        payments.validateInappPurchase(inappPurchase, productId).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/inapps', {
                platform: platform,
                transactionData: inappPurchase,
                originalProductId: productId
            }, {}, false);

            expect(fakePlatform.is).toHaveBeenCalled();
            expect(data).toEqual(response);
        });
    });

    it('purchaseInappProduct for a recurring plan should return correct result', () => {
        const isRecurring: boolean = true;
        const productId: string = 'membership_1';
        const definedProductId: string = productId.toUpperCase();

        const inappPurchase: IInappPurchaseData = {
            transactionId: 'test',
            receipt: 'test',
            signature: 'test',
            productType: 'test'
        };
 
        const response: string = 'ok';

        // fake inapp purchase
        spyOn(fakeInAppPurchase, 'subscribe').and.returnValue(
            Promise.resolve(inappPurchase)
        );

        spyOn(payments, 'validateInappPurchase').and.returnValue(
            Observable.of(response)
        );

        payments.purchaseInappProduct(definedProductId, productId, isRecurring).subscribe(data => {
            expect(fakeInAppPurchase.subscribe).toHaveBeenCalledWith(productId.toUpperCase());
            expect(payments.validateInappPurchase).toHaveBeenCalledWith(inappPurchase, productId);
            expect(data).toEqual(inappPurchase);
        });
    });

    it('purchaseInappProduct for a not recurring plan should return correct result', () => {
        const isRecurring: boolean = false;
        const productId: string = 'membership_1';
        const definedProductId: string = productId.toUpperCase();

        const inappPurchase: IInappPurchaseData = {
            transactionId: 'test',
            receipt: 'test',
            signature: 'test',
            productType: 'test'
        };
 
        const response: string = 'ok';

        // fake inapp purchases
        spyOn(fakeInAppPurchase, 'buy').and.returnValue(
            Promise.resolve(inappPurchase)
        );

        spyOn(payments, 'consumeInappPurchase').and.returnValue(
            Observable.of('')
        );

        // fake payments
        spyOn(payments, 'validateInappPurchase').and.returnValue(
            Observable.of(response)
        );

        payments.purchaseInappProduct(definedProductId, productId, isRecurring).subscribe(data => {
            expect(fakeInAppPurchase.buy).toHaveBeenCalledWith(productId.toUpperCase());
            expect(payments.consumeInappPurchase).toHaveBeenCalledWith(inappPurchase);
            expect(payments.validateInappPurchase).toHaveBeenCalledWith(inappPurchase, productId);
            expect(data).toEqual(inappPurchase);
        });
    });

    it('purchaseInappProduct should return an empty result if an error occurred in the purchase process', () => {
        const isRecurring: boolean = false;
        const productId: string = 'membership_1';
        const definedProductId: string = productId.toUpperCase();
        const errorResponse: string  = 'Some error';

        // fake inapp purchases
        spyOn(fakeInAppPurchase, 'buy').and.returnValue(
            Promise.reject(errorResponse)
        );

        // fake error handler
        spyOn(fakeErrorHandler, 'handleError');

        payments.purchaseInappProduct(definedProductId, productId, isRecurring).subscribe((data) => {
            expect(fakeInAppPurchase.buy).toHaveBeenCalledWith(productId.toUpperCase());
            expect(fakeErrorHandler.handleError).toHaveBeenCalledWith(errorResponse);
            expect(data).toBeUndefined();
        });
    });

    it('purchaseInappProduct should return an empty result if an error occurred in the consume process', () => {
        const isRecurring: boolean = false;
        const productId: string = 'membership_1';
        const definedProductId: string = productId.toUpperCase();
        const errorResponse: string  = 'Some error';

        const inappPurchase: IInappPurchaseData = {
            transactionId: 'test',
            receipt: 'test',
            signature: 'test',
            productType: 'test'
        };
 
        // fake inapp purchases
        spyOn(fakeInAppPurchase, 'buy').and.returnValue(
            Promise.resolve(inappPurchase)
        );

        // fake payments
        spyOn(payments, 'consumeInappPurchase').and.returnValue(
            Observable.throw(errorResponse)
        );

        // fake error handler
        spyOn(fakeErrorHandler, 'handleError');

        payments.purchaseInappProduct(definedProductId, productId, isRecurring).subscribe((data) => {
            expect(fakeInAppPurchase.buy).toHaveBeenCalledWith(productId.toUpperCase());
            expect(payments.consumeInappPurchase).toHaveBeenCalledWith(inappPurchase);
            expect(fakeErrorHandler.handleError).toHaveBeenCalledWith(errorResponse);
            expect(data).toBeUndefined();
        });
    });

    it('purchaseInappProduct should return an empty result if an error occurred in the validate process', () => {
        const isRecurring: boolean = false;
        const productId: string = 'membership_1';
        const definedProductId: string = productId.toUpperCase();
        const errorResponse: string  = 'Some error';

        const inappPurchase: IInappPurchaseData = {
            transactionId: 'test',
            receipt: 'test',
            signature: 'test',
            productType: 'test'
        };
 
        // fake inapp purchases
        spyOn(fakeInAppPurchase, 'buy').and.returnValue(
            Promise.resolve(inappPurchase)
        );

        // fake payments
        spyOn(payments, 'consumeInappPurchase').and.returnValue(
            Observable.of('')
        );

        spyOn(payments, 'validateInappPurchase').and.returnValue(
            Observable.throw(errorResponse)
        );
 
        // fake error handler
        spyOn(fakeErrorHandler, 'handleError');

        payments.purchaseInappProduct(definedProductId, productId, isRecurring).subscribe((data) => {
            expect(fakeInAppPurchase.buy).toHaveBeenCalledWith(productId.toUpperCase());
            expect(payments.consumeInappPurchase).toHaveBeenCalledWith(inappPurchase);
            expect(payments.validateInappPurchase).toHaveBeenCalledWith(inappPurchase, productId);
            expect(fakeErrorHandler.handleError).toHaveBeenCalledWith(errorResponse);
            expect(data).toBeUndefined();
        });
    });

    it('consumeInappPurchase should return correct result', () => {
        const inappPurchase: IInappPurchaseData = {
            transactionId: 'test',
            receipt: 'test',
            signature: 'test',
            productType: 'test'
        };

        // fake inapp purchase
        spyOn(fakeInAppPurchase, 'consume').and.returnValue(
            Promise.resolve('')
        );

        payments.consumeInappPurchase(inappPurchase).subscribe(data => {
            expect(fakeInAppPurchase.consume).toHaveBeenCalledWith(inappPurchase.productType, inappPurchase.receipt, inappPurchase.signature);
            expect(data).toEqual('');
        });
    });

    it('loadCreditPacks should return correct result', () => {
        const response: ICreditsResponse = {
            isInfoAvailable: false,
            packs: [],
            balance: 0
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadCreditPacks().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/credits');
            expect(data).toEqual(response);
        });
    });

    it('loadCreditsInfo should return correct result', () => {
        const response: ICreditsInfoResponse = {
            losing: [],
            earning: []
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadCreditsInfo().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/credits/info');
            expect(data).toEqual(response);
        });
    });

    it('loadBillingGateways should return correct result', () => {
        const response: Array<IBillingGatewayResponse> = [{
            name: 'test',
            isRedirectable: true,
        }, {
            name: 'test2',
            isRedirectable: false
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadBillingGateways().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/billing-gateways');
            expect(data).toEqual(response);
        });
    });

    it('loadBillingGatewayInfo should return correct result', () => {
        const gatewayKey = 'test';
        const response: IBillingGatewayInfoResponse = {
            options: [],
            formUrl: '',
            questions: []
        }

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadBillingGatewayInfo(gatewayKey).subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/billing-gateways/' + gatewayKey);
            expect(data).toEqual(response);
        });
    });

    it('loadMobilePurchaseSession should return correct result', () => {
        const purchaseId: number  = 1;
        const response: Array<IPurchaseItemResponse> = [{
            key: 'test',
            value: 'test'
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        payments.loadMobilePurchaseSession(purchaseId).subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/mobile-billings/' + purchaseId);
            expect(data).toEqual(response);
        });
    });

    it('initMobilePurchaseSession should return correct result', () => {
        const response: number = 1;
        const gateway: string = 'test';
        const pluginKey: string = 'test';
        const product: ICreditPackResponse = {
            id: 1
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        payments.initMobilePurchaseSession(product, gateway, pluginKey).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/mobile-billings/inits', {
                product: product,
                gatewayKey: gateway,
                pluginKey: pluginKey
            });

            expect(data).toEqual(response);
        });
    });

    it('finishMobilePurchaseSession should return correct result', () => {
        const gateway: string = 'test';
        const saleId: number = 1;
        const purchaseOptions = {
            card: 'test',
            expiration: 'test'
        };

        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        payments.finishMobilePurchaseSession(gateway, saleId, purchaseOptions).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/mobile-billings/finishes', {
                gatewayKey: gateway,
                saleId: saleId,
                ...purchaseOptions
            });

            expect(data).toEqual(response);
        });
    });
});
