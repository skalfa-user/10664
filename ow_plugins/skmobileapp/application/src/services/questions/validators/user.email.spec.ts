import { TestBed, fakeAsync, tick } from '@angular/core/testing';
import { UserEmailValidator, UserEmailValidatorFailedResult } from './user.email';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { FormControl } from '@angular/forms';
import { Events } from 'ionic-angular';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { ApplicationService } from 'services/application';
import { SiteConfigsService } from 'services/site-configs';
import { SecureHttpService } from 'services/http';
import { AuthService } from 'services/auth';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';

// responses
import { IValidatorResponse } from './responses';

// fakes
import { PlatformMock, EventsMock } from 'ionic-mocks';

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

describe('User email validator', () => {
    // register service's fakes
    let fakeSiteConfigs: SiteConfigsService; 
    let fakeAuth: AuthService;
    let fakeHttp: SecureHttpService;
    let fakeEvents: Events;

    // testable class
    let userEmailValidator: UserEmailValidator; 
    let validatorFunction: Function;
    let failedValidation: UserEmailValidatorFailedResult;

    beforeEach(() => {
        failedValidation = new UserEmailValidatorFailedResult;

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
        fakeAuth = TestBed.get(AuthService);
        fakeSiteConfigs = TestBed.get(SiteConfigsService);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeEvents = EventsMock.instance();

        // init validator instance
        userEmailValidator = new UserEmailValidator(fakeAuth, fakeHttp, fakeSiteConfigs, fakeEvents);
        validatorFunction = userEmailValidator.validate();
    });

    it('validate should return negative result for an empty user email including null', fakeAsync(() => {
        spyOn(userEmailValidator, 'getValidationDelay').and.returnValue(0);
        let validationResult: UserEmailValidatorFailedResult | null;
        
        // empty email
        validatorFunction(new FormControl('')).then(data => {
            validationResult = data;
        });

        tick(0);

        expect(validationResult).toEqual(failedValidation);

        // null email
        validatorFunction(new FormControl(null)).then(data => {
            validationResult = data;
        });

        tick(0);

        expect(validationResult).toEqual(failedValidation);
    }));

    it('validate should return positive result for not registered user emails', fakeAsync(() => {
        let validationResult: UserEmailValidatorFailedResult | null;
        const validatorResponse: IValidatorResponse = {
            valid: true
        };
 
        const email: string = 'not_registered@gmail.com';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(validatorResponse)
        );

        spyOn(userEmailValidator, 'getValidationDelay').and.returnValue(0);

        validatorFunction(new FormControl(email)).then(data => {
            validationResult = data;
        });

        tick(0);

        expect(validationResult).toBeNull();
        expect(fakeHttp.post).toHaveBeenCalledWith('/validators/user-email', {
            email: email
        });
    }));

    it('validate should return negative result for registered user emails', fakeAsync(() => {
        let validationResult: UserEmailValidatorFailedResult | null;
        const validatorResponse: IValidatorResponse = {
            valid: false
        };
 
        const email: string = 'already_registered@gmail.com';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(validatorResponse)
        );

        spyOn(userEmailValidator, 'getValidationDelay').and.returnValue(0);

        validatorFunction(new FormControl(email)).then(data => {
            validationResult = data;
        });

        tick(0);

        expect(validationResult).toEqual(failedValidation);
        expect(fakeHttp.post).toHaveBeenCalledWith('/validators/user-email', {
            email: email
        });
    }));

});
