import { TestBed, fakeAsync, tick } from '@angular/core/testing';
import { MockBackend } from '@angular/http/testing';
import { Observable } from 'rxjs/Rx';
import { Http, BaseRequestOptions } from '@angular/http';
import { Platform } from 'ionic-angular';
import { AngularFireModule } from 'angularfire2';
import { AngularFireAuthModule } from 'angularfire2/auth';
import { AngularFireAuth, } from '@angular/fire/auth';
import { auth } from 'firebase/app';
import { InAppBrowser } from '@ionic-native/in-app-browser';

// services
import { FireAuthService, IAuthData } from './';
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
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// responses
import { ILoginResponse } from './responses';

describe('Fire auth service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;
    let fakeApplication: ApplicationService;
    let fakePlatform: Platform;
    let fakeAngularFireAuth: AngularFireAuth;
    let fakeInappBrowser: InAppBrowser;

    let firebase: FireAuthService; // testable service

    beforeEach(() => { 
        TestBed.configureTestingModule({
            imports: [
                AngularFireModule.initializeApp({
                    apiKey: 'test',
                    authDomain: 'test'
                }),
                AngularFireAuthModule
            ],
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
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                },
                FireAuthService,
                InAppBrowser
            ]}
        );

        // init service's fakes
        fakeHttp = TestBed.get(SecureHttpService);
        fakeApplication = TestBed.get(ApplicationService);
        fakePlatform = TestBed.get(Platform);
        fakeAngularFireAuth = TestBed.get(AngularFireAuth);
        fakeInappBrowser = TestBed.get(InAppBrowser);

        // init service
        firebase = TestBed.get(FireAuthService);
    });

    it('setCustomToken should avoid setting tokens for non custom providers', () => {
        const provider: string = 'test';
        const token: string = 'test_token';

        // fake the service
        spyOn(firebase, 'getCustomProviders').and.returnValue([]);

        firebase.setCustomToken(provider, token);
        expect(firebase.getCustomToken()).toBeNull();

    });

    it('setCustomToken should allow setting tokens for custom providers', () => {
        const provider: string = 'test';
        const token: string = 'test_token';

        // fake the service
        spyOn(firebase, 'getCustomProviders').and.returnValue([
            provider
        ]);

        firebase.setCustomToken(provider, token);
        expect(firebase.getCustomToken()).toEqual(token);
    });

    it('getCustomAuthLink should return a correct link', () => {
        const provider: string = 'test';
        const firebaseProjectId: string = 'test-project';
        const firebaseAuthDomain: string = `${firebaseProjectId}.firebaseapp.com`;

        // fake services
        spyOn(fakeApplication, 'getConfig').and.returnValue(firebaseAuthDomain);

        expect(firebase.getCustomAuthLink(provider)).toEqual(`https://us-central1-${firebaseProjectId}.cloudfunctions.net/${provider}Redirect?backUrl=`);
    });

    it('watchAuthData should register a custom token before subscribing to the auth data', () => {
        const authToken: string = 'test';
   
        // fake services
        spyOn(firebase, 'getCustomToken').and.returnValue(authToken);
        spyOn(fakeAngularFireAuth.auth, 'signInWithCustomToken').and.returnValue(
            Promise.resolve(null)
        );

        spyOn(fakeAngularFireAuth.auth, 'onAuthStateChanged').and.callFake(callback => {
            callback(null);
        });

        firebase.watchAuthData().subscribe((response) => {
            expect(response).toBeNull();
            expect(fakeAngularFireAuth.auth.signInWithCustomToken).toHaveBeenCalledWith(authToken);
            expect(fakeAngularFireAuth.auth.onAuthStateChanged).toHaveBeenCalled();
        });
    });

    it('watchAuthData should return correct auth data', () => {
        const authData: IAuthData = {
            displayName: 'test',
            email: 'test',
            phoneNumber: 'test',
            photoURL: 'test',
            uid: 'test'
        };

        // fake services
        spyOn(firebase, 'getCustomToken').and.returnValue(null);
        spyOn(fakeAngularFireAuth.auth, 'onAuthStateChanged').and.callFake(callback => {
            callback(authData);
        });

        firebase.watchAuthData().subscribe((response) => {
            expect(response).toEqual(authData);
            expect(fakeAngularFireAuth.auth.onAuthStateChanged).toHaveBeenCalled();
        });
    });

    it('login should return correct result', () => {
        const providerId: string = 'test';
        const remoteId: string = 'test';
        const response: ILoginResponse = {
            isSuccess: true
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        const authData: IAuthData = {
            uid: remoteId
        };

        firebase.login(authData, providerId).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/firebase/login', {
                ...authData,
                providerId: providerId
            });

            expect(data).toEqual(response);
        });
    });

    it('signOut should return correct result', () => {
        // fake services
        spyOn(fakeAngularFireAuth.auth, 'signOut').and.returnValue(
            Promise.resolve(null)
        );

        firebase.signOut().subscribe(() => {
            expect(fakeAngularFireAuth.auth.signOut).toHaveBeenCalled();
        });
    });

    it('isAuthContextRedirectable should return a positive boolean value for all external browsers', () => {
        spyOn(fakeApplication, 'isAppRunningInExternalBrowser').and.returnValue(true);

        expect(firebase.isAuthContextRedirectable()).toBeTruthy();
    });

    it('isAuthContextRedirectable should return a positive boolean value for the ios platform', () => {
        spyOn(fakeApplication, 'isAppRunningInExternalBrowser').and.returnValue(false);

        const spyMethod: any = fakePlatform.is;
        spyMethod.and.returnValue(true);

        expect(firebase.isAuthContextRedirectable()).toBeTruthy();
    });

    it('isAuthContextRedirectable should return a negative boolean value for all internal browsers (web views) and platforms different from ios', () => {
        spyOn(fakeApplication, 'isAppRunningInExternalBrowser').and.returnValue(false);

        const spyMethod: any = fakePlatform.is;
        spyMethod.and.returnValue(false);

        expect(firebase.isAuthContextRedirectable()).toBeFalsy();
    });

    it('showFirebaseProvider should trigger a type error if there is no such provider', () => {
        const provider = 'test';

        expect(() => firebase
            .showFirebaseProvider(provider))
            .toThrow(new TypeError(`Unsupported provider ${provider}`));
    });

    it('showFirebaseProvider should create a facebook provider instance and redirect to the host', () => {
        spyOn(fakeAngularFireAuth.auth, 'signInWithRedirect').and.returnValue(
            Promise.resolve(null)
        );

        firebase.showFirebaseProvider(FireAuthService.FACEBOOK_PROVIDER).subscribe(() => {
            expect(fakeAngularFireAuth.auth.signInWithRedirect).toHaveBeenCalledWith(jasmine.any(auth.FacebookAuthProvider));
        });
    });

    it('showFirebaseProvider should create a twitter provider instance and redirect to the host', () => {
        spyOn(fakeAngularFireAuth.auth, 'signInWithRedirect').and.returnValue(
            Promise.resolve(null)
        );

        firebase.showFirebaseProvider(FireAuthService.TWITTER_PROVIDER).subscribe(() => {
            expect(fakeAngularFireAuth.auth.signInWithRedirect).toHaveBeenCalledWith(jasmine.any(auth.TwitterAuthProvider));
        });
    });

    it('showFirebaseProvider should create a google provider instance and redirect to the host', () => {
        spyOn(fakeAngularFireAuth.auth, 'signInWithRedirect').and.returnValue(
            Promise.resolve(null)
        );

        firebase.showFirebaseProvider(FireAuthService.GOOGLE_PROVIDER).subscribe(() => {
            expect(fakeAngularFireAuth.auth.signInWithRedirect).toHaveBeenCalledWith(jasmine.any(auth.GoogleAuthProvider));
        });
    });

    it('showFirebaseProvider should invoke the showCustomProvider method if the linkedin provider is received', () => {
        spyOn(firebase, 'showCustomProvider').and.returnValue(
            Observable.of(null)
        );

        firebase.showFirebaseProvider(FireAuthService.LINKEDIN_PROVIDER).subscribe(() => {
            expect(firebase.showCustomProvider).toHaveBeenCalledWith(FireAuthService.LINKEDIN_PROVIDER);
        });
    });

    it('showFirebaseProvider should invoke the showCustomProvider method if the instagram provider is received', () => {
        spyOn(firebase, 'showCustomProvider').and.returnValue(
            Observable.of(null)
        );

        firebase.showFirebaseProvider(FireAuthService.INSTAGRAM_PROVIDER).subscribe(() => {
            expect(firebase.showCustomProvider).toHaveBeenCalledWith(FireAuthService.INSTAGRAM_PROVIDER);
        });
    });

    it('showCustomProvider should redirect if the current context is redirectable', fakeAsync(() => {
        const authLink: string = 'test.com';
        const provider: string = 'test';

        // fake services
        spyOn(firebase, 'isAuthContextRedirectable').and.returnValue(true);
        spyOn(firebase, 'redirectCustomProvider');
        spyOn(firebase, 'getCustomAuthLink').and.returnValue(authLink);
        spyOn(fakeApplication, 'getAppUrl').and.returnValue('');

        firebase.showCustomProvider(provider).subscribe(() => {
            expect(firebase.isAuthContextRedirectable).toHaveBeenCalled();
            expect(firebase.redirectCustomProvider).toHaveBeenCalledWith(authLink);
            expect(firebase.getCustomAuthLink).toHaveBeenCalled();
            expect(fakeApplication.getAppUrl).toHaveBeenCalled();
        });

        tick(0);
    }));

    it('showCustomProvider should open the internal inapp browser if context is not redirectable', () => {
        const token: string = 'test_token';
        const provider: string = 'test';

        // fake services
        spyOn(firebase, 'isAuthContextRedirectable').and.returnValue(false);
        spyOn(firebase, 'setCustomToken');
        spyOn(fakeInappBrowser, 'create').and.returnValue({
            on: () =>  Observable.of(null),
            executeScript: () => Promise.resolve([token]),
            close: ()=> {}
        });

        firebase.showCustomProvider(provider).subscribe(() => {
            expect(firebase.setCustomToken).toHaveBeenCalledWith(provider, token);
        });
    });

    it('showCustomProvider should return empty access token in the internal inapp browser if the token is empty or equals to __empty__ string', () => {
        const token: string = '__empty__';
        const provider: string = 'test';

        // fake services
        spyOn(firebase, 'isAuthContextRedirectable').and.returnValue(false);
        spyOn(firebase, 'setCustomToken');
        spyOn(fakeInappBrowser, 'create').and.returnValue({
            on: () =>  Observable.of(null),
            executeScript: () => Promise.resolve([token]),
            close: ()=> {}
        });

        firebase.showCustomProvider(provider).subscribe(() => {
            expect(firebase.setCustomToken).toHaveBeenCalledWith(provider, '');
        });
    });
});
