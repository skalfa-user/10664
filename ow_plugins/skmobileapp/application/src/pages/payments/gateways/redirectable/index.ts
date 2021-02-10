import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, ViewChild, ElementRef } from '@angular/core';
import { NavParams } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';

// services
import { PaymentsService, IBillingGatewayInfoResponse, IPurchaseItemResponse } from 'services/payments';

@Component({
    selector: 'redirectable-payment-gateway',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class RedirectablePaymentsGatewayPage implements OnInit {
    @ViewChild('billingGatewayForm') set content(form: ElementRef) {
        this.billingForm = form;
    }

    isPageLoading: boolean = true;
    billingGatewayInfo: IBillingGatewayInfoResponse;
    purchaseSession: Array<IPurchaseItemResponse> = [];

    private saleId: number;
    private gatewayKey: string;
    private billingForm: ElementRef;
    private isFormSubmitting: boolean = false;

    /**
     * Constructor
     */
    constructor(
        private ref: ChangeDetectorRef,
        private payments: PaymentsService,
        private navParams: NavParams) 
    {
        this.saleId = this.navParams.get('saleId');
        this.gatewayKey = this.navParams.get('gatewayKey');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // load page's dependencies
        const dependencies: Observable<any> = Observable.forkJoin( 
            this.payments.loadBillingGatewayInfo(this.gatewayKey),
            this.payments.loadMobilePurchaseSession(this.saleId)
        );

        dependencies.subscribe((data) => {
            [this.billingGatewayInfo, this.purchaseSession] = data;

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * After content checked
     */
    ngAfterViewChecked() {
        if (!this.isPageLoading && this.billingForm && !this.isFormSubmitting) {
            this.isFormSubmitting = true;
            this.billingForm.nativeElement.submit();
        }
    }
}
