import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { Device } from '@ionic-native/device';
import { Push, PushOptions } from '@ionic-native/push';
import { Platform } from 'ionic-angular';

// services
import { PushNotificationsService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';

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
    PushObjectFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('Push service', () => {
    // register service's fakes
    let fakePersistentStorage: PersistentStorageService;
    let fakeHttp: SecureHttpService;
    let fakeApplication: ApplicationService;
    let fakeDevice: DeviceFake;
    let fakePushObjectNative: Push;

    let push: PushNotificationsService; // testable service

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [{
                    provide: SiteConfigsService,
                    useFactory: (fakeHttp) => new SiteConfigsServiceFake(new ReduxFake(), fakeHttp),
                    deps: [SecureHttpService]
                }, {
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakeDevice, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, fakeDevice, new StringUtilsFake, fakePlatform),
                    deps: [PersistentStorageService, Device, Platform]
                }, {
                    provide: PersistentStorageService,
                    useFactory: () => new PersistentStorageService(new PersistentStorageMemoryAdapterFake),
                    deps: []
                }, {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
                }, {
                    provide: Device,
                    useFactory: () => new DeviceFake,
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
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                },
                PushNotificationsService,
                Push
            ]}
        );

        // init service's fakes
        fakePersistentStorage = TestBed.get(PersistentStorageService);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeApplication = TestBed.get(ApplicationService);
        fakeDevice = TestBed.get(Device);
        fakePushObjectNative = TestBed.get(Push);

        // init the service
        push = TestBed.get(PushNotificationsService);
    });

    it('initAndroidChannels should correctly initialize android channels', () => {
        const channel: string = 'test';

        // fake the method
        spyOn(fakePushObjectNative, 'createChannel').and.returnValue(
            Promise.resolve('')
        );

        push.setAndroidChannels([channel]);
        push.initAndroidChannels();

        expect(fakePushObjectNative.createChannel).toHaveBeenCalledWith({
            id: channel,
            description: channel,
            importance: 4,
            sound: channel
        });

        expect(fakePushObjectNative.createChannel).toHaveBeenCalledTimes(1);
    });

    it('init should correctly initialize the push object', () => {
        const options: PushOptions = {
            android: {
                icon: 'notification',
                iconColor: 'NULL'
            },
            ios: {
                alert: 'true',
                badge: 'false',
                sound: 'true'
            }
        };

        const pushObject = new PushObjectFake();

        // fake push 
        spyOn(fakePushObjectNative, 'init').and.returnValue(pushObject);
        spyOn(pushObject, 'on').and.returnValue(
            Observable.empty()
        );

        push.init();

        expect(fakePushObjectNative.init).toHaveBeenCalledWith(options);
        expect(pushObject.on).toHaveBeenCalledTimes(2);
    });

    it('registerDevice should retain the received device info', () => {
        const appPlatform: string = 'test';
        const appLanguage: string = 'test';
        const appUuid: string = 'test';
        const pushRegistrationId: string = 'test';

        const device = {
            registrationId: pushRegistrationId
        };

        const response: string = 'ok';

        // fake the app platform
        fakeDevice.platform = appPlatform;

        // fake application 
        spyOn(fakeApplication, 'getAppUuid').and.returnValue(appUuid);
        spyOn(fakeApplication, 'getLanguage').and.returnValue(appLanguage);
 
        // fake the storage
        spyOn(fakePersistentStorage, 'getValue').and.returnValue('');
        spyOn(fakePersistentStorage, 'setValue');

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        const result: boolean = push.registerDevice(device);

        expect(result).toBeTruthy();
        expect(fakePersistentStorage.getValue).toHaveBeenCalled();
        expect(fakeApplication.getAppUuid).toHaveBeenCalled();
        expect(fakeApplication.getLanguage).toHaveBeenCalled();

        expect(fakeHttp.post).toHaveBeenCalledWith('/devices', {
            deviceUuid: appUuid,
            token: pushRegistrationId,
            platform: appPlatform,
            language: appLanguage
        });

        expect(fakePersistentStorage.setValue).toHaveBeenCalledWith('push_token', pushRegistrationId);
    });

    it('registerDevice should not retain the received device info if the device has already registered', () => {
        const pushRegistrationId: string = 'test';

        const device = {
            registrationId: pushRegistrationId
        };

        // fake the storage
        spyOn(fakePersistentStorage, 'getValue').and.returnValue(pushRegistrationId);

        const result: boolean = push.registerDevice(device);

        expect(result).toBeFalsy();
        expect(fakePersistentStorage.getValue).toHaveBeenCalled();
    });
});
