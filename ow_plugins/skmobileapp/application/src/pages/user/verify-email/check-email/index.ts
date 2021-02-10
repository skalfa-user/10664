import { Component, Input, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController, AlertController } from 'ionic-angular';

// services
import { AuthService } from 'services/auth';
import { UserService } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { LoginPage } from 'pages/user/login';
import { BaseFormBasedPage } from 'pages/base.form.based';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'verify-email-check-email',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class VerifyEmailCheckEmailPage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isRequestLoading: boolean = false;
    form: FormGroup;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public toast: ToastController,
        private alert: AlertController,
        private user: UserService,
        private nav: NavController,
        private auth: AuthService,
        private ref: ChangeDetectorRef,
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
            this.questionManager.getQuestion(QuestionManager.TYPE_EMAIL, {
                key: 'email',
                value: this.auth.getUser().email,
                label: this.translate.instant('verify_email_email_input'),
                placeholder: this.translate.instant('verify_email_email_input_placeholder'),
                validators: [
                    {name: 'require'},
                    {name: 'userEmail'}
                ]
            }, {
                hideWarning: true
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions);
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

        const currentEmail = this.auth.getUser().email;
        const newEmail = this.form.value['email'];

        // update user email
        if (currentEmail !== newEmail) {
            this.user.updateMe({
                email: this.form.value['email']
            }).subscribe(response => {
                this.isRequestLoading = false;
                this.ref.markForCheck();

                // refresh auth token
                this.auth.setAuthenticated(response.token);
                this.showUpdatingResult(true);
            });

            return;
        }

        // resend verification code
        this.user.resendVerificationCode(this.form.value['email']).subscribe(response => {
            this.isRequestLoading = false;
            this.ref.markForCheck();

            this.showUpdatingResult(response.success, response.message);
        });
    }

    /**
     * Show updating result
     */
    private showUpdatingResult(isSuccess: boolean, errorMessage?: string): void {
        if (isSuccess) {
            this.showNotification('verify_email_mail_sent', {
                email: this.form.value['email']
            });
            this.nav.pop();

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
