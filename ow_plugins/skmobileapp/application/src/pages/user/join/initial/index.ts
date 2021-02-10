import { Component, ChangeDetectionStrategy, Input, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { ToastController, AlertController, NavController } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';

// services
import { UserService, IUserData } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { JoinQuestionsPage } from 'pages/user/join/questions';
import { BaseFormBasedPage } from 'pages/base.form.based'

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

// components
import { IFileUploadResult } from  'shared/components/file-uploader';

@Component({
    selector: 'join-initial',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})

export class JoinInitialPage extends BaseFormBasedPage implements OnInit, OnDestroy {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    isPageLoading: boolean = false;
    form: FormGroup;
    isAvatarUploadIng: boolean = false;
    avatarUrl: string = null;
    avatarUploadUri = '/avatars';
    sections: any = [];
    lookingFor: number = 0;

    private isAvatarUploaded: boolean = false;
    private avatarKey: string = null;
    private siteConfigsSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        private nav: NavController,
        private alert: AlertController,
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
        // watch configs changes
        this.siteConfigsSubscription = this.siteConfigs.watchConfigGroup([
            'isAvatarRequired',
            'isAvatarHidden',
            'avatarMaxUploadSize',
            'validImageMimeTypes'
        ]).subscribe(() => this.ref.markForCheck());

        this.isPageLoading = true;
        this.ref.markForCheck();

        this.user.loadGenders().subscribe(response => {
            // process genders
            const genderList: Array<{value: any, title: string}> = [];

            response.forEach(gender => genderList.push({
                value: gender.id,
                title: gender.name
            }));

            const questions = this.getQuestionList(genderList);

            // process questions
            questions.forEach(questionData => {
                const data = {
                    section: '',
                    questions: []
                };

                data.section = questionData.section;

                questionData.questions.forEach(question => {

                    if ( this.siteConfigs.isPluginActive('hlookingfor') ) {
                        // TODO match_sex
                        if ( question.key == 'lookingFor' ) {
                            return;
                        }
                    }

                    const params = question.params ? question.params : {};

                    // create a question
                    const questionItem: QuestionBase = this.questionManager.getQuestion(question.type, {
                        key: question.key,
                        label: question.label,
                        placeholder: question.placeholder,
                        values: question.values,
                        value: question.value
                    }, params);

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
            this.form = this.questionControl.toFormGroup(this.questions, formGroup => {
                // validate passwords
                if (formGroup.get('password').value === formGroup.get('repeatPassword').value) {
                    return null;
                }

                return {
                    message: this.translate.instant('password_repeat_validator_error'),
                    question: 'repeatPassword'
                };
            });

            if ( this.siteConfigs.isPluginActive('hlookingfor') ) {
                // TODO match_sex
                let initialSex = 0;
                this.form.valueChanges.subscribe(form => {

                    if ( form.sex != initialSex ) {

                        initialSex = form.sex;

                        genderList.forEach((gender: any) => {
                            if (gender.value != form.sex) {

                                this.lookingFor = gender.value;
                                return;
                            }
                        });
                    }
                });
            }

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.siteConfigsSubscription.unsubscribe();
    }

    /**
     * Get avatar mime types
     */
    get getAvatarMimeTypes(): Array<string> {
        return this.siteConfigs.getConfig('validImageMimeTypes');
    }

    /**
     * Get avatar max size
     */
    get getAvatarMaxSize(): Array<string> {
        return this.siteConfigs.getConfig('avatarMaxUploadSize');
    }

    /**
     * Is avatar required
     */
    get isAvatarRequired(): boolean {
        return this.siteConfigs.getConfig('isAvatarRequired');
    }

    /**
     * Is avatar hidden
     */
    get isAvatarHidden(): boolean {
        return this.siteConfigs.getConfig('isAvatarHidden');
    }

    /**
     * Is avatar valid
     */
    get isAvatarValid(): boolean {
        return this.isAvatarHidden
            || (!this.isAvatarRequired && !this.isAvatarUploadIng)
            || (this.isAvatarUploaded && !this.isAvatarUploadIng);
    }

    /**
     * Success avatar upload callback
     */
    successAvatarUploadCallback(response: IFileUploadResult): void {
        this.avatarUrl = response.data.url;
        this.avatarKey = response.data.key;

        this.isAvatarUploaded = true;
        this.isAvatarUploadIng = false;
        this.ref.markForCheck();
    }

    /**
     * Error avatar upload callback
     */
    errorAvatarUploadCallback(): void {
        this.isAvatarUploadIng = false;
        this.ref.markForCheck();

        const alert = this.alert.create({
            title: this.translate.instant('error_occurred'),
            subTitle: this.translate.instant('error_uploading_file'),
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }

    /**
     * Start uploading avatar callback
     */
    startUploadingAvatarCallback(): void {
        this.isAvatarUploadIng = true;
        this.ref.markForCheck();
    }

    /**
     * Submit form
     */
    submit(): void {
        // is form valid
        if (!this.form.valid || !this.isAvatarValid) {
            // avatar is not uploaded
            if (this.form.valid && !this.isAvatarValid) {
                this.showNotification('avatar_input_error');

                return;
            }

            this.showFormGeneralError(this.form);

            return;
        }
        // TODO match_sex

        let initialData: IUserData = null;

        if ( this.siteConfigs.isPluginActive('hlookingfor') ) {
            initialData = {
                ...this.form.value,
                avatarKey: !this.isAvatarHidden ? this.avatarKey : null,
                lookingFor: [this.lookingFor]
            };
        } else {
            initialData = {
                ...this.form.value,
                avatarKey: !this.isAvatarHidden ? this.avatarKey : null
            };
        }

        this.nav.push(JoinQuestionsPage, {
            initial: initialData
        });
    }

    /**
     * Get question list
     */
    private getQuestionList(genderList: Array<{value: any, title: string}>): Array<any> {
        return [{
            section: '',
            questions: [{
                    type: QuestionManager.TYPE_TEXT,
                    key: 'userName',
                    label: this.translate.instant('username_input'),
                    placeholder: this.translate.instant('username_input_placeholder'),
                    validators: [
                        {name: 'require'},
                        {name: 'userName'}
                    ],
                    params: {
                        stacked: true
                    }
                }, {
                    type: QuestionManager.TYPE_PASSWORD,
                    key: 'password',
                    label: this.translate.instant('password_input'),
                    placeholder: this.translate.instant('password_input_placeholder'),
                    validators: [
                        {name: 'require'}, {
                            name: 'minLength',
                            message: this.translate.instant('password_min_length_validator_error', {
                                length: this.siteConfigs.getConfig('minPasswordLength')
                            }),
                            params: {
                                length: this.siteConfigs.getConfig('minPasswordLength')
                            }
                        }, {
                            name: 'maxLength',
                            message: this.translate.instant('password_max_length_validator_error', {
                                length: this.siteConfigs.getConfig('maxPasswordLength')
                            }),
                            params: {
                                length: this.siteConfigs.getConfig('maxPasswordLength')
                            }
                        }
                    ],
                    params: {
                        stacked: true
                    }
                }, {
                    type: QuestionManager.TYPE_PASSWORD,
                    key: 'repeatPassword',
                    label: this.translate.instant('password_repeat_input'),
                    placeholder: this.translate.instant('password_repeat_input_placeholder'),
                    validators: [
                        {name: 'require'}
                    ],
                    params: {
                        stacked: true
                    }
                }
            ]
        }, {
            section: this.translate.instant('base_input_section'),
            questions: [{
                    type: QuestionManager.TYPE_EMAIL,
                    key: 'email',
                    label: this.translate.instant('email_input'),
                    placeholder: this.translate.instant('email_input_placeholder'),
                    validators: [
                        {name: 'require'},
                        {name: 'userEmail'},
                    ],
                    params: {
                        stacked: true
                    }
                }, {
                    type: QuestionManager.TYPE_SELECT,
                    key: 'sex',
                    label: this.translate.instant('gender_input'),
                    values: genderList,
                    validators: [
                        {name: 'require'}
                    ],
                    params: {
                        hideEmptyValue: true
                    }
                }, {
                    type: QuestionManager.TYPE_MULTICHECKBOX,
                    key: 'lookingFor',
                    label: this.translate.instant('looking_for_input'),
                    values: genderList,
                    validators: [
                        {name: 'require'}
                    ]
                }
            ]
        }];
    }
}
