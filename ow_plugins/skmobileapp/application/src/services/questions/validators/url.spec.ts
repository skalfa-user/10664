import { TestBed } from '@angular/core/testing';
import { UrlValidator, UrlValidatorFailedResult } from './url';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { FormControl } from '@angular/forms';
import { Platform } from 'ionic-angular';

// services
import { ApplicationService } from 'services/application';
import { SiteConfigsService } from 'services/site-configs';
import { SecureHttpService } from 'services/http';
import { AuthService } from 'services/auth';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';

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
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('Url validator', () => {
    // register service's fakes
    let fakeSiteConfigs: SiteConfigsService; 

    // testable class
    let urlValidator: UrlValidator; 
    let validatorFunction: Function;
    let failedValidation: UrlValidatorFailedResult;

    beforeEach(() => {
        failedValidation = new UrlValidatorFailedResult;

        TestBed.configureTestingModule({
            providers: [{
                    provide: SiteConfigsService,
                    useFactory: (fakeHttp) => new SiteConfigsServiceFake(new ReduxFake(), fakeHttp),
                    deps: [SecureHttpService]
                }, {
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
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                },
            ]}
        );

        // init service's fakes
        fakeSiteConfigs = TestBed.get(SiteConfigsService);
    
        // init validator instance
        urlValidator = new UrlValidator(fakeSiteConfigs);
        validatorFunction = urlValidator.validate();
    });
 
    it('validate should return positive result for an empty url including null', () => {
        expect(validatorFunction(new FormControl(''))).toBeNull();
        expect(validatorFunction(new FormControl(null))).toBeNull();
    });

    it('validate should return negative result for a wrong url', () => {
        expect(validatorFunction(new FormControl('wrong_url'))).toEqual(failedValidation);
    });

    it('validate should return positive result for a correct url', () => {
        expect(validatorFunction(new FormControl('http://test.com?param=1&param2=test'))).toBeNull();
    });

    it('validate should return positive result using custom regular expression', () => {
        // fake site configs
        spyOn(fakeSiteConfigs, 'getConfig').and.returnValue('^[1-9]+$');

        expect(validatorFunction(new FormControl('12345'))).toBeNull();
        expect(validatorFunction(new FormControl('http://test.com'))).toEqual(failedValidation);
    });

});
