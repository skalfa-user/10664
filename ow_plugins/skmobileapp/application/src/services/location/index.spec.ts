import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';

// services
import { LocationService } from './';
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

describe('Location service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;

    let location: LocationService; // testable service

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
                LocationService
            ]}
        );

        // init service's fakes
        fakeHttp = TestBed.get(SecureHttpService);

        // init service
        location = TestBed.get(LocationService);
    });

    it('loadAutocomplete should return correct result', () => {
        const query: string = 'test';
        const response: Array<string> = ['test', 'test2'];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        location.loadAutocomplete(query).subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/location-autocomplete', {
                q: query
            });

            expect(data).toEqual(response);
        });
    });
});
