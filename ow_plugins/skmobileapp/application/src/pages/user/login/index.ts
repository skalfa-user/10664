import { Component, Input, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { ToastController, AlertController, NavController, Modal, ModalController, LoadingController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { Observable } from 'rxjs/Observable';

// service
import { UserService } from 'services/user';
import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';
import { FireAuthService } from 'services/fire-auth';
import { PersistentStorageService } from 'services/persistent-storage';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

// pages
import { BaseFormBasedPage } from 'pages/base.form.based'
import { AppUrlPage } from 'pages/app-url';
import { DashboardPage } from 'pages/dashboard';
import { JoinInitialPage } from 'pages/user/join/initial';
import { ForgotPasswordCheckEmailPage } from 'pages/user/forgot-password/check-email';

// components
import { AuthProvidersComponent } from './components/auth-providers';

@Component({
    selector: 'login',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager,
        FireAuthService
    ]
})

export class LoginPage extends BaseFormBasedPage implements OnInit, OnDestroy {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    form: FormGroup;
    loginInProcessing: boolean = false;  
    forgotPasswordPage = ForgotPasswordCheckEmailPage;
    joinPage = JoinInitialPage;
    appUrlPage = AppUrlPage;

    authProviders$: Observable<Array<string>>;

    private firebaseAuthProvidersModal: Modal;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public toast: ToastController,
        private ref: ChangeDetectorRef,
        private user: UserService,
        private modal: ModalController,
        private auth: AuthService,
        private application: ApplicationService,
        private nav: NavController,
        private alert: AlertController,
        private loadingCtrl: LoadingController,
        private persistentStorage: PersistentStorageService,
        private fireAuth: FireAuthService,
        private questionManager: QuestionManager) 
    {
        super(questionControl, siteConfigs, translate, toast);
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // init firebase auth
        if (this.fireAuth.isAuthContextRedirectable() 
                && this.persistentStorage.getValue('firebase_authenticate')) {

            this.initFirebaseAuth(this.persistentStorage.getValue('firebase_provider'));

            this.persistentStorage.setValue('firebase_authenticate', false);
            this.persistentStorage.setValue('firebase_provider', '');
        }

        // init watchers
        this.authProviders$ = this.siteConfigs.watchConfig('authProviders');

        const isDemoModeActivated: boolean = this.siteConfigs.getConfig('isDemoModeActivated');
 
        // create form items
        this.questions = [
            this.questionManager.getQuestion(QuestionManager.TYPE_TEXT, {
                key: 'login',
                placeholder: this.translate.instant('login_input'),
                value: isDemoModeActivated ? 'demo' : '',
                validators: [
                    {name: 'require'}
                ]
            }, {
                questionClass: 'sk-name',
                hideWarning: true
            }),
            this.questionManager.getQuestion(QuestionManager.TYPE_PASSWORD, {
                key: 'password',
                placeholder: this.translate.instant('password_input'),
                value: isDemoModeActivated ? 'demo' : '',
                validators: [
                    {name: 'require'}
                ]
            }, {
                questionClass: 'sk-password',
                hideWarning: true
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions);
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        // close the firebase modal window
        if (this.firebaseAuthProvidersModal) {
            this.firebaseAuthProvidersModal.dismiss();
        }
    }

    /**
     * Is generic site url
     */
    get isGenericSiteUrl(): boolean {
        if (this.application.getGenericApiUrl()) {
            return true;
        }

        return false;
    }
 
    /**
     * Login
     */
    login(): void {
        this.loginInProcessing = true;
        this.ref.markForCheck();

        this.user.login(this.form.value.login,
                this.form.value.password).subscribe(data => {

            this.loginInProcessing = false;
            this.ref.markForCheck();

            if (data.success === true) {
                this.auth.setAuthenticated(data.token);
                this.nav.setRoot(DashboardPage);

                return;
            }

            this.showAlert(this.translate.instant('login_failed'));
        });
    }

    /**
     * Is firebase long provider list
     */
    get isFirebaseLongProviderList(): boolean {
        const providers = this.siteConfigs.getConfig('authProviders');
        
        if (providers && providers.length > this.siteConfigs.getConfig('maxDisplayedAuthProviders')) {
            return true;
        }

        return false;
    }

    /**
     * Init firebase auth
     */
    initFirebaseAuth(providerId: string): void {
        // show a loading window
        const loading = this.loadingCtrl.create({
            content: this.translate.instant('firebaseauth_authenticate'),
            dismissOnPageChange: true
        });

        loading.present();

        const appParams = this.application.getAppUrlParams();

        // set custom auth token
        if (appParams['token']) {
            this.fireAuth.setCustomToken(providerId, appParams['token']);
        }

        // get auth data
        this.fireAuth.watchAuthData().subscribe(user => {
            // try to authenticate user in the app using the auth data
            if (user) {
                this.fireAuth.login(user, providerId).subscribe(response => {
                    // the user successfully logged in
                    if (response && response.isSuccess === true) {
                        this.auth.setAuthenticated(response.token);

                        // reload the app
                        window.location.href = this.application.getAppUrl(false);

                        return;
                    }

                    this.showNotification('firebaseauth_authenticate_error');
                    loading.dismiss();
                });

                return;
            }

            // auth data is missing
            this.fireAuth.usedSignInMethod
                ? this.showNotification('firebaseauth_error_account_exists', {'usedSignInMethod': this.fireAuth.usedSignInMethod})
                : this.showNotification('firebaseauth_authenticate_error');

            loading.dismiss();
        });
    }

    /**
     * Show firebase auth provider
     */
    showFirebaseProvider(provider: string) {
        // set an auth flag (we need it for auth data fetching when the page will be redirected)
        if (this.fireAuth.isAuthContextRedirectable()) {
            this.persistentStorage.setValue('firebase_authenticate', true);
            this.persistentStorage.setValue('firebase_provider', provider);

            // show a loading window
            const loading = this.loadingCtrl.create({
                content: this.translate.instant('firebaseauth_authenticate')
            });

            loading.present();
        }
 
        this.fireAuth.signOut().subscribe(() => this.fireAuth.showFirebaseProvider(provider).subscribe(() => {
            // try to authenticate user (we are ready to fetch the auth data) 
            if (!this.fireAuth.isAuthContextRedirectable()) {
                this.initFirebaseAuth(provider);
            }
        }));
    }

    /**
     * Show firebase providers modal
     */
    showFirebaseProvidersModal(): void {
        this.firebaseAuthProvidersModal = this.modal.create(AuthProvidersComponent);

        // capture the returned data
        this.firebaseAuthProvidersModal.onDidDismiss((provider?: string) => {
            if (provider) {
                this.showFirebaseProvider(provider);
            }
        });

        this.firebaseAuthProvidersModal.present();
    }

    /**
     * Show alert
     */
    private showAlert(message: string): void {
        const alert = this.alert.create({
            title: this.translate.instant('error_occurred'),
            subTitle: message,
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }
}
