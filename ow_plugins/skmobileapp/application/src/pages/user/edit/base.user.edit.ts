import { ViewChild } from '@angular/core';
import { TranslateService } from 'ng2-translate';
import { NavController, ToastController, ModalController, ActionSheetController, AlertController } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';

// services
import { SiteConfigsService } from 'services/site-configs';
import { QuestionControlService } from 'services/questions/control.service';
import { PhotosService, IPhotoData } from 'services/photos';
import { AvatarsService, IAvatarData } from 'services/avatars';
import { PermissionsService } from 'services/permissions';
import { StringUtilsService } from 'services/string-utils';
import { FileUploaderService, IFileUploadOptions, IFileUploadResult } from 'services/file-uploader';

// pages
import { BaseFormBasedPage } from 'pages/base.form.based'

// components
import { PhotosViewerComponent } from 'shared/components/photos-viewer';
import { FileUploaderComponent } from  'shared/components/file-uploader';
import { PermissionsComponent } from  'shared/components/permissions';

export interface IPhotoUnit {
    id: number | string;
    url: string;
    bigUrl: string;
    type: 'avatar' | 'photo' | 'more';
    isActive: boolean;
    isPending: boolean;
}

export abstract class BaseUserEdit extends BaseFormBasedPage {
    @ViewChild('photoUploader') photoUploaderComponent: FileUploaderComponent;
    @ViewChild('avatarUploader') avatarUploaderComponent: FileUploaderComponent;
    @ViewChild(PermissionsComponent) permissionsComponent: PermissionsComponent;

    generatedPhotoList: Array<Array<IPhotoUnit>> = [];

    protected myUploadingAvatar: IAvatarData;
    protected myAllPhotos: Array<IPhotoData> = [];
    protected minPhotosSlots: number = 9;
    protected photosPerRow: number = 3;

    /**
     * Constructor
     */
    constructor(
        protected nav: NavController,
        protected fileUploader: FileUploaderService,
        protected stringUtils: StringUtilsService,
        protected avatars: AvatarsService,
        protected photos: PhotosService,
        protected alert: AlertController,
        protected actionSheet: ActionSheetController,
        protected permissions: PermissionsService,
        protected modal: ModalController,
        protected questionControl: QuestionControlService,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        protected toast: ToastController) 
    {
        super(
            questionControl, 
            siteConfigs,
            translate,
            toast
        );
    }

    /**
     * Is avatar required
     */
    get isAvatarRequired(): boolean {
        return this.siteConfigs.getConfig('isAvatarRequired');
    }

    /**
     * Is avatar valid
     */
    get isAvatarValid(): boolean {
        return !this.isAvatarRequired || (this.isAvatarRequired && this.myUploadingAvatar !== undefined);
    }

    /**
     * Is avatar pending
     */
    get isAvatarPending(): boolean {
        return this.myUploadingAvatar !== undefined && this.avatars.isAvatarPending(this.myUploadingAvatar);
    }

    /**
     * Get avatar max size
     */
    get getAvatarMaxSize(): Array<string> {
        return this.siteConfigs.getConfig('avatarMaxUploadSize');
    }

    /**
     * Photo max upload size
     */
    get photoMaxUploadSize(): number {
        return this.siteConfigs.getConfig('photoMaxUploadSize');
    }

    /**
     * Get image mime types
     */
    get getImageMimeTypes(): any {
        return this.siteConfigs.getConfig('validImageMimeTypes');
    }
 
    /**
     * Start uploading photo
     */
    startUploadingPhoto(response: IFileUploadResult): void {
        const fakeId: string = this.stringUtils.getRandomString();
        const fileSystemUrl: string = window.URL.createObjectURL(response.data);

        // upload a photo
        this.photos.beforeUploadMyPhoto(fakeId, fileSystemUrl);

        const fileUploadOptions: IFileUploadOptions = {
            uri: '/photos',
            fileName: 'file',
            allowedMimeTypes: [],
            maxFileSize: 0,
            isBroadcastError: false
        };

        const uploading: Observable<any> = this.fileUploader.upload(response.data, fileUploadOptions);

        uploading.subscribe((response: IFileUploadResult) => {
            switch(response.type) {
                case FileUploaderService.SUCCESS_RESULT :
                case FileUploaderService.UPLOAD_ERROR_RESULT :
                    response.type == FileUploaderService.SUCCESS_RESULT
                        ? this.photos.afterUploadMyPhoto(fakeId, response.data)
                        : this.photos.errorUploadPhoto(fakeId);

                    window.URL.revokeObjectURL(fileSystemUrl);

                    if (response.type == FileUploaderService.SUCCESS_RESULT) {
                        this.showNotification('photo_has_been_uploaded');
                    }
                    else
                    {
                        try {
                            const json = response.data.json();

                            json.shortDescription && this.showNotification(json.shortDescription)
                        }
                        catch (e) { }
                    }

                    break;

                default :
            }
        });
    }

