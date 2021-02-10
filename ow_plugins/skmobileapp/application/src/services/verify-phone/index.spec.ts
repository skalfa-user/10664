import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';

// services
import { VerifyPhoneService, ICountriesResponse, IUserDataResponse, IResendVerifyPhoneResponse, IVerifyPhoneResponse } from './';
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

describe('VerifyPhoneService service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;

    let verifyPhoneService: VerifyPhoneService; // testable service`

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
                VerifyPhoneService
            ]}
        );

        // init service's fakes
        fakeHttp = TestBed.get(SecureHttpService);

        // init service
        verifyPhoneService = TestBed.get(VerifyPhoneService);
    });

    it('loadCountries should return correct result', () => {
        const response: Array<ICountriesResponse> = [{
            phoneCode: 355,
            title: 'Albania'
        },{
            phoneCode: 21,
            title: 'Algeria'
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        verifyPhoneService.loadCountries().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/sms-verifications/countries');
            expect(data).toEqual(response);
        });
    });

    it('loadPhonesMe should return correct result', () => {
        const response: IUserDataResponse = {
            Id: 1,
            id: null,
            code: null,
            country: null,
            countryCode: null,
            isVeryfied: 0,
            number: null,
            userId: 1
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        verifyPhoneService.loadPhonesMe().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/sms-verifications/phones/me');
            expect(data).toEqual(response);
        });
    });

    it('resendVerificationCode should return correct result', () => {
        const countryCode: number = 93;
        const phoneNumber: number = 23456789;

        const response: IResendVerifyPhoneResponse = {
            success: true
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        verifyPhoneService.resendVerificationCode(countryCode, phoneNumber).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/sms-verifications/sms', {
                countryCode: countryCode,
                phoneNumber: phoneNumber
            });
            expect(data).toEqual(response);
        });
    });

    it('verificationCode should return correct result', () => {
        const code: string = 'test';
        const userId: number = 1;

        const response: IVerifyPhoneResponse = {
            valid: true
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        verifyPhoneService.verificationCode(code, userId).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/validators/verify-sms-code', {
                code: code,
                userId: userId
            });

            expect(data).toEqual(response);
        });
    });
});
