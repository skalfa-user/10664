import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController, ModalController, ActionSheetController, AlertController, NavParams } from 'ionic-angular';

// services
import { SiteConfigsService } from 'services/site-configs';
import { PhotosService, IPhotoData } from 'services/photos';
import { AvatarsService } from 'services/avatars';
import { PermissionsService  } from 'services/permissions';
import { StringUtilsService } from 'services/string-utils';
import { FileUploaderService } from 'services/file-uploader';
import { ISubscription } from 'rxjs/Subscription';
import { UserService } from 'services/user';

// pages
import { BaseUserEdit } from 'pages/user/edit/base.user.edit'

// questions
import { QuestionControlService } from 'services/questions/control.service';

@Component({
    selector: 'edit-user-photos',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        QuestionControlService,
        PhotosService,
        AvatarsService
    ]
})

export class EditUserPhotosPage extends BaseUserEdit implements OnInit, OnDestroy {
    isPageLoading: boolean = false;
    approvalText: string = '';

    protected minPhotosSlots: number = 18;

    private isPhotosLoaded: boolean;
    private myUploadingAvatarSubscription: ISubscription;
    private myAllPhotosSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public questionControl: QuestionControlService,
        public ref: ChangeDetectorRef,
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
        private navParams: NavParams)
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

        this.isPhotosLoaded = this.navParams.get('isPhotosLoaded') === true;
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // watch the logged user's avatar changes
        this.myUploadingAvatarSubscription = this.avatars.watchMyUploadingAvatar().subscribe(avatar => {
            this.myUploadingAvatar = avatar;

            // refresh the generated photo list
            this.generatedPhotoList = this.generatePhotoList(false);
            this.generateApprovalText();

            this.ref.markForCheck();
        });

        // watch all logged user's photos changes
        this.myAllPhotosSubscription = this.photos.watchMyAllPhotos().subscribe(photos => {
            this.myAllPhotos = photos;

            // refresh the generated photo list
            this.generatedPhotoList = this.generatePhotoList(false);
            this.generateApprovalText();

            this.ref.markForCheck();
        });

        // load photos
        if (!this.isPhotosLoaded) {
            this.isPageLoading = true;
            this.ref.markForCheck();

            this.users.loadMe().subscribe(() => {
                this.isPageLoading = false;
                this.ref.markForCheck();
            });
        }
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.myUploadingAvatarSubscription.unsubscribe();
        this.myAllPhotosSubscription.unsubscribe();
    }

    /**
     * Generate approval text
     */
    generateApprovalText(): string {
        // count of not active photos
        const notApprovedPhotos = this.myAllPhotos && this.myAllPhotos.length
            ? this.myAllPhotos.filter((photo: IPhotoData) => photo.approved !== true)
            : [];

        if (notApprovedPhotos.length && this.myUploadingAvatar && this.myUploadingAvatar.active !== true) {
            this.approvalText = this.translate.instant('avatar_and_photos_approval_text', {
                photos: notApprovedPhotos.length
            });

            return;
        }

        if (notApprovedPhotos.length) {
            this.approvalText = this.translate.instant('photos_approval_text', {
                photos: notApprovedPhotos.length
            });

            return;
        }

        if (this.myUploadingAvatar && this.myUploadingAvatar.active !== true) {
            this.approvalText = this.translate.instant('avatar_approval_text');
        }
    }
}
