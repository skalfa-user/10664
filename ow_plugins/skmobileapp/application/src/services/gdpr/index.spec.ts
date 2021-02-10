import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';

// services
import { GdprService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { AuthService } from 'services/auth';
import { JwtService } from 'services/jwt';
import { Platform } from 'ionic-angular';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    JwtFake,
    AuthServiceFake,
    ReduxFake,
    ApplicationServiceFake,
    ApplicationConfigFake,
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('Gdpr service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;

    let gdpr: GdprService; // testable service`

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
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                }, {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
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
                },
                GdprService
            ]}
        );

        // init service's fakes
        fakeHttp = TestBed.get(SecureHttpService);

        // init service
        gdpr = TestBed.get(GdprService);
    });

    it('requestUserDataToDownload should return correct result', () => {
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        gdpr.requestUserDataToDownload().subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/gdpr/downloads');
            expect(data).toEqual(response);
        });
    });

    it('requestUserDataToDelete should return correct result', () => {
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        gdpr.requestUserDataToDelete().subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/gdpr/deletions');
            expect(data).toEqual(response);
        });
    });

    it('sendMessageToAdmin should return correct result', () => {
        const response: string = 'ok';
        const message: string = 'test';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        gdpr.sendMessageToAdmin(message).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/gdpr/messages', {
                message: message
            });

            expect(data).toEqual(response);
        });
    });
});
