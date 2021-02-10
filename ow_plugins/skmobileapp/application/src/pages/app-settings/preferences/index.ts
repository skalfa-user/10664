import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, Input } from '@angular/core';
import { ToastController, NavParams } from 'ionic-angular';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';

// services
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';

// pages
import { BaseFormBasedPage } from 'pages/base.form.based'

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'preferences',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class PreferencesPage extends BaseFormBasedPage implements OnInit {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    form: FormGroup;
    isPageLoading: boolean = true;
    isPreferenceSaving: boolean = false;
    pageTitle: string;

    private preferencesSection: string;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        private ref: ChangeDetectorRef,
        private navParams: NavParams,
        private user: UserService,
        private questionManager: QuestionManager)
    {
        super(
            questionControl, 
            siteConfigs,
            translate,
            toast
        );

        this.pageTitle = this.navParams.get('title');
        this.preferencesSection = this.navParams.get('section');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // load questions
        this.user.loadPreferencesQuestions(this.preferencesSection).subscribe(response => {
            // process questions
            response.forEach(question => {
                const questionItem: QuestionBase = this.questionManager.getQuestion(question.type, {
                    key: question.key,
                    label: question.label,
                    value: question.value
                });

                // add validators
                if (question.validators) {
                    questionItem.validators = question.validators;
                }

                this.questions.push(questionItem);
            });

            // register all questions inside a form group
            this.form = this.questionControl.toFormGroup(this.questions); 
            this.isPageLoading = false;
            this.ref.markForCheck(); 
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

        this.isPreferenceSaving = true;
        this.ref.markForCheck();

        const processedQuestions = [];
        this.questions.forEach((questionData: QuestionBase) => {
            processedQuestions.push({
                name: questionData.key,
                value: this.form.value[questionData.key]
            });
        });

        // update preferences
        this.user.updatePreferencesQuestions(processedQuestions).subscribe(() => {
            this.showNotification('preferences_saved');

            this.isPreferenceSaving = false;
            this.ref.markForCheck();
        });
    }
}
