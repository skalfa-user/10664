import { Component, ChangeDetectionStrategy, Input, OnInit, OnDestroy, ChangeDetectorRef, ViewChild } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { TranslateService } from 'ng2-translate';
import { ToastController, AlertController, NavController, ActionSheetController, NavParams, ModalController } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';

// services
import { VideoUploaderService, IVideoDataResponse, VideoUploaderSource, IVideoPreview, IVideoUserDataResponse } from 'services/video-uploader';
import { SiteConfigsService } from 'services/site-configs';
import { PermissionsService, IPermission } from 'services/permissions';
import { ViewVideoComponent } from 'shared/components/view-video';
import { UserSearchAutocompleteComponent } from 'shared/components/user-search-autocomplete';

// pages
import { BaseFormBasedPage } from 'pages/base.form.based'

// questions
import { QuestionBase } from 'services/questions/questions/base';
import { QuestionManager } from 'services/questions/manager';
import { QuestionControlService } from 'services/questions/control.service';

// components
import { FileUploaderComponent } from "shared/components/file-uploader";
import { ProfileViewPage } from "pages/profile";
import { PermissionsComponent } from  'shared/components/permissions';
import {DashboardPage} from "pages/dashboard";

@Component({
    selector: 'video-upload',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        QuestionManager
    ]
})
export class VideoUploadPage extends BaseFormBasedPage implements OnInit, OnDestroy {
    @ViewChild(PermissionsComponent) permissionsComponent: PermissionsComponent;
    @Input() formElements: Array<QuestionBase> = [];

    isPageLoading: boolean = false;
    isShowUserList: boolean = false;
    isSubmit: boolean = false;
    form: FormGroup;
    selectUserItems: Array<IVideoUserDataResponse> = [];

    maxSizeBytes: number = 0;
    videoUrl: string = null;
    duration: number = 0;

