import { Component, OnInit, Input, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { ToastController, AlertController, NavController } from 'ionic-angular';

// services
import { ApplicationService } from 'services/application';
import { SecureHttpService } from 'services/http';
import { BootstrapService } from 'services/bootstrap';
import { SiteConfigsService } from 'services/site-configs';

// pages 
import { LoginPage } from 'pages/user/login';
import { AppMaintenancePage } from 'pages/app-maintenance';
import { BaseFormBasedPage } from 'pages/base.form.based';

// questions
import { QuestionManager } from 'services/questions/manager';
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'app-url',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class AppUrlPage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isAppUrlInProcessing: boolean = false;
    isUrlConfigured: boolean = false;
    form: FormGroup;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        private nav: NavController,
        private bootstrap: BootstrapService,
        private alert: AlertController,
        private http: SecureHttpService,
        private ref: ChangeDetectorRef,
        private application: ApplicationService,
        private questionManager: QuestionManager)
    {
        super(
            questionControl, 
            siteConfigs,
            translate,
            toast
        );
 
        if (this.application.getApiUrl()) {
            this.isUrlConfigured = true;
        }
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // create a form items
        this.questions = [
            this.questionManager.getQuestion(QuestionManager.TYPE_TEXT, {
                value: this.application.getGenericApiUrl(),
                key: 'url',
                placeholder: this.translate.instant('site_address_input'),
                validators: [{
                    name: 'require',
                    message: this.translate.instant('site_address_input_require_error')
                }]
            }, {
                hideWarning: true
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions);
    }

    /**
     * Submit
     */
    onSubmit(): void {
        // is form valid
        if (!this.form.valid) {
            this.showFormGeneralError(this.form);

            return;
        }

        if (!navigator.onLine) {
            this.showErrorPopup(this.translate.instant('no_internet'));

            return;
        }

        this.isAppUrlInProcessing = true;
        this.ref.markForCheck();

        this.http.validateApiUrl(this.form.value.url).subscribe(url => {
            if (!url) {
                this.isAppUrlInProcessing = false;
                this.ref.markForCheck();

                this.showErrorPopup(this.translate.instant('site_address_error'));

                return;
            }

            // remember defined url
            this.application.setGenericApiUrl(url);

            // load application dependencies
            this.bootstrap.loadDependencies(true).subscribe(() => {
                // redirect to the maintenance page
                if (this.siteConfigs.getConfig('maintenanceMode') === true) {
                    this.nav.setRoot(AppMaintenancePage);

                    return;
                }

                this.nav.setRoot(LoginPage);
            });
        });
    }

    /**
     * Show error popup
     */
    private showErrorPopup(message: string): void {
        const alert = this.alert.create({
            title: this.translate.instant('error_occurred'),
            subTitle: message,
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }
}
