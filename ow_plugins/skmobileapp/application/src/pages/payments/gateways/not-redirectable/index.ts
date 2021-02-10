import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, Input } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { ToastController, NavParams, NavController } from 'ionic-angular';

// services
import { SiteConfigsService } from 'services/site-configs';
import { PaymentsService } from 'services/payments';
import { PersistentStorageService } from 'services/persistent-storage';

// pages
import { DashboardPage } from 'pages/dashboard';
import { BaseFormBasedPage } from 'pages/base.form.based'
import { VideoUploadPage } from 'pages/user/video/upload';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'not-redirectable-payment-gateway',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class NotRedirectablePaymentsGatewayPage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isPageLoading: boolean = true;
    isPurchasing: boolean = false;
    form: FormGroup;
    sections: any = [];

    private gatewayKey: string;
    private saleId: number;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        private payments: PaymentsService,
        private navParams: NavParams,
        private nav: NavController,
        private ref: ChangeDetectorRef,
        protected persistentStorage: PersistentStorageService,
        private questionManager: QuestionManager)
    {
        super(
            questionControl,
            siteConfigs,
            translate,
            toast
        );

        this.gatewayKey = this.navParams.get('gatewayKey');
        this.saleId  = this.navParams.get('saleId');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // load questions
        this.payments.loadBillingGatewayInfo(this.gatewayKey).subscribe(response => {
            // process questions
            response.questions.forEach(questionData => {
                const data = {
                    section: '',
                    questions: []
                };

                data.section = questionData.section;

                questionData.items.forEach(question => {
                    const questionItem: QuestionBase = this.questionManager.getQuestion(question.type, {
                        key: question.key,
                        label:  this.translate.instant(question.label),
                        placeholder: this.translate.instant(question.placeholder),
                        values: question.values,
                        value: question.value
                    }, question.params);

                    // add validators
                    if (question.validators) {
                        questionItem.validators = question.validators;
                    }

                    data.questions.push(questionItem);
                    this.questions.push(questionItem);
                });

                this.sections.push(data);
            });

            // register all questions inside a form group
            this.form = this.questionControl.toFormGroup(this.questions);

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Submit
     */
    submit(): void {
        // is form valid
        if (!this.form.valid) {
            this.showFormGeneralError(this.form);

            return;
        }

        this.isPurchasing = true;
        this.ref.markForCheck();

        this.payments.finishMobilePurchaseSession(this.gatewayKey, this.saleId, this.form.value).subscribe(() => {
            this.showNotification('payment_success_finished_message');

            if ( this.persistentStorage.getValue('isBackVideoUpload') ) {
                this.nav.setRoot(VideoUploadPage, {isCancel: true});
            } else {
                this.nav.setRoot(DashboardPage);
            }
        });
    }
}
