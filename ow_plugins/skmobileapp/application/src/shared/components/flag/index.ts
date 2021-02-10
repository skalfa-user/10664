import { Component, OnInit, Input, ChangeDetectionStrategy } from '@angular/core';
import { ToastController, NavParams, ViewController } from 'ionic-angular';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';

// service
import { SiteConfigsService } from 'services/site-configs';
import { FlagService } from 'services/flag';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

// pages
import { BaseFormBasedPage } from 'pages/base.form.based';

@Component({
    selector: 'flag',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager,
        FlagService
    ]
})

export class FlagComponent extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    form: FormGroup;
    sections: any = [];

    private identityId: number;
    private entityType: string;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public toast: ToastController,
        private flag: FlagService,
        private view: ViewController,
        private navParams: NavParams,
        private questionManager: QuestionManager) 
    {
        super(
            questionControl, 
            siteConfigs,
            translate,
            toast
        );

        this.identityId = this.navParams.get('identityId');
        this.entityType = this.navParams.get('entityType');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // questions list
        const questions = [{
            section: '',
            questions: [{
                type: QuestionManager.TYPE_SELECT,
                key: 'reason',
                label: this.translate.instant('flag_input'),
                values: [{
                    value: 'spam',
                    title: this.translate.instant('flag_as_spam')
                }, {
                    value: 'offence',
                    title: this.translate.instant('flag_as_offence')
                }, {
                    value: 'illegal',
                    title: this.translate.instant('flag_as_illegal')
                }],
                validators: [
                    {name: 'require'}
                ]
            }]
        }];

        // process questions
        questions.forEach(questionData => {
            const data = {
                section: '',
                questions: []
            };

            data.section = questionData.section;

            questionData.questions.forEach(question => {
                const questionItem: QuestionBase = this.questionManager.getQuestion(question.type, {
                    key: question.key,
                    label: question.label,
                    values: question.values
                });

                // add validators
                if (question.validators) {
                    questionItem.validators = question.validators;
                }

                data.questions.push(questionItem);
                this.questions.push(questionItem);
            });

            this.sections.push(data);

            // register all questions inside a form group
            this.form = this.questionControl.toFormGroup(this.questions);
        });
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

        // flag content
        this.flag.flagContent(this.identityId, this.entityType, this.form.value.reason).subscribe();

        this.view.dismiss({
            reported: true
        });
    }

    /**
     * Return back
     */
    returnBack(): void {
        this.view.dismiss({
            reported: false
        });
    }
}
