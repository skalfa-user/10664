import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit } from '@angular/core';

// services
import { PaymentsService, ICreditsInfoResponse } from 'services/payments';

@Component({
    selector: 'credits-info',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class CreditsInfoPage implements OnInit {
    isPageLoading: boolean = true;
    creditsInfo: ICreditsInfoResponse;

    /**
     * Constructor
     */
    constructor(
        private payments: PaymentsService, 
        private ref: ChangeDetectorRef) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        this.payments.loadCreditsInfo().subscribe(response => {
            this.creditsInfo = response;

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }
}
