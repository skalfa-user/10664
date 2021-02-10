import { TestBed } from '@angular/core/testing';
import { Platform, Config as AppConfig } from 'ionic-angular';
import { TranslateModule, TranslateService } from 'ng2-translate';
import { Http } from '@angular/http';
import { Observable } from 'rxjs/Rx';

// services
import { I18nService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { AuthService } from 'services/auth';
import { JwtService } from 'services/jwt';

// fakes
import { PlatformMock, ConfigMock } from 'ionic-mocks';

import {
    JwtFake,
    AuthServiceFake,
    ReduxFake,
    ApplicationServiceFake,
    ApplicationConfigFake,
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// responses
import { I18nResponse } from 'services/i18n/responses';

describe('i18n service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;
    let translateFake: TranslateService;
    let fakeApplication: ApplicationService;
    let fakeConfig: AppConfig;
    let fakePlatform: Platform;

    let i18n: I18nService; // testable service

    beforeEach(() => { 
        TestBed.configureTestingModule({
            imports: [
                TranslateModule.forRoot()
            ],
            providers: [
                {
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new DeviceFake, new StringUtilsFake, fakePlatform),
                    deps: [PersistentStorageService, Platform]
                },
                {
                    provide: PersistentStorageService,
                    useFactory: () => new PersistentStorageService(new PersistentStorageMemoryAdapterFake),
                    deps: []
                },
                {
                    provide: Platform,
                    useFactory: () => PlatformMock.instance(),
                    deps: []
                },
                {
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                },
                {
                    provide: AppConfig,
                    useFactory: () => ConfigMock.instance(),
                    deps: []
                },
                {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
                },
                {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                },
                I18nService
            ]}
        );

        // init service's fakes
        fakeApplication = TestBed.get(ApplicationService);
        fakeHttp = TestBed.get(SecureHttpService);
        translateFake = TestBed.get(TranslateService);
        fakeConfig = TestBed.get(AppConfig);
        fakePlatform = TestBed.get(Platform);

        // init i18n service
        i18n = TestBed.get(I18nService);
    });

    it('loadTranslations should init application language', () => {
        const language: string = 'ru';
        const languageDir: string = 'ltr';
        const translations = {
            'test': 'test'
        };

        const response: I18nResponse = {
            dir: languageDir,
            translations: translations
        };

        // fake application
        spyOn(fakeApplication, 'getLanguage').and.returnValue(language);
        spyOn(fakeApplication, 'setLanguageDirection');

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        // fake translation service
        spyOn(translateFake, 'setTranslation');
        spyOn(translateFake, 'use');

        i18n.loadTranslations();

        expect(fakeHttp.get).toHaveBeenCalledWith('/i18n/' + language);

        // translation service
        expect(translateFake.setTranslation).toHaveBeenCalled();
        expect(translateFake.setTranslation).toHaveBeenCalledWith(language, translations); 

        expect(translateFake.use).toHaveBeenCalled();
        expect(translateFake.use).toHaveBeenCalledWith(language);

        // app
        expect(fakeConfig.set).toHaveBeenCalled();

        // platform
        expect(fakePlatform.setDir).toHaveBeenCalled();
        expect(fakePlatform.setDir).toHaveBeenCalledWith(languageDir, true); 
        expect(fakePlatform.setLang).toHaveBeenCalled();
        expect(fakePlatform.setLang).toHaveBeenCalledWith(language, true); 

        // application
        expect(fakeApplication.setLanguageDirection).toHaveBeenCalled();
        expect(fakeApplication.setLanguageDirection).toHaveBeenCalledWith(languageDir);
    });
});
