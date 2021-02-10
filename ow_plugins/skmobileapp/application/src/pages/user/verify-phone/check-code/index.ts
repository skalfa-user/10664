import { Component, Input, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController, AlertController } from 'ionic-angular';

// services
import { AuthService } from 'services/auth';
import { VerifyPhoneService } from 'services/verify-phone';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { DashboardPage } from 'pages/dashboard';
import { LoginPage } from 'pages/user/login';
import { VerifyPhoneCheckNumberPage } from 'pages/user/verify-phone/check-number';
import { BaseFormBasedPage } from 'pages/base.form.based';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'verify-phone-check-code',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class VerifyPhoneCheckCodePage  extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    form: FormGroup;
    isRequestLoading: boolean = false;

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
        private ref: ChangeDetectorRef,
        private auth: AuthService,
        private nav: NavController,
        private questionManager: QuestionManager)
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
        // create form items
        this.questions = [
            this.questionManager.getQuestion(QuestionManager.TYPE_TEXT, {
                key: 'code',
                label: this.translate.instant('verify_phone_code_input'),
                placeholder: this.translate.instant('verify_phone_code_input'),
                validators: [{
                    name: 'require'
                }]
            }, {
                hideWarning: true
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions);
    }

    /**
     * Open check email page
     */
    openCheckCodePage(): void {
        this.nav.push(VerifyPhoneCheckNumberPage);
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
        if (!this.form.valid) {
            this.showFormGeneralError(this.form);

            return;
        }

        this.isRequestLoading = true;
        this.ref.markForCheck();

        // verify email by a code
        this.verifyPhoneService.verificationCode(this.form.value['code'], this.auth.getUser().id).subscribe(response => {
            this.isRequestLoading = false;
            this.ref.markForCheck();

            if (response.valid) {
                this.showNotification('verify_phone_verification_successful');
                this.nav.setRoot(DashboardPage);

                return;
            }

            const alert = this.alert.create({
                title: this.translate.instant('error_occurred'),
                subTitle: this.translate.instant('verify_phone_invalid_code'),
                buttons: [this.translate.instant('ok')]
            });

            alert.present();
        });
    }
}