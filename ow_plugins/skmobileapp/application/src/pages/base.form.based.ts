import { TranslateService } from 'ng2-translate';
import { ToastController } from 'ionic-angular';
import { FormGroup } from '@angular/forms';

// services
import { SiteConfigsService } from 'services/site-configs';
import { QuestionControlService } from 'services/questions/control.service';

export abstract class BaseFormBasedPage {
    /**
     * Constructor
     */
    constructor(
        protected questionControl: QuestionControlService,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        protected toast: ToastController) {}

    /**
     * Delete conversation
     */
    protected showFormGeneralError(form: FormGroup): void {
        this.questionControl.validateForm(form);
        this.showNotification('form_general_error');
    }

    /**
     * Show notification
     */
    protected showNotification(lang: string, params?: {}): void {
        const notificationToaster = this.toast.create({
            message: this.translate.instant(lang, params),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        notificationToaster.present();
    }
}
