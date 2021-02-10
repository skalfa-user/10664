import { BaseApp } from './app.base.component';
import { App as IonicApp, Platform } from 'ionic-angular';
import { TestBed } from '@angular/core/testing';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { InAppPurchase } from '@ionic-native/in-app-purchase';

class AppComponent extends BaseApp {}

// services
import { SecureHttpService, IHttpError } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';
import { PaymentsService } from 'services/payments';
import { AppErrorHandlerService } from 'services/error-handler';

// pages
import { AppNoInternetPage } from 'pages/app-no-internet';
import { AppErrorPage } from 'pages/app-error';
import { LoginPage } from 'pages/user/login';
import { UserDisapprovedPage } from 'pages/user/disapproved';
import { VerifyEmailCheckCodePage } from 'pages/user/verify-email/check-code';
import { CompleteProfilePage } from 'pages/user/complete-profile'
import { CompleteAccountTypePage } from 'pages/user/complete-account-type'
import { AppMaintenancePage } from 'pages/app-maintenance'
import { InitialPaymentsPage } from 'pages/payments/initial'

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    SiteConfigsServiceFake,
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake,
    StringUtilsFake, 
    DeviceFake,
    PersistentStorageMemoryAdapterFake 
} from 'test/fake';

import { AppMock } from 'ionic-mocks';

describe('App base component', () => {
    // register service's fakes
    let fakeApp: IonicApp;
    let fakeAuth: AuthService;
    let fakePayments: PaymentsService;

    let baseApp: BaseApp; // testable service

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
                    provide: SiteConfigsService,
                    useFactory: (fakeHttp) => new SiteConfigsServiceFake(new ReduxFake(), fakeHttp),
                    deps: [SecureHttpService]
                }, {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: Platform,
                    useFactory: () => PlatformMock.instance(),
                    deps: []
                }, {
                    provide: AppErrorHandlerService,
                    useFactory: (fakeHttp) => new AppErrorHandlerService(fakeHttp),
                    deps: [SecureHttpService]
                }, 
                PaymentsService,
                InAppPurchase
            ]}
        );

        fakeApp = AppMock.instance();

        // init service's fakes
        fakeAuth = TestBed.get(AuthService);
        fakePayments = TestBed.get(PaymentsService);

        // init base app
        baseApp = new AppComponent(fakeApp, fakeAuth, fakePayments);
    });

    it('httpErrorHandler should redirect to the no connection page if error code is 0 and there is no internet', () => {
        // fake the method
        spyOn(baseApp, 'isNavigatorOnline').and.returnValue(false);

        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_NO_INTERNET_CONNECTION,
            type: null,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(AppNoInternetPage);
    });

    it('httpErrorHandler should redirect to the app error page if error code is 0 and there is enabled internet', () => {
        // fake the method
        spyOn(baseApp, 'isNavigatorOnline').and.returnValue(true);

        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_NO_INTERNET_CONNECTION,
            type: null,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(AppErrorPage);
    });

    it('httpErrorHandler should redirect to the login page if error code is 401', () => {
        spyOn(fakeAuth, 'logout');

        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_NOT_AUTHORIZED,
            type: null,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(LoginPage);
        expect(fakeAuth.logout).toHaveBeenCalled();
    });

    it('httpErrorHandler should redirect to the user disapproved page if error code is 403 and type is disapproved', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: BaseApp.HTTP_ERROR_FORBIDDEN_DISAPPROVED,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(UserDisapprovedPage, {
            status: 'disapproved',
        });
    });

    it('httpErrorHandler should redirect to the user suspended page if error code is 403 and type is suspended', () => {
        const errorDescription: string = 'error';

        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: BaseApp.HTTP_ERROR_FORBIDDEN_SUSPENDED,
            description: errorDescription,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(UserDisapprovedPage, {
            status: 'suspended',
            description: errorDescription
        });
    });
 
    it('httpErrorHandler should redirect to the user email verification page if error code is 403 and type is not verifying', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: BaseApp.HTTP_ERROR_FORBIDDEN_EMAIL_NOT_VERIFIED,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(VerifyEmailCheckCodePage);
    });

    it('httpErrorHandler should redirect to the user complete profile page if error code is 403 and type is not completed', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: BaseApp.HTTP_ERROR_FORBIDDEN_PROFILE_NOT_COMPLETED,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(CompleteProfilePage);
    });

    it('httpErrorHandler should redirect to the user complete account type page if error code is 403 and type account is not completed', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: BaseApp.HTTP_ERROR_FORBIDDEN_ACCOUNT_TYPE_NOT_COMPLETED,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(CompleteAccountTypePage);
    });

    it('httpErrorHandler should redirect to the maintenance page if error code is 403 and type is maintenance', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: BaseApp.HTTP_ERROR_FORBIDDEN_MAINTENANCE,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(AppMaintenancePage);
    });

    it('httpErrorHandler should redirect to the app error page if error code is 403 and the error type is missing', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: null,
            description: null,
            shortDescription: null
        };

        // fake the method
        spyOn(fakePayments, 'isPaymentsAvailable').and.returnValue(false);

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakePayments.isPaymentsAvailable).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(AppErrorPage);
    });

    it('httpErrorHandler should redirect to the initial payments page if the error code is 403 and the error type is missing', () => {
        const error: IHttpError = {
            code: BaseApp.HTTP_ERROR_FORBIDDEN,
            type: null,
            description: null,
            shortDescription: null
        };

        // fake the methods
        spyOn(fakePayments, 'isPaymentsAvailable').and.returnValue(true);

        baseApp.httpErrorHandler(error);

        expect(fakePayments.isPaymentsAvailable).toHaveBeenCalled();
        expect(fakeApp.getRootNav().push).toHaveBeenCalledWith(InitialPaymentsPage, {
            isShowNotification: true
        });
    });
 
    it('httpErrorHandler should redirect to the app error page if error code is 404, 500', () => {
        const error: IHttpError = {
            code: 500,
            type: null,
            description: null,
            shortDescription: null
        };

        baseApp.httpErrorHandler(error);

        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalled();
        expect(fakeApp.getRootNav().setRoot).toHaveBeenCalledWith(AppErrorPage);
    });
});
