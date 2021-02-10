import { Component, OnInit, OnDestroy, Input, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { NavController, ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { ISubscription } from 'rxjs/Subscription';

// services
import { UserService, IQuestionData } from 'services/user';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { DashboardPage } from 'pages/dashboard';
import { BaseFormBasedPage } from 'pages/base.form.based';
import { LoginPage } from 'pages/user/login';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'complete-profile',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class CompleteProfilePage extends BaseFormBasedPage implements OnInit, OnDestroy {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isPageLoading: boolean = false;
    isUpdatingUserProfile: boolean = false;
    sections: any = [];
    form: FormGroup;

    private loadQuestionsSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public toast: ToastController,
        private nav: NavController,
        private auth: AuthService,
        private user: UserService,
        private ref: ChangeDetectorRef,
        private questionManager: QuestionManager) 
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
        this.isPageLoading = true;
        this.ref.markForCheck();

        // load questions
        this.loadQuestionsSubscription = this.user.loadCompleteProfileQuestions().subscribe(response => {
            // process questions sections
            response.forEach(questionData => {
                const data = {
                    section: '',
                    questions: []
                };

                data.section = questionData.section;

                // process questions
                questionData.items.forEach(question => {
                    // create a question from response
                    const questionItem = this.questionManager.getQuestion(question.type, {
                        key: question.key,
                        label: question.label,
                        placeholder: question.placeholder,
                        values: question.values,
                        value: question.value
                    }, question.params);

                    questionItem.validators = [];

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
     * Component destroy
     */
    ngOnDestroy(): void {
        this.loadQuestionsSubscription.unsubscribe();
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

        this.isUpdatingUserProfile = true;
        this.ref.markForCheck();

        // process questions
        const questions: Array<IQuestionData> = [];
        this.questions.forEach(questionData => {
            questions.push({
                name: questionData.key,
                value: this.form.value[questionData.key],
                type: questionData.controlType
            });
        });

        // update the user questions
        this.user.updateQuestionsData(questions).subscribe(response => {
            // refresh auth token if it exists
            response.forEach(question => {
                if (question.params && question.params.token) {
                    this.auth.setAuthenticated(question.params.token);
                }
            });

            // refresh the logged user's data
            this.user.loadMe().subscribe(() => {
                this.isUpdatingUserProfile = false;
                this.ref.markForCheck();

                // load the dashboard
                this.nav.setRoot(DashboardPage);
                this.showNotification('profile_updated');
            });
        });
    }

    /**
     * Logout user
     */
    logout(): void {
        this.auth.logout();
        this.nav.setRoot(LoginPage);
    } 
}
