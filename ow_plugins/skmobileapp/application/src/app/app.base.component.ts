
import { App as IonicApp } from 'ionic-angular';

// services
import { AuthService } from 'services/auth';
import { IHttpError } from 'services/http';
import { PaymentsService } from 'services/payments';

// pages
import { AppNoInternetPage } from 'pages/app-no-internet';
import { AppErrorPage } from 'pages/app-error';
import { LoginPage } from 'pages/user/login';
import { UserDisapprovedPage } from 'pages/user/disapproved';
import { VerifyEmailCheckCodePage } from 'pages/user/verify-email/check-code';
import { CompleteProfilePage } from 'pages/user/complete-profile'
import { CompleteAccountTypePage } from 'pages/user/complete-account-type'
import { AppMaintenancePage } from 'pages/app-maintenance'
import { InitialPaymentsPage } from 'pages/payments/initial';
import { VerifyPhoneCheckNumberPage } from 'pages/user/verify-phone/check-number';
import { VerifyPhoneCheckCodePage } from 'pages/user/verify-phone/check-code';

export abstract class BaseApp {
    public static readonly HTTP_ERROR_NO_INTERNET_CONNECTION: number = 0;
    public static readonly HTTP_ERROR_NOT_AUTHORIZED: number = 401;
    public static readonly HTTP_ERROR_FORBIDDEN: number = 403;

    // forbidden types
    public static readonly HTTP_ERROR_FORBIDDEN_DISAPPROVED: string = 'disapproved';
    public static readonly HTTP_ERROR_FORBIDDEN_SUSPENDED: string = 'suspended';
    public static readonly HTTP_ERROR_FORBIDDEN_EMAIL_NOT_VERIFIED: string = 'emailNotVerified';
    public static readonly HTTP_ERROR_FORBIDDEN_MAINTENANCE: string = 'maintenance';
    public static readonly HTTP_ERROR_FORBIDDEN_PROFILE_NOT_COMPLETED: string = 'profileNotCompleted';
    public static readonly HTTP_ERROR_FORBIDDEN_ACCOUNT_TYPE_NOT_COMPLETED: string = 'accountTypeNotCompleted';
    public static readonly HTTP_ERROR_FORBIDDEN_PHONE_NOT_VERIFIED: string = 'phoneNumberNotVerified';
    public static readonly HTTP_ERROR_FORBIDDEN_PHONE_CODE_NOT_VERIFIED: string = 'phoneCodeNotVerified';

    /**
     * Constructor
     */
    constructor(
        protected ionicApp: IonicApp, 
        protected auth: AuthService,
        protected payments: PaymentsService) {}

    /**
     * Is navigator online
     */
    isNavigatorOnline(): boolean {
        return navigator.onLine;
    }

    /**
     * Http error handler
     */
    httpErrorHandler(error: IHttpError): void {
         switch (error.code) {
            case BaseApp.HTTP_ERROR_NO_INTERNET_CONNECTION: // 0
                if (!this.isNavigatorOnline()) {
                    this.ionicApp.getRootNav().setRoot(AppNoInternetPage);
                }
                else {
                    this.ionicApp.getRootNav().setRoot(AppErrorPage);
                }

                break;

            case BaseApp.HTTP_ERROR_NOT_AUTHORIZED: // 401
                this.auth.logout();
                this.ionicApp.getRootNav().setRoot(LoginPage);

                break;

            case BaseApp.HTTP_ERROR_FORBIDDEN: // 403
                switch (error.type) {
                    case BaseApp.HTTP_ERROR_FORBIDDEN_DISAPPROVED :
                        this.ionicApp.getRootNav().setRoot(UserDisapprovedPage, {
                            status: 'disapproved',
                        });

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_SUSPENDED :
                        this.ionicApp.getRootNav().setRoot(UserDisapprovedPage, {
                            status: 'suspended',
                            description: error.description
                        });

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_EMAIL_NOT_VERIFIED :
                        this.ionicApp.getRootNav().setRoot(VerifyEmailCheckCodePage);

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_PROFILE_NOT_COMPLETED :
                        this.ionicApp.getRootNav().setRoot(CompleteProfilePage);

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_ACCOUNT_TYPE_NOT_COMPLETED :
                        this.ionicApp.getRootNav().setRoot(CompleteAccountTypePage);

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_MAINTENANCE :
                        this.ionicApp.getRootNav().setRoot(AppMaintenancePage);

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_PHONE_NOT_VERIFIED :
                        this.ionicApp.getRootNav().setRoot(VerifyPhoneCheckNumberPage);

                        break;

                    case BaseApp.HTTP_ERROR_FORBIDDEN_PHONE_CODE_NOT_VERIFIED :
                        this.ionicApp.getRootNav().setRoot(VerifyPhoneCheckCodePage);

                        break;

                    default :
                        // redirect to the payments
                        if (this.payments.isPaymentsAvailable()) {
                            this.ionicApp.getRootNav().push(InitialPaymentsPage, {
                                isShowNotification: true
                            });

                            return;
                        }

                        this.ionicApp.getRootNav().setRoot(AppErrorPage);
                }

                break;

            default : // 404, 500, etc
                this.ionicApp.getRootNav().setRoot(AppErrorPage);
        }
    }
};
