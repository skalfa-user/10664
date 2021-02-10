import { TestBed } from '@angular/core/testing';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions, Response, ResponseOptions } from '@angular/http';
import { Observable } from 'rxjs/Rx';

import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { SecureHttpService } from './';
import { Platform } from 'ionic-angular';

// fakes
import { PlatformMock } from 'ionic-mocks';

import { 
    ApplicationServiceFake, 
    ApplicationConfigFake, 
    ReduxFake, 
    PersistentStorageMemoryAdapterFake,
    JwtFake,
    StringUtilsFake,
    DeviceFake,
    AuthServiceFake } from 'test/fake';

describe('Http service', () => {
    // register service's fakes
    let httpFake: Http;
 
    let http: SecureHttpService; // testable service

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

        httpFake = TestBed.get(Http);
        http = TestBed.get(SecureHttpService);
    });
 
    it('validateApiUrl should correct determine an api url', () => {
        const apiDomain: string =  'test.com';
        const response = {
            status: 'ok',
            url: 'http://' + apiDomain
        };
 
        // fake the method
        spyOn(httpFake, 'get').and.returnValue(
            Observable.of(new Response(new ResponseOptions({
                body: JSON.stringify(response)
            })))
        );

        http.validateApiUrl(apiDomain).subscribe(url => {
            expect(url).toEqual('http://' + apiDomain); 
        });
    });

    it('validateApiUrl should return null for wrong api urls', () => {
        // fake the method
        spyOn(httpFake, 'get').and.returnValue(
            Observable.throw('Some error')
        );

        http.validateApiUrl('http://test.com').subscribe(url => {
            expect(url).toBeNull(); 
        });
    });

    it('get should send a correct request', () => {
        const response: string = 'ok';
        const url: string = 'http://test.com';
        const params = {
            a: 'a',
            b: 'b'
        };

        // fake the method
        spyOn(http, 'get').and.returnValue(
            Observable.of(response)
        );

        http.get(url, params).subscribe(httpResponse => {
            expect(http.get).toHaveBeenCalled();
            expect(http.get).toHaveBeenCalledWith(url, params);
            expect(httpResponse).toEqual(response); 
        });
    });

    it('delete should send a correct request', () => {
        const response: string = 'ok';
        const url: string = 'http://test.com';
        const params = {
            a: 'a',
            b: 'b'
        };

        // fake the method
        spyOn(http, 'delete').and.returnValue(
            Observable.of(response)
        );

        http.delete(url, params).subscribe(httpResponse => {
            expect(http.delete).toHaveBeenCalled();
            expect(http.delete).toHaveBeenCalledWith(url, params);
            expect(httpResponse).toEqual(response); 
        });
    });

    it('post should send a correct request', () => {
        const response: string = 'ok';
        const url: string = 'http://test.com';

        const data = {
            message: 'test'
        };

        const params = {
            a: 'a',
            b: 'b'
        };

        // fake the method
        spyOn(http, 'post').and.returnValue(
            Observable.of(response)
        );

        http.post(url, data, params).subscribe(httpResponse => {
            expect(http.post).toHaveBeenCalled();
            expect(http.post).toHaveBeenCalledWith(url, data, params);
            expect(httpResponse).toEqual(response); 
        });
    });

    it('put should send a correct request', () => {
        const response: string = 'ok';
        const url: string = 'http://test.com';

        const data = {
            message: 'test'
        };

        const params = {
            a: 'a',
            b: 'b'
        };

        // fake the method
        spyOn(http, 'put').and.returnValue(
            Observable.of(response)
        );

        http.put(url, data, params).subscribe(httpResponse => {
            expect(http.put).toHaveBeenCalled();
            expect(http.put).toHaveBeenCalledWith(url, data, params);
            expect(httpResponse).toEqual(response); 
        });
    });
});
