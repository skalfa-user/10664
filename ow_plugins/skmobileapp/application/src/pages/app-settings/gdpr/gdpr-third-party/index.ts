import { Component, ChangeDetectionStrategy } from '@angular/core';
import { ModalController } from 'ionic-angular';

// components
import { GdprMessageComponent } from 'pages/app-settings/gdpr/gdpr-third-party/components/gdpr-message';

@Component({
    selector: 'gdpr-third-party',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class GdprThirdPartyPage {

    /**
     * Constructor
     */
    constructor(private modal: ModalController) {}

    /**
     * Show modal
     */
    showModal(): void {
        const modal = this.modal.create(GdprMessageComponent);

        modal.present();
    }
}
