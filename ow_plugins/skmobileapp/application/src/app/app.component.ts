import { Component } from '@angular/core';
import { Platform, App as IonicApp } from 'ionic-angular';
import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { Keyboard } from '@ionic-native/keyboard';
import { TranslateService } from 'ng2-translate';
import { Geolocation, GeolocationOptions, Coordinates } from '@ionic-native/geolocation';
import { firebase } from '@firebase/app';

// services
import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';
import { BootstrapService } from 'services/bootstrap';
import { SecureHttpService } from 'services/http';
import { PaymentsService } from 'services/payments';
import { AdMobService } from 'services/admob';
import { PermissionsService, IPermission } from 'services/permissions';

// pages
import { AppUrlPage } from 'pages/app-url';
import { AppMaintenancePage } from 'pages/app-maintenance';
import { LoginPage } from 'pages/user/login';
import { DashboardPage } from 'pages/dashboard';

import { BaseApp } from './app.base.component';

@Component({
    templateUrl: 'app.html',
    providers: [
        StatusBar,
        SplashScreen,
        Geolocation
    ]
})

export class App extends BaseApp {
    private currentPageName: string;

    /**
     * Constructor
     */
    constructor(
        protected permissions: PermissionsService,
        protected admob: AdMobService,
        protected payments: PaymentsService,
        protected http: SecureHttpService,
        protected ionicApp: IonicApp,
        protected siteConfigs: SiteConfigsService,
        protected bootstrap: BootstrapService,
        protected auth: AuthService,
        protected splashScreen: SplashScreen,
        protected geoLocation: Geolocation,
        protected translate: TranslateService,
        protected application: ApplicationService,
        protected statusBar: StatusBar,
        protected platform: Platform,
        protected keyboard: Keyboard
    ) {
        super(ionicApp, auth, payments);

        this.initializeApp();
    }

    /**
     * Initialize app
     */
    async initializeApp(): Promise<any> {
        await this.platform.ready();

        // set default settings for app
        setTimeout(() => this.statusBar.styleDefault(), 1000); // It's dirty but one-second delay solves white-status-bar issue in iOS 13
        this.keyboard.disableScroll(false);
        this.keyboard.hideKeyboardAccessoryBar(true);

        this.initFirebaseCloudMessaging();
        this.initAppLanguage();

        this.watchHttpErrors();
        this.watchMaintenanceMode();
        this.watchAuth();
        this.watchPagesNavigation();
        this.watchLocation();
        this.watchAdmob();

        // application url is not defined yet
        if (!this.application.getApiUrl()) {
            this.splashScreen.hide();

            this.ionicApp.getRootNav().setRoot(AppUrlPage);

            return;
        }
 
        this.loadDependencies();
    }
 
    /**
     * Watch http errors
     */
    protected watchHttpErrors(): void {
        this.http.httpError$.subscribe(error => {
            this.httpErrorHandler(error);
        });
    }
 
    /**
     * Watch maintenance mode
     */
    protected watchMaintenanceMode(): void {
        this.siteConfigs.watchConfig('maintenanceMode').subscribe(config => {
            if (config === true) {
                this.ionicApp.getRootNav().setRoot(AppMaintenancePage);
            }
        });
    }

    /**
     * Watch admob
     */
    protected watchAdmob(): void {
        // watch is admob enabled or not
        this.siteConfigs.watchConfig('isAdmobEnabled').subscribe(() => {
            this.checkAdmobBannerStatus();
        });

        // watch ads permissions
        this.permissions.watchMe('ads_hide_ads').subscribe(() => {
            this.checkAdmobBannerStatus();
        });

        // watch for logout
        this.auth.watchLogout$.subscribe(() => {
            this.checkAdmobBannerStatus();
        });
    }

    /**
     * Watch auth
     */
    protected watchAuth(): void {
        // check logged user's status
        this.ionicApp.getRootNav().viewWillEnter.subscribe(() => {
            if (this.auth.getUserId() && !this.auth.isAuthenticated()) {
                this.auth.logout();
                this.ionicApp.getRootNav().setRoot(LoginPage);
            }
        });
    }

    /**
     * Watch pages navigation
     */
    protected watchPagesNavigation(): void {
        this.ionicApp.getRootNav().viewDidEnter.subscribe(view => {
            this.currentPageName = view.pageRef().nativeElement.tagName;
            this.checkAdmobBannerStatus();
        });
    }

    /**
     * Watch location
     */
    protected watchLocation(): void {
        const locationOptions: GeolocationOptions = {
            maximumAge: 1000 * 60 * 5,// cache time is 5 minutes
            enableHighAccuracy: false
        };

        const watch = this.geoLocation.watchPosition(locationOptions);

        watch.subscribe(data => {
            const coordinates: Coordinates = data.coords;

            if (coordinates) {
                this.application.setLocation(coordinates.latitude, coordinates.longitude);
            }
        });
    }

    /**
     * Init FCM
     */
    protected initFirebaseCloudMessaging(): void {
        if (this.application.isAppRunningInExternalBrowser()) {
            firebase.initializeApp({
                apiKey: this.application.getConfig('firebaseApiKey'),
                authDomain: this.application.getConfig('firebaseAuthDomain'),
                databaseURL: this.application.getConfig('firebaseDatabaseURL'),
                projectId: this.application.getConfig('firebaseProjectId'),
                storageBucket: this.application.getConfig('firebaseStorageBucket'),
                messagingSenderId: this.application.getConfig('firebaseMessagingSenderId'),
            });
        }
    }

    /**
     * Init app language
     */
    protected initAppLanguage(): void {
        // set  the browser lang
        this.application.setLanguage(this.translate.getBrowserLang());
    }
 
    /**
     * Load dependencies
     */
    protected loadDependencies(): void {
        this.bootstrap.loadDependencies().subscribe(() => {
            this.splashScreen.hide();

            // redirect to the maintenance page
            if (this.siteConfigs.getConfig('maintenanceMode') === true) {
                this.ionicApp.getRootNav().setRoot(AppMaintenancePage);

                return;
            }

            this.ionicApp.getRootNav().setRoot(!this.auth.isAuthenticated() ? LoginPage : DashboardPage);
        }, (error) => {

            throw error;
        }, () => {
            this.splashScreen.hide();
            this.hideSplashScreen();
        });
    }


    protected timer;
    protected attempt = 0;

    protected hideSplashScreen() {
        if (this.timer) {
            return;
        }

        this.timer = setInterval(() => {
            const element = window.document.querySelector('[src="./assets/img/pwa_splash_icon.png"]');

            if (element || this.attempt > 10) {
                this.splashScreen.hide();
                clearInterval(this.timer);
            }

            this.attempt++;
        }, 1000);
    }

    /**
     * Check admob banner status
     */
    protected checkAdmobBannerStatus(): void {
        if (this.admob.isBannerCreated()) {
            const permission: IPermission = this.permissions.getMe('ads_hide_ads');

            if (this.siteConfigs.getConfig('isAdmobEnabled') && (!permission || !permission.isAllowed)) {
                if (this.siteConfigs.getConfig('admobPages').indexOf(this.currentPageName.toLowerCase()) !== -1) {
                    // show a banner
                    this.admob.showBanner().subscribe();

                    return;
                }
            }

            // hide the banner
            this.admob.hideBanner().subscribe();
        }
    }
}