    // TODO VIDEO
    private fileUploader;
    private value: IVideoPreview = null;
    private isUploadingStarted: boolean = false;
    private isVideoValid: boolean = false;
    private uploadDescription: string;
    private uploadProgress: number = 0;
    private uploadProcess: ISubscription = null;
    private static readonly STATUS_PROCESSED: string = 'processed';
    private permissionSubscription: ISubscription;
    private videoPermission: IPermission;
    private isCancel: boolean = false;
    private isDelete: boolean = false;

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
        private modalController: ModalController,
        private videoUploader: VideoUploaderService,
        private ref: ChangeDetectorRef,
        private actionSheetCtrl: ActionSheetController,
        private permissions: PermissionsService,
        private navParams: NavParams,
        private questionManager: QuestionManager)
    {
        super(
            questionControl,
            siteConfigs,
            translate,
            toast
        );

        if ( this.navParams.get('isCancel') ) {
            this.isCancel = true;
        } else {
            this.isCancel = false;
        }
    }

    cancel() {
        this.nav.setRoot(DashboardPage);
    }

    ngOnInit(): void {

        this.isPageLoading = true;
        this.ref.markForCheck();

        // watch the permission updates
        this.permissionSubscription = this.permissions
            .watchMeGroup([
                'cvideoupload_upload_video'
            ])
            .subscribe((permissions: Array<IPermission>) => {
                console.log(permissions);
                [this.videoPermission] = permissions;
                this.ref.markForCheck();
            });

        this.videoUploader.loadUserVideo().subscribe((response: IVideoDataResponse) => {

            const privacyVal: Array<{value: any, title: string}> = [];

            if ( response.privacyVal != undefined ) {
                response.privacyVal.forEach(privacy => privacyVal.push({
                    value: privacy,
                    title: this.translate.instant('video_upload_privacy_' + privacy)
                }));
            }

            if ( response.params != undefined ) {
                this.videoUrl = response.params.uploadUrl;
                this.duration = response.params.duration;
                this.maxSizeBytes = response.params.maxSizeBytes;
            }

            if ( response.videoForPreview != undefined ) {
                this.setValue(response.videoForPreview);
            }

            if ( this.value != null && this.value.privacy != undefined ) {
                if ( this.value.privacy == 'certain_users' ) {
                    this.isShowUserList = true;
                }
            }

            if ( response.userIdList != undefined && response.userIdList.length > 0 ) {
                this.selectUserItems = response.userIdList;
            }

            const formElement = this.getFormElementList(privacyVal);

            formElement.forEach(question => {

                const params = question.params ? question.params : {};

                // create a question
                const item: QuestionBase = this.questionManager.getQuestion(question.type, {
                    key: question.key,
                    label: question.label,
                    placeholder: question.placeholder,
                    values: question.values,
                    value: question.value
                }, params);

                // add validators
                if (question.validators) {
                    item.validators = question.validators;
                }

                this.formElements.push(item);
            });

            this.uploadDescription = this.getDefaultUploadDescription();
            // register all questions inside a form group
            this.form = this.questionControl.toFormGroup(this.formElements, formGroup => {});

            this.form.valueChanges.subscribe(form => {

                if ( form.privacy_settings ) {

                    if ( form.privacy_settings != 'certain_users' )
                    {
                        this.isShowUserList = false;
                    }
                    else
                    {
                        this.isShowUserList = true;
                    }
                }
            });

            this.isPageLoading = false;
            this.refreshView();
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.permissionSubscription.unsubscribe();
    }

    get isVideoValidate(): boolean {
        if ( this.value == null || this.value.fileName == null )
        {
            return false;
        }

        return (!this.isUploadingStarted && this.isVideoValid && this.value.fileName != undefined && this.value.fileName != null &&
            this.value.fileName.length > 0) || (this.value.fileName != undefined && this.value.fileName != null && this.value.fileName.length > 0);
    }

    submitVideo(): void {

        if (this.videoPermission.isAllowedAfterTracking && !this.isDelete) {
            this.submit();

            return;
        }

        if (this.videoPermission.isPromoted) {
            this.permissionsComponent.showAccessDeniedAlert(true);

            return;
        }

        // show a confirmation window
        if (this.videoPermission.creditsCost < 0) {
            const buttons: any[] = [{
                text: this.translate.instant('no')
            }, {
                text: this.translate.instant('yes'),
                handler: () => {
                    this.submit();
                }
            }];

            const confirm = this.alert.create({
                message: this.translate.instant('video_upload_join_confirmation', {
                    count: Math.abs(this.videoPermission.creditsCost)
                }),
                buttons: buttons
            });

            confirm.present();

            return;
        }

        this.submit();
    }


    /**
     * Submit form
     */
    submit(): void {

        this.isSubmit = true;

        // is form valid
        if (!this.form.valid || !this.isVideoValidate) {

            if (this.form.valid && !this.isVideoValidate) {
                this.showNotification('video_input_error');

                return;
            }

            this.showFormGeneralError(this.form);

            return;
        }

        const data: Array<any> = [];
        this.formElements.forEach(formData => {
            if ( formData.key == 'privacy_settings' ) {
                data.push({
                    name: formData.key,
                    value: this.form.value[formData.key]
                });
            }
        });

        data.push({
            name: 'file_name',
            value: this.value.fileName
        });

        let userIdSelect = [];

        this.selectUserItems.forEach((selectUser: IVideoUserDataResponse) => {
            userIdSelect.push(selectUser.userId);
        });

        data.push({
            name: 'user_search',
            value: userIdSelect
        });

        this.videoUploader.updateVideoData(data).subscribe((response) => {

            const notificationToaster = this.toast.create({
                message: response.message,
                closeButtonText: this.translate.instant('ok'),
                showCloseButton: true,
                duration: this.siteConfigs.getConfig('toastDuration')
            });

            notificationToaster.present();

            if ( response.success ) {
                this.nav.pop();
            }

            this.ref.markForCheck();
        });
    }

    /**
     * Get question list
     */
    private getFormElementList(privacyVal: Array<{value: any, title: string}>): Array<any> {
        return [{
            type: QuestionManager.TYPE_SELECT,
            key: 'privacy_settings',
            label: this.translate.instant('privacy_settings_input'),
            placeholder: this.translate.instant('privacy_settings_input_placeholder'),
            values: privacyVal,
            value: ( this.value != null && this.value.privacy != undefined ) ? this.value.privacy : null,
            validators: [
                {name: 'require'}
            ],
            params: {
                hideEmptyValue: false,
                stacked: true
            }
        }];
    }

    trackUserList(index: number, data: IVideoUserDataResponse): number {
        return data.userId;
    }

    viewProfile(data: IVideoUserDataResponse): void {

        this.nav.push(ProfileViewPage, {
            userId: data.userId
        });
    }

    chooseItem(user: IVideoUserDataResponse) {
        let newSelectUserItems = [];

        this.selectUserItems.forEach((selectUser: IVideoUserDataResponse) => {
            if ( user.userId != selectUser.userId ) {
                newSelectUserItems.push(selectUser);
            }
        });

        this.selectUserItems = newSelectUserItems;

        this.refreshView();
    }




    /* VIDEO */

    @ViewChild('custom_video_uploader') set content(fileUploader: FileUploaderComponent) {
        this.fileUploader = fileUploader;
    }

    get isProcessed(): boolean {
        if (this.value && this.value.fileName) {
            return this.value.status == VideoUploadPage.STATUS_PROCESSED;
        }

        return false;
    }

    showVideoUploader(): void {

        this.fileUploader.setupUri('/videos');

        this.fileUploader.showFileChooser();
    }

    showVideo(): void {

        if ( this.value == null ||
            this.value.videos == undefined ||
            this.value.coverImage == undefined ) {

            return;
        }

        let modal = this.modalController.create(ViewVideoComponent, {
            videoUrls: this.value.videos,
            coverImageUrl: this.value.coverImage
        });

        modal.present();
    }

    // TODO ??????? проверить на телефоне
    protected abort(): void {
        this.uploadProcess.unsubscribe();

        this.videoUploader.abortUploading();
    }

    // TODO ??????? проверить на телефоне main showUploadOptions
    showUploadOptions(): void {

        let buttons: any = [];

        buttons.push({
            text: this.translate.instant('take_video'),
            handler: () => this.sendVideo('camera')
        });

        buttons.push({
            text: this.translate.instant('choose_video_from_library'),
            handler: () => this.sendVideo('library')
        });

        let actionSheet = this.actionSheetCtrl.create({
            buttons: buttons
        });

        actionSheet.present();

    }

    // TODO ??????? роверить на телефоне
    protected sendVideo(source: VideoUploaderSource): void {
        this.videoUploader.takeVideo(source);
    }


    public refreshView(): void {

        this.ref.detectChanges();
        this.ref.markForCheck();
    }

    protected getDefaultUploadDescription(): string {
        // check if video uploaded
        if (this.value && this.value.fileName) {
            if (this.value.status != VideoUploadPage.STATUS_PROCESSED) {
                return this.translate.instant('uploaded_video_in_process');
            }

            return this.translate.instant('my_video_file', {
                fileName: this.value.readableFileName
            });
        }

        return this.translate.instant('upload_file_limit', {
            size: this.maxSizeBytes / 1024 / 1024
        });
    }

    protected setValue(value: IVideoPreview = null): void {
        this.value = value;

        this.refreshView();
    }

    clear(): void {
        this.setValue(null);
        this.uploadDescription = this.getDefaultUploadDescription();
        this.isDelete = true;

        this.refreshView();
    }

    startUploadingVideoCallback(): void {
        this.clear();
        this.isUploadingStarted = true;
        this.isVideoValid = false;
        this.uploadProgress = 0;

        // change uploading description
        this.uploadDescription = this.translate.instant('uploading_video_started');

        this.refreshView();
    }

    successVideoUploadCallback(response): void {
        this.isUploadingStarted = false;
        this.uploadProgress = 0;
        this.isVideoValid = true;
        this.isSubmit = false;

        this.setValue(response.data);

        // change uploading description
        this.uploadDescription = this.getDefaultUploadDescription();

        this.refreshView();
    }

    errorVideoUploadCallback(): void {
        this.isUploadingStarted = false;
        this.uploadDescription = this.getDefaultUploadDescription();
        this.uploadProgress = 0;
        this.isVideoValid = false;

        this.refreshView();

        const alert = this.alert.create({
            title: this.translate.instant('error_occurred'),
            subTitle: this.translate.instant('error_uploading_file'),
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }

    progressValue(event: any) {
        this.uploadProgress = event.data;
    }

    protected realUpload(event: ISubscription): void
    {
        this.uploadProcess = event;

        this.clear();
        this.isUploadingStarted = true;
        this.uploadProgress = 0;
        this.isVideoValid = false;

        // change uploading description
        this.uploadDescription = this.translate.instant('uploading_video_started');
        this.refreshView();
    }

    showUserSearchModal(): void {
        let userIdListIgnore = [];

        this.selectUserItems.forEach((selectUser: IVideoUserDataResponse) => {
            userIdListIgnore.push(selectUser.userId);
        });

        const modal = this.modalController.create(UserSearchAutocompleteComponent, {
            userIdListIgnore: userIdListIgnore
        });

        modal.onDidDismiss((items: Array<IVideoUserDataResponse> = []) => {
            if (items.length > 0) {

                items.forEach((item: IVideoUserDataResponse) => {
                    this.selectUserItems.push(item);
                });
            }

            this.refreshView();
        });

        modal.present();
    }

}