    /**
     * Start uploading avatar
     */
    startUploadingAvatar(response: IFileUploadResult): void {
        const fakeId: string = this.stringUtils.getRandomString();
        const fileSystemUrl: string = window.URL.createObjectURL(response.data);

        // upload an avatar
        this.avatars.beforeUploadMyAvatar(fakeId, fileSystemUrl);

        const fileUploadOptions: IFileUploadOptions = {
            uri: '/avatars/me',
            fileName: 'file',
            allowedMimeTypes: [],
            maxFileSize: 0,
            isBroadcastError: true
        };

        const uploading: Observable<any> = this.fileUploader.upload(response.data, fileUploadOptions);

        uploading.subscribe((response: IFileUploadResult) => {
            switch(response.type) {
                case FileUploaderService.SUCCESS_RESULT :
                case FileUploaderService.UPLOAD_ERROR_RESULT :
                    response.type == FileUploaderService.SUCCESS_RESULT
                        ? this.avatars.afterUploadMyAvatar(fakeId, response.data)
                        : this.avatars.errorUploadAvatar(fakeId);

                    window.URL.revokeObjectURL(fileSystemUrl);

                    if (response.type == FileUploaderService.SUCCESS_RESULT) {
                        this.showNotification('avatar_has_been_uploaded');
                    }

                    break;

                default :
            }
        });
    }
 
    /**
     * Press photo
     */
    pressPhoto(row: number, col: number): void {
        const photo: IPhotoUnit = this.generatedPhotoList[row][col];

        if (photo.bigUrl && !photo.isPending) { // show actions
            this.showPhotoActions(photo);
        }
    }

    /**
     * Tap photo
     */
    tapPhoto(row: number, col: number): void {
        const photo: IPhotoUnit = this.generatedPhotoList[row][col];

        switch (photo.type) {
            case 'more' :
                this.showPhotoActions(photo);
                break;

            default :
                // preview photo
                if (photo.bigUrl) {
                    let photoUrls: Array<string> = [];

                    // process list of urls
                    this.generatedPhotoList.forEach(cols => {
                        cols.forEach((photo: IPhotoUnit) => {
                            if (photo.bigUrl) {
                                photoUrls.push(photo.bigUrl);
                            }
                        });
                    });

                    const modalWindow = this.modal.create(PhotosViewerComponent, {
                        activeIndex: this.photosPerRow * row + col - (!this.myUploadingAvatar ? 1 : 0), // exclude the avatar
                        urls: photoUrls
                    });

                    modalWindow.present();

                    return;
                }

                this.showPhotoActions(photo); // show actions
        }
    }
 
    /**
     * Show all actions
     */
    showAllActions(): void {
        let buttons = <any>[];

        buttons = buttons.concat(this.getAvatarActions());
        buttons = buttons.concat(this.getPhotoActions());

        buttons.push({
            text: this.translate.instant('cancel'),
            role: 'cancel'
        });

        const actionSheet = this.actionSheet.create({
            buttons: buttons,
            enableBackdropDismiss: false
        });

        actionSheet.present();
    }

    /**
     * Generate photo list
     */
    generatePhotoList(isViewMoreSlotActive: boolean = true, maxPhotos: number = 0): Array<Array<IPhotoUnit>> {
        const photoList: Array<IPhotoUnit> = [];
        const extraSlots: number = isViewMoreSlotActive 
            ? 2 // including the avatar and view more slots
            : 1; // including only the avatar slot

        const myAllPhotosCount: number = this.myAllPhotos && this.myAllPhotos.length
            ? (maxPhotos && this.myAllPhotos.length > maxPhotos ? maxPhotos : this.myAllPhotos.length)
            : 0;

        // add extra slots
        const photosWithSlots: number = myAllPhotosCount
            ? myAllPhotosCount + extraSlots
            : extraSlots;

        let photosLimit: number = photosWithSlots >= this.minPhotosSlots
            ? photosWithSlots
            : this.minPhotosSlots;

        const defaultAvatar: string = this.siteConfigs.getConfig('defaultAvatar');

        // add extra slots
        if (photosLimit % this.photosPerRow) {
            photosLimit += (this.photosPerRow - photosLimit % this.photosPerRow);
        }

        // process photos
        for (let i = 0; i < photosLimit; i++) {
            // add the avatar inside the list
            if (!i) {
                const avatar: IPhotoUnit = {
                    id: this.myUploadingAvatar ? this.myUploadingAvatar.id  : null,
                    url: this.myUploadingAvatar ? this.myUploadingAvatar.pendingUrl : defaultAvatar,
                    bigUrl: this.myUploadingAvatar ? this.myUploadingAvatar.pendingBigUrl : null,
                    type: 'avatar',
                    isActive: !this.myUploadingAvatar ? true : (this.myUploadingAvatar.active === true),
                    isPending: !this.myUploadingAvatar ? false : this.avatars.isAvatarPending(this.myUploadingAvatar)
                };

                photoList.push(avatar);

                continue;
            }

            // add the view more slot
            if (i == photosLimit - 1 && isViewMoreSlotActive) {
                const more: IPhotoUnit = {
                    id: null,
                    url: null,
                    bigUrl: null,
                    type: 'more',
                    isActive: true,
                    isPending: false
                };

                photoList.push(more);

                continue;
            }

            // add photos
            const photoDetails = this.myAllPhotos && this.myAllPhotos[i - 1] 
                ? this.myAllPhotos[i - 1] 
                : null;

            const photo: IPhotoUnit = {
                id: photoDetails ? photoDetails.id : null,
                url: photoDetails ? photoDetails.url : null,
                bigUrl: photoDetails ? photoDetails.bigUrl : null,
                type: 'photo',
                isActive: photoDetails ? photoDetails.approved : true,
                isPending: !photoDetails ? false : this.photos.isPhotoPending(photoDetails)
            };

            photoList.push(photo);
        }

        const processedPhotos: Array<Array<IPhotoUnit>> = [];

        // chunk photos
        for (let i = 0; i < photoList.length; i += this.photosPerRow) {
            processedPhotos.push(photoList.slice(i, i + this.photosPerRow));
        }

        return processedPhotos;
    }

