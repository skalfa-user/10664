import { Component, ChangeDetectionStrategy, Input, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';

// services
import { UserService } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';
import { AuthService } from 'services/auth';

// pages
import { DashboardPage } from 'pages/dashboard';
import { BaseFormBasedPage } from 'pages/base.form.based';
import { LoginPage } from 'pages/user/login';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'complete-account-type',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class CompleteAccountTypePage extends BaseFormBasedPage implements OnInit, OnDestroy {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isPageLoading: boolean = false;
    isUpdatingUserProfile: boolean = false;
    form: FormGroup;
    sections: any = [];

    private loadGendersSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public siteConfigs: SiteConfigsService,
        public translate: TranslateService,
        public toast: ToastController,
        private nav: NavController,
        private user: UserService,
        private ref: ChangeDetectorRef,
        private auth: AuthService,
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

        // load genders
        this.loadGendersSubscription = this.user.loadGenders().subscribe(genders => {
            // process genders
            const genderList = [];

            genders.forEach((gender) => {
                genderList.push({
                    value: gender.id,
                    title: gender.name
                });
            });

            // questions list
            const questions = [
                {
                    section: '',
                    questions: [
                        {
                            type: QuestionManager.TYPE_SELECT,
                            key: 'accountType',
                            label: this.translate.instant('gender_input'),
                            values: genderList,
                            validators: [
                                {name: 'require'}
                            ]
                        }
                    ]
                }
            ];

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


            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.loadGendersSubscription.unsubscribe();
    }

    /**
     * Submit form
     */
    submit(): void {
        // is the form valid
        if (!this.form.valid) {
            this.showFormGeneralError(this.form);

            return;
        }

        this.isUpdatingUserProfile = true;
        this.ref.markForCheck();

        // update the user account
        this.user.updateAccountType(this.form.value['accountType']).subscribe(() => {
            this.user.loadMe().subscribe(() => {
                this.isPageLoading = false;
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
