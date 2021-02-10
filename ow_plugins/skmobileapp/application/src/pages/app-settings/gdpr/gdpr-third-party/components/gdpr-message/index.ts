import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { ViewController, ToastController } from 'ionic-angular';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';

// pages
import { BaseFormBasedPage } from 'pages/base.form.based';

// services
import { SiteConfigsService } from 'services/site-configs';
import { GdprService } from 'services/gdpr';
import { QuestionControlService } from 'services/questions/control.service';
import { QuestionManager } from 'services/questions/manager';
import { QuestionBase } from 'services/questions/questions/base';

@Component({
    selector: 'gdpr-message',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        GdprService,
        QuestionControlService
    ]
})

export class GdprMessageComponent extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = [];

    form: FormGroup;

    /**
     * Constructor
     */
    constructor(
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public questionControl: QuestionControlService,
        public toast: ToastController,
        private gdpr: GdprService,
        private view: ViewController,
        private questionManager: QuestionManager
    )
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
            this.questionManager.getQuestion(QuestionManager.TYPE_TEXTAREA, {
                key: 'message',
                label: this.translate.instant('gdpr_message_input'),
                placeholder: this.translate.instant('gdpr_message_input_placeholder'),
                validators: [
                    {name: 'require'}
                ]
            })
        ];

        // register all questions inside a form group
        this.form = this.questionControl.toFormGroup(this.questions);
    }

    /**
     * Submit form
     */
    submit() {
        // is form valid
        if (!this.form.valid) {
            this.showFormGeneralError(this.form);

            return;
        }

        this.showNotification('gdpr_third_party_message_feedback');
        this.close();
        this.gdpr.sendMessageToAdmin(this.form.value.message).subscribe();
    }

    /**
     * Close
     */
    close(): void {
        this.view.dismiss();
    }
}
