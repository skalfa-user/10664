import { TestBed } from '@angular/core/testing';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { AuthService } from 'services/auth';
import { SecureHttpService } from 'services/http';
import { JwtService } from 'services/jwt';
import { PersistentStorageService } from 'services/persistent-storage';
import { ApplicationService } from 'services/application';
import { AppErrorHandlerService } from './';

// fakes
import { PlatformMock } from 'ionic-mocks';

import { 
    AuthServiceFake, 
    JwtFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake, 
    ReduxFake, 
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('App error service', () => {
    // register fakes
    let fakeHttp: Http;
 
    let appErrorHander: AppErrorHandlerService; // testable service

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
                    provide: AppErrorHandlerService,
                    useFactory: (fakeHttp) => new AppErrorHandlerService(fakeHttp),
                    deps: [SecureHttpService]
                }, {
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                }, {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }
            ]}
        );

        appErrorHander = TestBed.get(AppErrorHandlerService);
        fakeHttp = TestBed.get(SecureHttpService);
    });

    it('handleError should send an error to the server api in production mode', () => {
        const errorMessage: string = 'error';

        // fake functions
        spyOn(appErrorHander, 'isDevMode').and.returnValue(false);
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(null)
        );

        appErrorHander.handleError(errorMessage);

        expect(fakeHttp.post).toHaveBeenCalled();
    });

    it('handleError should show an error via console error in dev mode', () => {
        const errorMessage: string = 'error';

        // fake functions
        spyOn(appErrorHander, 'isDevMode').and.returnValue(true);
        spyOn(console, 'error');

        appErrorHander.handleError(errorMessage);

        expect(console.error).toHaveBeenCalledWith(errorMessage);
    });
});
