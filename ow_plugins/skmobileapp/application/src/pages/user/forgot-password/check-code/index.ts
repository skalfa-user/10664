import { Component, Input, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { ToastController, NavController, AlertController, NavParams } from 'ionic-angular';

// services
import { UserService } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { ForgotPasswordNewPasswordPage } from 'pages/user/forgot-password/new-password';
import { BaseFormBasedPage } from 'pages/base.form.based'

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'forgot-password-check-code',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class ForgotPasswordCheckCodePage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isRequestLoading: boolean = false;
    form: FormGroup;

    private email: string;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected siteConfigs: SiteConfigsService,
        protected toast: ToastController,
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

        this.email = this.navParams.get('email');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // create form items
        this.questions = [
            this.questionManager.getQuestion(QuestionManager.TYPE_TEXT, {
                key: 'code',
                label: this.translate.instant('forgot_password_code_input'),
                placeholder: this.translate.instant('forgot_password_code_input_placeholder'),
                validators: [{
                    name: 'require'
                }]
            }, {
                stacked: true
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions);
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

        this.user.forgotPasswordValidateCode(this.form.value['code']).subscribe(response => {
            this.isRequestLoading = false;
            this.ref.markForCheck();

            if (response.valid == true) {
                this.nav.push(ForgotPasswordNewPasswordPage, {
                    code: this.form.value['code'],
                    email: this.email
                });

                return;
            }

            const alert = this.alert.create({
                title: this.translate.instant('error_occurred'),
                subTitle: this.translate.instant('forgot_password_code_invalid'),
                buttons: [this.translate.instant('ok')]
            });
    
            alert.present();
        });
    }
}
