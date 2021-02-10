import { Component, Input, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController, AlertController } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';

// services
import { AuthService } from 'services/auth';
import { VerifyPhoneService, ICountriesResponse, IUserDataResponse } from 'services/verify-phone';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { LoginPage } from 'pages/user/login';
import { BaseFormBasedPage } from 'pages/base.form.based';
import { VerifyPhoneCheckCodePage } from 'pages/user/verify-phone/check-code';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'verify-phone-check-phone',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class VerifyPhoneCheckNumberPage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isRequestLoading: boolean = false;
    form: FormGroup;
    formReady: boolean = false;
    private currentPhoneNumber: any = '';
    private countryCode: any = '';
    private countriesList: any[] = [];

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public toast: ToastController,
        private alert: AlertController,
        private verifyPhoneService: VerifyPhoneService,
        private nav: NavController,
        private auth: AuthService,
        private ref: ChangeDetectorRef,
        private questionManager: QuestionManager
    )
    {
        super(
            questionControl,
            siteConfigs,
            translate,
            toast
        );
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // load page's dependencies
        const dependencies: Observable<any> = Observable.forkJoin(
            this.verifyPhoneService.loadCountries(),
            this.verifyPhoneService.loadPhonesMe()
        );

        dependencies.subscribe((data: [ICountriesResponse[], IUserDataResponse] ) => {
            const [verificationsCountries, userPhone] = data;

            if ( userPhone ) {
                this.currentPhoneNumber = userPhone.number;
                this.countryCode = userPhone.countryCode;
            }

            verificationsCountries.forEach((country: ICountriesResponse) => this.countriesList.push({
                value: country.phoneCode,
                title: country.title
            }));

            let labelVerifyPhoneNumberInput = this.getPhoneNumberLabel(this.countryCode);

            // create form items
            this.questions = [
                this.questionManager.getQuestion(QuestionManager.TYPE_SELECT, {
                    key: 'countryCode',
                    value: this.countryCode,
                    values: this.countriesList,
                    label: this.translate.instant('verify_country_code_input'),
                    validators: [
                        {name: 'require'}
                    ]
                }, {
                    stacked: true,
                    hideWarning: true
                }),
                this.questionManager.getQuestion(QuestionManager.TYPE_TEXT, {
                    key: 'phoneNumber',
                    value: this.currentPhoneNumber,
                    label: labelVerifyPhoneNumberInput,
                    placeholder: this.translate.instant('verify_phone_number_placeholder'),
                    validators: [
                        {name: 'require'},
                        {
                            name: 'regExp',
                            message: this.translate.instant('user_phone_validator_error'),
                            params: {
                                pattern: /^\d+$/
                            }
                        }
                    ]
                }, {
                    stacked: true,
                    hideWarning: true
                })
            ];

            // register all questions inside a form group
            this.form = this.questionControl.toFormGroup(this.questions);
            let initialCountryCode = this.form.value.countryCode;

            this.form.valueChanges.subscribe(form => {
                // is sex value changed
                if ( form.countryCode != initialCountryCode ) {
                    initialCountryCode = form.countryCode;
                    labelVerifyPhoneNumberInput = this.getPhoneNumberLabel(initialCountryCode);

                    this.questions.forEach((question: any) => {
                        if (question.key == 'phoneNumber') {
                            question.label = labelVerifyPhoneNumberInput;
                        }
                    });

                    this.ref.markForCheck();
                }
            });

            this.formReady = true;
            this.ref.markForCheck();
        });
    }

    getPhoneNumberLabel( countryCode: any ): string {
        let labelVerifyPhoneNumberInput = this.translate.instant('verify_phone_number_input');

        return countryCode ? labelVerifyPhoneNumberInput + " +(" + countryCode + ")" : labelVerifyPhoneNumberInput;
    }

    /**
     * Open login page
     */
    openLoginPage(): void {
        this.auth.logout();
        this.nav.setRoot(LoginPage);
    }

    /**
     * Submit form
     */
    submit(): void {
        // is form valid
        if ( !this.form.valid ) {
            this.showFormGeneralError(this.form);

            return;
        }

        this.isRequestLoading = true;
        this.ref.markForCheck();

        const countryCode = this.form.value['countryCode'];
        const phoneNumber = this.form.value['phoneNumber'];

        // resend verification code
        this.verifyPhoneService.resendVerificationCode(countryCode, phoneNumber).subscribe(response => {
            this.isRequestLoading = false;
            this.ref.markForCheck();

            this.showUpdatingResult(response.success, response.message);
        });
    }

    /**
     * Show updating result
     */
    private showUpdatingResult(isSuccess: boolean, errorMessage?: string): void {
        if ( isSuccess ) {
            this.showNotification('verify_phone_sms_sent');
            this.nav.setRoot(VerifyPhoneCheckCodePage);

            return;
        }

        const alert = this.alert.create({
            title: this.translate.instant('error_occurred'),
            subTitle: errorMessage,
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }
}
