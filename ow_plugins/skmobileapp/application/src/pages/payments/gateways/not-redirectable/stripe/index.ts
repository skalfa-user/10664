import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, Input, ViewChild } from '@angular/core';
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

import { SassHelperComponent } from 'pages/payments/gateways/not-redirectable/stripe/sass-helper';

declare var Stripe: any;

@Component({
    selector: 'not-redirectable-payment-gateway-stripe',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class NotRedirectablePaymentsGatewayPageStripe extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions
    @ViewChild(SassHelperComponent) sassHelper: SassHelperComponent;

    isPageLoading: boolean = true;
    isPurchasing: boolean = false;
    form: FormGroup;
    sections: any = [];
    stripe: any;
    cardNumber: any;
    cardExpiry: any;
    cardCvc: any;
    token: string;

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
            // get publish key
            const publishKey = response.options['publishKey'];

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

            // init Stripe
            this.stripe = Stripe(publishKey);

            // init card elements
            let style = {
                base: {
                    fontSmoothing: 'antialiased',
                    '::placeholder': {
                        color: this.sassHelper.readProperty('sk-desc-color'),
                    },
                },
                invalid: {
                    color: this.sassHelper.readProperty('danger'),
                }
            };

            const elements = this.stripe.elements();

            // card number
            this.cardNumber = elements.create('cardNumber',
                { classes: { base: 'stripe_element_base' },
                placeholder: this.translate.instant('stripe_card_number_placeholder'),
                style: style
            });

            this.cardNumber.mount('#card-number-element');

            // expiry
            this.cardExpiry = elements.create('cardExpiry',
                { classes: { base: 'stripe_element_base'},
                placeholder: this.translate.instant('stripe_card_expiry_placeholder'),
                style: style
            });

            this.cardExpiry.mount('#card-expiry-element');

            // cvc
            this.cardCvc = elements.create('cardCvc',
                { classes: { base: 'stripe_element_base'},
                placeholder: this.translate.instant('stripe_card_cvc_placeholder'),
                style: style
            });

            this.cardCvc.mount('#card-cvc-element');

            this.isPageLoading = false;
            this.ref.markForCheck();
            this.showNotification('stripe_process_complete_notification');
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

        // collect token data
        const tokenData =  {
            name: this.form.value['card_name'],
            address_country: this.form.value['country'] ? this.form.value['country'] : '',
            address_line1: this.form.value['address_line'] ? this.form.value['address_line'] : '',
            address_zip: this.form.value['zip_code'] ? this.form.value['zip_code'] : '',
            address_state: this.form.value['state'] ? this.form.value['state'] : ''
        };

        // create token
        this.stripe.createToken(this.cardNumber, tokenData).then((response) => {
            // check errors
            if (response.error) {
                this.showStripeCardNotification(response.error);

                this.isPurchasing = false;
                this.ref.markForCheck();

                return;
            }
            else {
                this.token = response.token.id;

                let options = {
                    status: 'init',
                    data: this.token
                };
                // start process sale
                this.payments.processMobilePurchaseSession(this.gatewayKey, this.saleId,
                    {options: options}).subscribe(response => {
                        // process status
                        switch (response.status) {

                        // init paymentIntent
                        case 'init_payment': {
                            this.stripe.handleCardPayment(
                                response.data, this.cardNumber).then( (result) => {
                                if (result.error) {
                                    // display error.message
                                    this.showStripeCardNotification(result.error);
                                    this.nav.setRoot(DashboardPage);
                                } else {
                                    // payment has succeeded
                                    if (result.paymentIntent.status === 'succeeded') {
                                        this.showNotification('stripe_notification_after_actions');
                                        // deliver sale
                                        let options = {
                                            status: 'success_payment',
                                            data: result.paymentIntent.id
                                        };

                                        this.payments.processMobilePurchaseSession(this.gatewayKey, this.saleId,
                                            {options: options}).subscribe(response => {
                                            if (response.status === 'success') {
                                                this.isPurchasing = false;
                                                this.ref.markForCheck();

                                                this.showNotification('payment_success_finished_message');
                                                if ( this.persistentStorage.getValue('isBackVideoUpload') ) {
                                                    this.nav.setRoot(VideoUploadPage, {isCancel: true});
                                                } else {
                                                    this.nav.setRoot(DashboardPage);
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                            break;
                        }

                        // init subscription
                        case 'incomplete_subscription': {
                            this.stripe.handleCardPayment(response.data).then( (result) => {
                                if (result.error) {
                                    // display error.message
                                    this.showStripeCardNotification(result.error);

                                    this.isPurchasing = false;
                                    this.ref.markForCheck();

                                    this.nav.setRoot(DashboardPage);
                                } else {
                                    this.showNotification('stripe_notification_after_actions');
                                    if (result.paymentIntent.status === 'succeeded') {
                                        // deliver sale
                                        let options = {
                                            status: 'success_payment_subscription',
                                            data: result.paymentIntent.id
                                        };

                                        this.payments.processMobilePurchaseSession(this.gatewayKey, this.saleId,
                                            {options: options}).subscribe(response => {

                                            if (response.status === 'success') {
                                                this.isPurchasing = false;
                                                this.ref.markForCheck();

                                                this.showNotification('payment_success_finished_message');
                                                if ( this.persistentStorage.getValue('isBackVideoUpload') ) {
                                                    this.nav.setRoot(VideoUploadPage, {isCancel: true});
                                                } else {
                                                    this.nav.setRoot(DashboardPage);
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                            break;
                        }

                        // process success status
                        case 'success': {
                            this.isPurchasing = false;
                            this.ref.markForCheck();

                            this.showNotification('payment_success_finished_message');

                            if ( this.persistentStorage.getValue('isBackVideoUpload') ) {
                                this.nav.setRoot(VideoUploadPage, {isCancel: true});
                            } else {
                                this.nav.setRoot(DashboardPage);
                            }

                            break;
                        }
                        default: {
                            this.isPurchasing = false;
                            this.ref.markForCheck();

                            this.showNotification('payment_success_finished_message');

                            if ( this.persistentStorage.getValue('isBackVideoUpload') ) {
                                this.nav.setRoot(VideoUploadPage, {isCancel: true});
                            } else {
                                this.nav.setRoot(DashboardPage);
                            }
                        }
                    }
                });
            }
        });
    }

    showStripeCardNotification(error) {
        switch(error.code) {
            case 'incomplete_number': {
                this.showNotification('stripe_card_number_invalid');
                break;
            }
            case 'incomplete_expiry': {
                this.showNotification('stripe_exp_date_invalid');
                break;
            }
            case 'incomplete_cvc': {
                this.showNotification('stripe_cvc_invalid');
                break;
            }
            default: {
                this.showNotification(error.message);
                break;
            }
        }
    }
}
