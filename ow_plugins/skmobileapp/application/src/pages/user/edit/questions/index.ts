import { Component, ChangeDetectionStrategy, Input, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController, ModalController, ActionSheetController, AlertController } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';
import { ISubscription } from 'rxjs/Subscription';

// services
import { SiteConfigsService } from 'services/site-configs';
import { UserService, IQuestionData } from 'services/user';
import { AuthService } from 'services/auth';
import { PhotosService } from 'services/photos';
import { AvatarsService } from 'services/avatars';
import { PermissionsService  } from 'services/permissions';
import { StringUtilsService } from 'services/string-utils';
import { FileUploaderService } from 'services/file-uploader';

// pages
import { BaseUserEdit } from 'pages/user/edit/base.user.edit'
import { EditUserPhotosPage } from 'pages/user/edit/photos';

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'edit-user-questions',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager,
        PhotosService,
        AvatarsService
    ]
})

export class EditUserQuestionsPage extends BaseUserEdit implements OnInit, OnDestroy {
    @Input() questions: Array<QuestionBase> = []; // list of questions

    form: FormGroup;
    sections: any = [];
    isPageLoading: boolean = true;
    isUserUpdating: boolean = false;

    private myUploadingAvatarSubscription: ISubscription;
    private myAllPhotosSubscription: ISubscription;
    protected maxPhotos: number = 7;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        protected nav: NavController,
        protected fileUploader: FileUploaderService,
        protected stringUtils: StringUtilsService,
        protected photos: PhotosService,
        protected avatars: AvatarsService,
        protected toast: ToastController,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        protected modal: ModalController,
        protected permissions: PermissionsService,
        protected alert: AlertController,
        protected actionSheet: ActionSheetController,
        private users: UserService,
        private ref: ChangeDetectorRef,
        private auth: AuthService,
        private questionManager: QuestionManager)
    {
        super(
            nav,
            fileUploader,
            stringUtils,
            avatars,
            photos,
            alert,
            actionSheet,
            permissions,
            modal,
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
        // watch the logged user's avatar changes
        this.myUploadingAvatarSubscription = this.avatars.watchMyUploadingAvatar().subscribe(avatar => {
            this.myUploadingAvatar = avatar;

            // refresh the generated photo list
            this.generatedPhotoList = this.generatePhotoList(true, this.maxPhotos);

            this.ref.markForCheck();
        });

        // watch all logged user's photos changes
        this.myAllPhotosSubscription = this.photos.watchMyAllPhotos().subscribe(photos => {
            this.myAllPhotos = photos;

            // refresh the generated photo list
            this.generatedPhotoList = this.generatePhotoList(true, this.maxPhotos);

            this.ref.markForCheck();
        });

        // load all page's dependencies
        Observable.forkJoin( 
            this.users.loadEditQuestions(),
            this.users.loadMe()
        ).subscribe((data) => {
            const [questions] = data;

            this.initForm(questions);

            this.isPageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.myUploadingAvatarSubscription.unsubscribe();
        this.myAllPhotosSubscription.unsubscribe();
    }

    /**
     * Submit
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

        this.isUserUpdating = true;
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

        this.users.updateQuestionsData(questions, false).subscribe((response) => {
            // refresh auth token if it exists
            response.forEach(question => {
                if (question.params && question.params.token) {
                    this.auth.setAuthenticated(question.params.token);
                }
            });

            // refresh logged user's data
            this.users.loadMe().subscribe(() => {
                this.isUserUpdating = false;
                this.ref.markForCheck();

                this.showNotification('profile_updated');
            });
        });
    }

    /**
     * Get extra photo actions
     */
    protected getExtraPhotoActions(): Array<any> {
        const buttons: Array<any> = [];
        const photoUploadPermission = this.permissions.getMe('photo_upload');

        // view all photos
        buttons.push({
            text: this.translate.instant('view_all_photos'),
            handler: () => this.nav.push(EditUserPhotosPage, {
                isPhotosLoaded: true
            })
        });

        // upload photo
        if (photoUploadPermission.isAllowed || photoUploadPermission.isPromoted) {
            buttons.push({
                text: this.translate.instant('upload_photo'),
                handler: () => photoUploadPermission.isAllowed
                    ? this.photoUploaderComponent.showFileChooser()
                    : this.permissionsComponent.showAccessDeniedAlert()
            });
        }

        return buttons;
    }
 
    /**
     * Init form
     */
    private initForm(questions): void {
        // process questions
        questions.forEach(questionData => {
            const data = {
                section: '',
                questions: []
            };

            data.section = questionData.section;

            questionData.items.forEach(question => {
                const questionItem: QuestionBase = this.questionManager.getQuestion(question.type, {
                    key: question.key,
                    label: question.label,
                    values: question.values,
                    placeholder: question.placeholder,
                    value: question.value
                }, question.params);

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
    }
}
