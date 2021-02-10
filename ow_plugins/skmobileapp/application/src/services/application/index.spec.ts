import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Rx';
import { IApplicationConfig } from 'app/app.config';
import { Platform } from 'ionic-angular';

// payloads
import { IByIdPayload } from 'store/payloads';

// store
import { 
    APPLICATION_SET_LANGUAGE_DIRECTION, 
    APPLICATION_SET_LANGUAGE, 
    APPLICATION_SET_LOCATION, 
    APPLICATION_RESET 
} from 'store/actions'; 

import { IApplicationLocation } from 'store/states'; 
import { IAppState } from 'store';

// services
import { ApplicationService } from 'services/application'
import { PersistentStorageService } from 'services/persistent-storage';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {  
    ReduxFake, 
    ApplicationConfigFake, 
    PersistentStorageMemoryAdapterFake,
    StringUtilsFake,
    DeviceFake
} from 'test/fake';

describe('Application service', () => {

    const API_URL = 'http://test.com';
    const API_URI = '/skmobileapp/api';

    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeApplicationConfig: IApplicationConfig;
    let fakePersistentStorage: PersistentStorageService;
    let fakeDevice: DeviceFake;
    let fakeStringUtils: StringUtilsFake;
    let fakePlatform: Platform;

    let application: ApplicationService; // testable service

    beforeEach(() => {
        // init service's fakes
        fakeApplicationConfig = ApplicationConfigFake;
        fakeRedux = new ReduxFake();
        fakePersistentStorage = new PersistentStorageService(new PersistentStorageMemoryAdapterFake);
        fakeDevice = new DeviceFake();
        fakeStringUtils = new StringUtilsFake;
        fakePlatform = PlatformMock.instance();

        // init application service
        application = new ApplicationService(fakeApplicationConfig, 
                fakeRedux as NgRedux<IAppState>, fakePersistentStorage, fakeDevice, fakeStringUtils, fakePlatform);
    });

    it('setLanguageDirection should dispatch APPLICATION_SET_LANGUAGE_DIRECTION action', () => {
        const direction: string = 'rtl';
        const payload: IByIdPayload = {
            id: direction
        };
 
        const expectedArgs = {
            type: APPLICATION_SET_LANGUAGE_DIRECTION,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        application.setLanguageDirection(direction);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchLanguageDirection should return a correct language direction', () => {
        const direction: string = 'rtl';

        // fake the method
        spyOn(application, 'watchLanguageDirection').and.returnValue(
            Observable.of(direction)
        );

        application.watchLanguageDirection().subscribe(appLanguageDirection => {
            expect(appLanguageDirection).toEqual(direction); 
        });
    });
 
    it('setLanguage should dispatch APPLICATION_SET_LANGUAGE action', () => {
        const language: string = 'ru';
        const payload: IByIdPayload = {
            id: language
        };

        const expectedArgs = {
            type: APPLICATION_SET_LANGUAGE,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        application.setLanguage(language);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchLanguage should return a correct language', () => {
        const language: string = 'ru';

        // fake the method
        spyOn(application, 'watchLanguage').and.returnValue(
            Observable.of(language)
        );

        application.watchLanguage().subscribe(appLanguage => {
            expect(appLanguage).toEqual(language); 
        });
    });
 
    it('setLocation should dispatch APPLICATION_SET_LOCATION action', () => {
        const latitude: number = 42.82;
        const longitude: number = 56.74;

        const expectedArgs = {
            type: APPLICATION_SET_LOCATION,
            payload: {
                latitude: latitude,
                longitude: longitude
            }
        };

        spyOn(fakeRedux, 'dispatch');
        application.setLocation(latitude, longitude);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('resetApplication should dispatch APPLICATION_RESET action', () => {
        const expectedArgs = {
            type: APPLICATION_RESET,
            payload: {}
        };

        spyOn(fakeRedux, 'dispatch');
        application.resetApplication();

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchLocation should return a correct location', () => {
        const location: IApplicationLocation = {
            latitude: '42.82',
            longitude: '56.74'
        };

        // fake the method
        spyOn(application, 'watchLocation').and.returnValue(
            Observable.of(location)
        );

        application.watchLocation().subscribe(appLocation => {
            expect(appLocation).toEqual(location); 
        });
    });

    it('getConfig should return correct values from fakeed config service', () => {
        expect(application.getConfig('id')).toEqual(fakeApplicationConfig.id);
        expect(application.getConfig('version')).toEqual(fakeApplicationConfig.version);
        expect(application.getConfig('name')).toEqual(fakeApplicationConfig.name);
        expect(application.getConfig('description')).toEqual(fakeApplicationConfig.description);
        expect(application.getConfig('authorEmail')).toEqual(fakeApplicationConfig.authorEmail);
        expect(application.getConfig('authorName')).toEqual(fakeApplicationConfig.authorName);
        expect(application.getConfig('authorUrl')).toEqual(fakeApplicationConfig.authorUrl);
        expect(application.getConfig('serverUrl')).toEqual(fakeApplicationConfig.serverUrl);
        expect(application.getConfig('googleProjectNumber')).toEqual(fakeApplicationConfig.googleProjectNumber);
        expect(application.getConfig('playStoreKey')).toEqual(fakeApplicationConfig.playStoreKey);
        expect(application.getConfig('pwaBackgroundColor')).toEqual(fakeApplicationConfig.pwaBackgroundColor);
        expect(application.getConfig('pwaThemeColor')).toEqual(fakeApplicationConfig.pwaThemeColor);
        expect(application.getConfig('pwaIcon')).toEqual(fakeApplicationConfig.pwaIcon);
        expect(application.getConfig('pwaIconSize')).toEqual(fakeApplicationConfig.pwaIconSize);
        expect(application.getConfig('pwaIconType')).toEqual(fakeApplicationConfig.pwaIconType);
        expect(application.getConfig('appleIcon')).toEqual(fakeApplicationConfig.appleIcon);
        expect(application.getConfig('appleIconSize')).toEqual(fakeApplicationConfig.appleIconSize);
        expect(application.getConfig('firebaseApiKey')).toEqual(fakeApplicationConfig.firebaseApiKey);
        expect(application.getConfig('firebaseAuthDomain')).toEqual(fakeApplicationConfig.firebaseAuthDomain);
    });

    it('getGenericApiUrl should get a value from the persistent storage service', () => {
        spyOn(fakePersistentStorage, 'getValue');

        const uri: string = application.getGenericApiUrl();

        expect(fakePersistentStorage.getValue).toHaveBeenCalled();
        expect(fakePersistentStorage.getValue).toHaveBeenCalledWith('server-url');
        expect(uri).toBeUndefined();
    });

    it('setGenericApiUrl should set a url in the persistent storage service', () => {
        spyOn(fakePersistentStorage, 'setValue');

        application.setGenericApiUrl(API_URL);

        expect(fakePersistentStorage.setValue).toHaveBeenCalled();
        expect(fakePersistentStorage.setValue).toHaveBeenCalledWith('server-url', API_URL);
    });

    it('getApiUri should get a base API uri', () => {
        expect(application.getApiUri()).toEqual(API_URI);
    });

    it('getApiUrl should get a complete API url', () => {
        fakeApplicationConfig.serverUrl = API_URL; // mutate application config

        expect(application.getApiUrl()).toEqual(API_URL + API_URI);
    });

    it('isAppRunningInExternalBrowser should return a positive boolean value of the url starts with http', () => {
        // fake the method
        spyOn(application, 'getAppUrl').and.returnValue('http://test.com');

        expect(application.isAppRunningInExternalBrowser()).toBeTruthy();
    });

    it('isAppRunningInExternalBrowser should return a positive boolean value of the url starts with https', () => {
        // fake the method
        spyOn(application, 'getAppUrl').and.returnValue('https://test.com');

        expect(application.isAppRunningInExternalBrowser()).toBeTruthy();
    });

    it('isAppRunningInExternalBrowser should return a negative boolean value if the app running on the dev local host', () => {
        // fake the method
        spyOn(application, 'getAppUrl').and.returnValue('http://localhost:8080');

        expect(application.isAppRunningInExternalBrowser()).toBeFalsy();
    });

    it('isAppRunningInExternalBrowser should return a negative boolean value if the app running on the local host or using the file protocol', () => {
        // fake the method
        spyOn(application, 'getAppUrl').and.returnValue('file://index.html');

        expect(application.isAppRunningInExternalBrowser()).toBeFalsy();
    });

    it('getAppUuid should return a real app id within the native wrapper (ios, android)', () => {
        const uuid: string = 'test';

        fakeDevice.uuid = uuid;

        expect(application.getAppUuid()).toEqual(uuid);
    });

    it('getAppUuid should generate and return a random id when the app works outside the native wrappers (ios, android)', () => {
        const emptyUuid: string = '';
        const randomUuid: string = 'test';

        fakeDevice.uuid = emptyUuid;

        spyOn(fakePersistentStorage, 'getValue').and.returnValue(emptyUuid);
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(randomUuid);
        spyOn(fakePersistentStorage, 'setValue');

        expect(application.getAppUuid()).toEqual(randomUuid);
        expect(fakePersistentStorage.setValue).toHaveBeenCalledWith('app_uuid', randomUuid);
    });

    it('isAppReadyForDownload should return a negative boolean value if app works inside natives either ios or android', () => {
        spyOn(application, 'isAppRunningInExternalBrowser').and.returnValue(false);

        expect(application.isAppReadyForDownload()).toBeFalsy();
    });

    it('isAppReadyForDownload should return a positive boolean value if app works inside a browser and pwa mode is not activated', () => {
        spyOn(application, 'isAppRunningInExternalBrowser').and.returnValue(true);
        spyOn(application, 'isAppRunningInPwaMode').and.returnValue(false);

        expect(application.isAppReadyForDownload()).toBeTruthy();
    });

    it('isAppRunningInPwaMode should return a positive boolean value if there is a GET param "pwa"', () => {
        const spyMethod: any = fakePlatform.getQueryParam;
        spyMethod.and.returnValue(true);

        expect(application.isAppRunningInPwaMode()).toBeTruthy();
    });

    it('isAppRunningInPwaMode should return a negative boolean value if there is a GET param "pwa"', () => {
        const spyMethod: any = fakePlatform.getQueryParam;
        spyMethod.and.returnValue(undefined);

        expect(application.isAppRunningInPwaMode()).toBeFalsy();
    });

    it('isAppRunningInMobileSafari should return a positive boolean value if the app running in the mobile safari', () => {
        const spyMethod = spyOn(application, 'getAppUserAgent');

        const userAgents: Array<string> = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 9_0_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13A452 Safari/601.1',
            'Mozilla/5.0 (iPad; CPU OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F69 Safari/600.1.4',
            'Mozilla/5.0 (iPad; CPU OS 9_3_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13F69 Safari/601.1',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 10_0_1 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/14A403 Safari/602.1'
        ];

        userAgents.forEach(userAgent => {
            spyMethod.and.returnValue(userAgent);
            expect(application.isAppRunningInMobileSafari()).toBeTruthy();
        });
    });

    it('isAppRunningInMobileSafari should return a negative boolean value if the app running not in the mobile safari', () => {
        const spyMethod = spyOn(application, 'getAppUserAgent');

        const userAgents: Array<string> = [
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.1 Safari/605.1.15',
            'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Mobile Safari/537.36'
        ];

        userAgents.forEach(userAgent => {
            spyMethod.and.returnValue(userAgent);
            expect(application.isAppRunningInMobileSafari()).toBeFalsy();
        });
    });

    it('getAppUrlParams should both get hash params', () => {
        const url: string = 'http://test.com?param1=1&param2=2#hash1=12&hash2=13';

        spyOn(application, 'getAppUrl').and.returnValue(url);

        expect(application.getAppUrlParams()).toEqual({
            param1: '1',
            param2: '2',
            hash1: '12',
            hash2: '13'
        });
    });

    it('getAppUrlParams should return an empty object if url without both get and hash params', () => {
        const url: string = 'http://test.com';

        spyOn(application, 'getAppUrl').and.returnValue(url);

        expect(application.getAppUrlParams()).toEqual({});
    });

    it('getAppUrlParams should return get params', () => {
        const url: string = 'http://test.com?param1=1&param2=2';

        spyOn(application, 'getAppUrl').and.returnValue(url);

        expect(application.getAppUrlParams()).toEqual({
            param1: '1',
            param2: '2'
        });
    });

    it('getAppUrlParams should return hash params', () => {
        const url: string = 'http://test.com#hash1=11&hash2=12';

        spyOn(application, 'getAppUrl').and.returnValue(url);

        expect(application.getAppUrlParams()).toEqual({
            hash1: '11',
            hash2: '12'
        });
    });

});