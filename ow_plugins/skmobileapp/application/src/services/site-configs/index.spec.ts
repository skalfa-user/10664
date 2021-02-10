import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { IMapType } from 'store/types';
import { Platform } from 'ionic-angular';

// services
import { SiteConfigsService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

// payloads
import { IMapPayload } from 'store/payloads';

// store
import { CONFIGS_SET } from 'store/actions'; 

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake, 
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('Site config service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;

    let siteConfig: SiteConfigsService; // testable service

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

        // init service's fakes
        fakeRedux = new ReduxFake();
        fakeHttp = TestBed.get(SecureHttpService);

        // init application service
        siteConfig = new SiteConfigsService(fakeRedux, fakeHttp);
    });

    it('loadConfigs should init application configs', () => {
        const response: IMapType<any> = {
            test: 'test',
            test2: 'test2'
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        // fake site config service
        spyOn(siteConfig, 'setConfigs');

        siteConfig.loadConfigs();

        expect(fakeHttp.get).toHaveBeenCalledWith('/configs');
        expect(siteConfig.setConfigs).toHaveBeenCalledWith(response);
    });

    it('setConfigs should dispatch CONFIGS_SET action', () => {
        const configList: IMapPayload = {
            '1': 'test'
        };

        const expectedArgs = {
            type: CONFIGS_SET,
            payload: configList
        };

        spyOn(fakeRedux, 'dispatch');
        siteConfig.setConfigs(configList);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchConfig should return a correct config', () => {
        const configName: string = 'testConfig'; 
        const configValue: string = 'test';

        // fake the method
        spyOn(siteConfig, 'watchConfig').and.returnValue(
            Observable.of(configValue)
        );

        siteConfig.watchConfig(configName).subscribe(value => {
            expect(value).toEqual(configValue); 
        });
    });

    it('watchConfigGroup should return a correct config', () => {
        const configName1: string = 'testConfig1';
        const configValue1: string = 'test1';

        const configName2: string = 'testConfig2';
        const configValue2: string = 'test2';

        // fake the method
        spyOn(siteConfig, 'watchConfigGroup').and.returnValue(
            Observable.of([
                configValue1, 
                configValue2
            ])
        );

        siteConfig.watchConfigGroup([
            configName1,
            configName2
        ]).subscribe(changedConfigs => {
            expect(changedConfigs).toEqual([
                configValue1,
                configValue2
            ]); 
        });
    });

    it('watchIsPluginActive should return a boolean value depended from the plugin state', () => {
        const isPluginState: boolean = true;
        const pluginKey: string = 'test';

        // fake the method
        spyOn(siteConfig, 'watchIsPluginActive').and.returnValue(
            Observable.of(isPluginState)
        );

        siteConfig.watchIsPluginActive(pluginKey).subscribe(isActive => {
            expect(isActive).toEqual(isPluginState); 
        });
    });

    it('watchIsTinderSearchMode should return a boolean value depended on search mode', () => {
        const isTinderSearchActivated: boolean = true;

        // fake the method
        spyOn(siteConfig, 'watchIsTinderSearchMode').and.returnValue(
            Observable.of(isTinderSearchActivated)
        );

        siteConfig.watchIsTinderSearchMode().subscribe(isActive => {
            expect(isActive).toEqual(isTinderSearchActivated); 
        });
    });
});
