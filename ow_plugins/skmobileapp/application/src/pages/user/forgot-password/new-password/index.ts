import { Component, Input, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { NavController, AlertController, NavParams, ToastController } from 'ionic-angular';

// services
import { UserService } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { LoginPage } from 'pages/user/login';
import { BaseFormBasedPage } from 'pages/base.form.based'

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'forgot-password-new-password',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class ForgotPasswordNewPasswordPage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isRequestLoading: boolean = false;
    form: FormGroup;

    private code: string;
    private email: string;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        private navParams: NavParams,
        private nav: NavController,
        private alert: AlertController,
        private user: UserService,
        private ref: ChangeDetectorRef,
        private questionManager: QuestionManager)
    {
        super(
            questionControl,
            siteConfigs,
            translate,
            toast
        );

        this.code = this.navParams.get('code');
        this.email = this.navParams.get('email');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // create form items
        this.questions = [
            this.questionManager.getQuestion(QuestionManager.TYPE_PASSWORD, {
                key: 'password',
                label: this.translate.instant('forgot_password_input'),
                placeholder: this.translate.instant('forgot_password_input_placeholder'),
                validators: [{
                    name: 'require'
                }, {
                    name: 'minLength',
                    message: this.translate.instant('password_min_length_validator_error', {
                        length: this.siteConfigs.getConfig('minPasswordLength')
                    }),
                    params: {
                        length: this.siteConfigs.getConfig('minPasswordLength')
                    }
                }, {
                    name: 'maxLength',
                    message: this.translate.instant('password_max_length_validator_error', {
                        length: this.siteConfigs.getConfig('maxPasswordLength')
                    }),
                    params: {
                        length: this.siteConfigs.getConfig('maxPasswordLength')
                    }
                }]
            }, {
                stacked: true
            }),
            this.questionManager.getQuestion(QuestionManager.TYPE_PASSWORD, {
                key: 'repeatPassword',
                label: this.translate.instant('password_repeat_input'),
                placeholder: this.translate.instant('password_repeat_input_placeholder'),
                validators: [{
                    name: 'require'
                }]
            }, {
                stacked: true
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions, (formGroup: FormGroup) => {
            // add extra validation for the form group
            if (formGroup.get('password').value === formGroup.get('repeatPassword').value) {
                return null;
            }

            return {
                message: this.translate.instant('password_repeat_validator_error'),
                question: 'repeatPassword'
            };
        });
    }

    /**
     * Submit form
     */
    submit(): void {
        // is form valid
        if (!this.form.valid) {
            this.showFormGeneralError(this.form);

            return;
        }

        this.isRequestLoading = true;
        this.ref.markForCheck();

        this.user.forgotPasswordRestPassword(this.code, this.email, this.form.value['password']).subscribe(response => {
            this.isRequestLoading = false;
            this.ref.markForCheck();

            if (response.success == true) {
                this.showNotification('forgot_password_reset_successful');
                this.nav.setRoot(LoginPage);

                return;
            }

            const alert = this.alert.create({
                title: this.translate.instant('error_occurred'),
                subTitle: response.message,
                buttons: [this.translate.instant('ok')]
            });

            alert.present();
        });
    }
}
