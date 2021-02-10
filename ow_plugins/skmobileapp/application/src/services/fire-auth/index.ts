import { Injectable } from '@angular/core';
import { AngularFireAuth } from '@angular/fire/auth';
import { auth, User } from 'firebase/app';
import { Observable } from 'rxjs/Observable';
import { ReplaySubject } from 'rxjs/ReplaySubject';
import { Platform } from 'ionic-angular';
import { InAppBrowser } from '@ionic-native/in-app-browser';

export interface IAuthData {
    displayName?: string;
    email?: string;
    phoneNumber?: string;
    photoURL?: string;
    uid: string;
}

// services
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';

// responses
import { ILoginResponse } from './responses';

@Injectable()
export class FireAuthService {
    static readonly TWITTER_PROVIDER: string = 'twitter.com';
    static readonly FACEBOOK_PROVIDER: string = 'facebook.com';
    static readonly GOOGLE_PROVIDER: string = 'google.com';
    static readonly LINKEDIN_PROVIDER: string = 'linkedin.com';
    static readonly INSTAGRAM_PROVIDER: string = 'instagram.com';

    public usedSignInMethod: string = '';

    private customAuthToken: string = null;

    /**
     * Constructor
     */
    constructor(
        private application: ApplicationService,
        private platform: Platform,
        private fireAuth: AngularFireAuth,
        private iab: InAppBrowser,
        private http: SecureHttpService) {}

    /**
     * Get custom providers
     */
    getCustomProviders(): Array<string> {
        return [
            FireAuthService.LINKEDIN_PROVIDER,
            FireAuthService.INSTAGRAM_PROVIDER
        ];
    }

    /**
     * Set custom token
     */
    setCustomToken(providerId: string, token: string) {
        // we only accept tokens related with custom providers
        if (this.getCustomProviders().indexOf(providerId) != -1) {
            this.customAuthToken = token;
        }
    }

    /**
     * Get custom token
     */
    getCustomToken(): string {
        return this.customAuthToken;
    }
 
    /**
     * Get custom auth link
     */
    getCustomAuthLink(provider: string, backUrl: string = ''): string {
        // extract the firebase project id from the fire base auth domain
        const [firebaseProjectId] = this.application.getConfig('firebaseAuthDomain').split('.');
        const [providerName] = provider.split('.');

        return `https://us-central1-${firebaseProjectId}.cloudfunctions.net/${providerName}Redirect?backUrl=${backUrl}`;
    }
 
    /**
     * Show custom provider
     */
    showCustomProvider(provider: string): Observable<any> {
        const providerResult$: ReplaySubject<any> = new ReplaySubject(1);

        if (this.isAuthContextRedirectable()) {
            setTimeout(() => {
                this.redirectCustomProvider(this.getCustomAuthLink(provider, this.application.getAppUrl(false)));

                providerResult$.next(null);
                providerResult$.complete();
            });
        }
        else {
            // open the auth window in the internal browser
            const browser =  this.iab.create(
                    this.getCustomAuthLink(provider), '_blank', 'location=no,toolbar=no,hardwareback=no');

            // find an auth token 
            browser.on('loadstop').subscribe(() => Observable
                .fromPromise(browser.executeScript({ code: 'document.getElementById("firebaseToken").innerText'}))
                .subscribe(token => {
                    if (token[0]) {
                        this.setCustomToken(provider, (token[0] != '__empty__' ? token[0] : ''));
 
                        providerResult$.next(null);
                        providerResult$.complete();

                        browser.close();
                    }
                }));
        }

        return providerResult$;
    }

    /**
     * Redirect custom provider
     */
    redirectCustomProvider(url: string): void {
        window.location.href = url;
    }

    /**
     * Watch auth data
     */
    watchAuthData(): Observable<IAuthData> {
        const authResult$: ReplaySubject<IAuthData> = new ReplaySubject(1);
 
        if (this.getCustomToken()) {
            Observable.fromPromise(this.fireAuth.auth.signInWithCustomToken(this.getCustomToken())).subscribe(() => {
                this.onAuthStateChanged(authResult$);
            });
        }
        else {
            this.onAuthStateChanged(authResult$);
        }

        return authResult$;
    }

    /**
     * Show firebase provider
     */
    showFirebaseProvider(provider: string): Observable<any> {
        let providerInstance = null;

        switch(provider) {
            case FireAuthService.TWITTER_PROVIDER :
                providerInstance = new auth.TwitterAuthProvider();
                break;

            case FireAuthService.FACEBOOK_PROVIDER :
                providerInstance = new auth.FacebookAuthProvider();
                break;

            case FireAuthService.GOOGLE_PROVIDER :
                providerInstance = new auth.GoogleAuthProvider();
                providerInstance.addScope('profile');
                providerInstance.addScope('email');
                break;

            case FireAuthService.LINKEDIN_PROVIDER :
            case FireAuthService.INSTAGRAM_PROVIDER :
                return this.showCustomProvider(provider);

            default:
                throw new TypeError(`Unsupported provider ${provider}`);
        }

        return Observable.fromPromise(this.fireAuth.auth.signInWithRedirect(providerInstance));
    }

    /**
     * Sing out
     */
    signOut(): Observable<any> {
        return Observable.fromPromise(this.fireAuth.auth.signOut());
    }
 
    /**
     * Login
     */
    login(authData: IAuthData, providerId: string): Observable<ILoginResponse> {
        return this.http.post('/firebase/login', {
            ...authData,
            providerId: providerId
        });
    }

    /**
     * Is auth context redirectable
     */
    isAuthContextRedirectable(): boolean {
        return this.application.isAppRunningInExternalBrowser() || this.platform.is('ios');
    }

    /**
     * On auth state changed
     */
    private onAuthStateChanged(authResult$: ReplaySubject<IAuthData>) {
        // subscribe to the firebase auth state source and broadcast it further
        const unsubscribe = this.fireAuth.auth.onAuthStateChanged((user: User) => {
            if (user) {
                const response: IAuthData = {
                    displayName: user.displayName,
                    email: user.email ? user.email : (user.providerData && 
                        user.providerData.length && user.providerData[0].email ? user.providerData[0].email : ''),
                    phoneNumber: user.phoneNumber,
                    photoURL: user.photoURL,
                    uid: user.uid
                };

                this.authResultNext(authResult$, response, unsubscribe);
            }
            else {
                // check redirect result
                this.fireAuth.auth.getRedirectResult().then(() => {
                    // if no redirect operation was called, return null User
                    this.authResultNext(authResult$, null, unsubscribe);
                }).catch(error => {
                    // process error "account exists with different credential error"
                    if (error.code === 'auth/account-exists-with-different-credential') {
                        // get used signInMethod
                        this.usedSignInMethod = '';
                        this.fireAuth.auth.fetchSignInMethodsForEmail(error.email).then(signInMethods => {
                            if (signInMethods && signInMethods.length) {
                                this.usedSignInMethod = signInMethods[0];
                                this.authResultNext(authResult$, null, unsubscribe);
                            }
                        });
                    }
                    else {
                        this.authResultNext(authResult$, null, unsubscribe);
                    }
                });
            }
        });
    }

    private authResultNext (authResult$: ReplaySubject<IAuthData>, next: IAuthData, unsubscribe)
    {
        authResult$.next(next);
        authResult$.complete();

        if (unsubscribe) {
            unsubscribe();
        }
    }
}