    /**
     * Show photo actions
     */
    protected showPhotoActions(photo: IPhotoUnit): void {
        let buttons: Array<any> = [];

        switch (photo.type) {
            case 'photo' :
                buttons = this.getPhotoActions(photo);
                break;

            case 'avatar' :
                buttons = this.getAvatarActions(photo.id);
                break;

            case 'more' :
            default :
                buttons = this.getExtraPhotoActions();
        }

        if (buttons.length) {
            buttons.push({
                text: this.translate.instant('cancel'),
                role: 'cancel'
            });

            const actionSheet = this.actionSheet.create({
                buttons: buttons,
                enableBackdropDismiss: false
            });

            actionSheet.present();
        }
    }

    /**
     * Get photo actions
     */
    protected getPhotoActions(photo?: IPhotoUnit): Array<any> {
        const buttons: any = [];
        const photoUploadPermission = this.permissions.getMe('photo_upload');

        // upload photo
        if (photoUploadPermission.isAllowed || photoUploadPermission.isPromoted) {
            buttons.push({
                text: this.translate.instant('upload_photo'),
                handler: () => photoUploadPermission.isAllowed
                    ? this.photoUploaderComponent.showFileChooser()
                    : this.permissionsComponent.showAccessDeniedAlert()
            });
        }

        if (photo && photo.id) {
            if (!this.isAvatarPending) {
                // set as avatar
                buttons.push({
                    text: this.translate.instant('set_avatar'),
                    handler: () => {
                        this.photos.setPhotoAsMyAvatar(photo.id, photo.bigUrl);
                        this.showNotification('photo_set_avatar');
                    }
                });
            }

            // delete photo
            buttons.push({
                text: this.translate.instant('delete_photo'),
                handler: () => {
                    let photoButtons: any[] = [];

                    photoButtons = [{
                        text: this.translate.instant('no')
                    }, {
                        text: this.translate.instant('yes'),
                        handler: () => {
                            this.photos.deleteMyPhoto(photo.id);
                            this.showNotification('photo_has_been_deleted');
                        }
                    }];

                    const confirm = this.alert.create({
                        message: this.translate.instant('delete_photo_confirmation'),
                        buttons: photoButtons
                    });

                    confirm.present();
                }
            });
        }

        return buttons;
    }

    /**
     * Get avatar actions
     */
    protected getAvatarActions(id?: number | string): Array<any> {
        let buttons: any;

        // upload avatar
        buttons = [{
            text: this.translate.instant('choose_avatar'),
            handler: () => this.avatarUploaderComponent.showFileChooser()
        }];

        if (id && !this.isAvatarRequired) {
            // delete avatar
            buttons.push({
                text: this.translate.instant('delete_avatar'),
                handler: () => {
                    let avatarButtons: any[] = [];

                    avatarButtons = [{
                        text: this.translate.instant('no')
                    }, {
                        text: this.translate.instant('yes'),
                        handler: () => {
                            this.avatars.deleteMyAvatar(id);
                            this.showNotification('avatar_has_been_deleted');
                        }
                    }];

                    const confirm = this.alert.create({
                        message: this.translate.instant('delete_avatar_confirmation'),
                        buttons: avatarButtons
                    });

                    confirm.present();
                }
            });
        }

        return buttons;
    }

    /**
     * Get extra photo actions
     */
    protected getExtraPhotoActions(): Array<any> {
        return [];
    }
}
