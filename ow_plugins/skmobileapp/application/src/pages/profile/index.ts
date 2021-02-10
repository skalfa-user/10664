import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef, ViewChild } from '@angular/core';
import { ISubscription } from 'rxjs/Subscription';
import { ToastController, AlertController, ActionSheetController, ModalController, Modal, NavParams, NavController, Slides, Toast } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { PermissionsService, IPermission } from 'services/permissions';
import { AuthService } from 'services/auth';
import { ApplicationService } from 'services/application';
import { SiteConfigsService } from 'services/site-configs';
import { UserService, IUserWithFullData } from 'services/user';
import { IPhotoData } from 'services/photos';
import { MessagesService } from 'services/messages';
import { BookmarksService } from 'services/bookmarks';
import { MatchActionsService } from 'services/match-actions';
import { PaymentsService } from 'services/payments';
import { VideoImService } from 'services/video-im';

// pages
import { EditUserQuestionsPage } from 'pages/user/edit/questions';
import { EditUserPhotosPage } from 'pages/user/edit/photos';
import { MessagesPage } from 'pages/messages';
import { VideoUploadPage } from 'pages/user/video/upload';

// shared components
import { PhotosViewerComponent } from 'shared/components/photos-viewer';
import { FlagComponent } from 'shared/components/flag';
import { MatchActionsComponent } from 'shared/components/match-actions';
import { PermissionsComponent } from  'shared/components/permissions';

// animations
import { like as likeAnimation, dislike as dislikeAnimation} from './animations/match.actions';


@Component({
    selector: 'profile-view',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        BookmarksService
    ],
    animations: [
        likeAnimation,
        dislikeAnimation
    ]
})

export class ProfileViewPage implements OnInit, OnDestroy {
    @ViewChild('photosSlider') sliderComponent: Slides;
    @ViewChild('matchActions') matchActionsComponent: MatchActionsComponent;
    @ViewChild(PermissionsComponent) permissionsComponent: PermissionsComponent;

    isUserBookmarked: boolean = false;
    isChatAllowed: boolean = true;
    isCompatibilityLoaded: boolean = false;
    isPhotosLoaded: boolean = false;
    isBookmarksLoaded: boolean = false;
    isPageLoading: boolean = false;
    userData: IUserWithFullData;
    editUserQuestionsPage = EditUserQuestionsPage;
    editUserPhotosPage = EditUserPhotosPage;
    defaultUserMatchType: string = 'default';
    currentUserMatchType: string = '';

    private isPrevPageMessages: boolean = false;
    private bookmarkToaster: Toast;
    private previewPhotosModal: Modal;
    private flagModal: Modal;
    private isUserLoaded: boolean = false;
    private userId: number;
    private viewUserPermission: IPermission;
    private viewPhotoPermission: IPermission;
    private callPermission: IPermission;
    private timedCallPermission: IPermission;
    private permissionSubscription: ISubscription;
    private siteConfigsSubscription: ISubscription;
    private trackedPhotosUrls: Array<string> = [];
    private videoPermission: IPermission;
    private isUserVideoLoaded: boolean = false;

    /**
     * Constructor
     */
    constructor(
        public payments: PaymentsService,
        public application: ApplicationService,
        public siteConfigs: SiteConfigsService,
        private messages: MessagesService,
        private matchActions: MatchActionsService,
        private bookmarks: BookmarksService,
        private toast: ToastController,
        private alert: AlertController,
        private translate: TranslateService,
        private actionSheet: ActionSheetController,
        private modal: ModalController,
        private nav: NavController,
        private users: UserService,
        private navParams: NavParams,
        private auth: AuthService,
        private ref: ChangeDetectorRef,
        private permissions: PermissionsService,
        private videoIm: VideoImService)
    {
        this.userId = this.navParams.get('userId');
        this.isPrevPageMessages = this.navParams.get('isPrevPageMessages') === true;
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // watch the config updates
        this.siteConfigsSubscription = this.siteConfigs.watchConfig('activePlugins').subscribe(() => this.ref.markForCheck());

        // watch the permission updates
        this.permissionSubscription = this.permissions
            .watchMeGroup([
                'base_view_profile',
                'photo_view',
                'videoim_video_im_call',
                'videoim_video_im_timed_call',
                'cvideoupload_upload_video',
            ])
            .subscribe((permissions: Array<IPermission>) => {
                [this.viewUserPermission, this.viewPhotoPermission, this.callPermission, this.timedCallPermission, this.videoPermission] = permissions;

                // show the initial slide with user's avatar when permissions are changed
                if (this.sliderComponent && !this.isPageLoading) {
                    this.sliderComponent.slideTo(0);
                }

                // load the user's data if permissions are allowed
                if (this.isViewUserAllowed && !this.isUserLoaded) {
                    this.loadUser();
                }

                // close the preview photos modal window if view photo is not permitted
                if (!this.isViewPhotoAllowed && this.previewPhotosModal) {
                    this.previewPhotosModal.dismiss();
                    this.previewPhotosModal = null;
                }

                this.ref.markForCheck();
            });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.siteConfigsSubscription.unsubscribe();
        this.permissionSubscription.unsubscribe();

        // close all active modal windows
        if (this.previewPhotosModal) {
            this.previewPhotosModal.dismiss();
        }

        if (this.flagModal) {
            this.flagModal.dismiss();
        }
    }

    /**
     * Page is going to be active
     */
    ionViewWillEnter(): void {
        // we should see the actual info of the current user
        // he might change their questions data or photos
        if (this.isViewUserAllowed && this.isUserLoaded && this.isProfileOwner) { 
            this.loadUser();
        }
    }

    /**
     * Is view user allowed
     */
    get isViewUserAllowed(): boolean {
        return this.isProfileOwner || this.viewUserPermission.isAllowed === true;
    }

    /**
     * Is view photo allowed
     */
    get isViewPhotoAllowed(): boolean {
        return this.isProfileOwner || this.viewPhotoPermission.isAllowed === true;
    }

    /**
     * Is call user promoted
     */
    get isCallAllowed(): boolean {
        return this.callPermission && !this.isProfileOwner &&
            (this.callPermission.isAllowed || this.callPermission.isPromoted);
    }

    /**
     * Is profile owner
     */
    get isProfileOwner(): boolean {
        return this.auth.getUserId() == this.userId;
    }

    /**
     * All user photos
     */
    get allUserPhotos(): Array<IPhotoData> {        
        if (this.userData.photos && this.userData.photos.length) {
            return this.userData.photos;
        }

        return [];
    }

    /**
     * Get first user's photos
     */
    get firstUserPhotos(): Array<IPhotoData> {
        return this.allUserPhotos.slice(0, this.firstUserPhotosLimit);
    }

    /**
     * First user photos limit
     */
    get firstUserPhotosLimit(): number {
        return this.siteConfigs.getConfig('profilePhotosLimit');
    }

    /**
     * Dislike user
     */
    dislikeUser(): void {
        // like should be removed before dislike can be clicked
        if (this.currentUserMatchType == MatchActionsComponent.TYPE_LIKE) {
            return;
        }

        this.matchActionsComponent.dislikeUser(this.userId, this.userData.user.userName);
    }

    /**
     * Like user
     */
    likeUser(): void {
        const userMatch = this.matchActions.getMatch(this.userId);

        // delete the user's match
        if (this.currentUserMatchType == MatchActionsComponent.TYPE_LIKE && userMatch) {
            this.matchActionsComponent.deleteMatch(this.userId, userMatch.id);

            return;
        }

        // like the user
        this.matchActionsComponent.likeUser(this.userId, this.userData.user.userName);
    }

    /**
     * On user match changed
     */
    onUserMatchChanged(): void {
        // the user's like has been deleted
        if (this.currentUserMatchType == MatchActionsComponent.TYPE_LIKE) {
            this.currentUserMatchType = this.defaultUserMatchType;
            this.isChatAllowed = this.siteConfigs.isTinderSearchMode() ? false : true;

            this.ref.markForCheck();

            return;
        }

        // the user's like has been added
        this.currentUserMatchType = MatchActionsComponent.TYPE_LIKE;
        this.ref.markForCheck();
    }

    /**
     * Bookmark user
     */
    bookmarkUser(): void {
        this.bookmarks.stopAllUserSubscriptions(this.userId);

        // close previously opened toasters
        if (this.bookmarkToaster) {
            this.bookmarkToaster.dismiss();
        }

        // remove the bookmark
        if (this.isUserBookmarked) {
            const bookmark = this.bookmarks.getBookmark(this.userId);
            this.isUserBookmarked = false;
            this.ref.markForCheck();

            if (bookmark) {
                this.bookmarks.deleteBookmark(bookmark.id, this.userId);
            }

            this.bookmarkToaster = this.toast.create({
                message: this.translate.instant('profile_removed_from_bookmarks'),
                closeButtonText: this.translate.instant('ok'),
                showCloseButton: true,
                duration: this.siteConfigs.getConfig('toastDuration')
            });

            this.bookmarkToaster.present();

            return;
        }

        this.bookmarkToaster = this.toast.create({
            message: this.translate.instant('profile_added_to_bookmarks'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        this.bookmarkToaster.present();

        // add a new bookmark
        this.bookmarks.addBookmark(this.userId);

        this.isUserBookmarked = true;
        this.ref.markForCheck();
    }

    /**
     * Show chat
     */
    showChat(): void {
        // don't open the messages page twice it takes a lot of resources
        if (this.isPrevPageMessages) {
            this.returnBack();

            return;
        }

        this.nav.push(MessagesPage, {
            userId: this.userId,
            isPrevPageProfile: true
        });
    }


    uploadVideo(): void {

        if (this.videoPermission.isAllowedAfterTracking) {
            this.nav.push(VideoUploadPage);

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
                    this.nav.push(VideoUploadPage);
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

        this.nav.push(VideoUploadPage);
    }

    /**
     * Track viewed photos
     */
    trackViewedPhotos(): void {
        if (!this.isProfileOwner && this.isViewPhotoAllowed && this.sliderComponent.getActiveIndex()) {
            const activePhotoIndex: number = this.sliderComponent.getActiveIndex() - 1;

            if (this.userData.photos[activePhotoIndex]
                    && activePhotoIndex < this.firstUserPhotosLimit
                    && !this.isPhotoTracked(this.userData.photos[activePhotoIndex].bigUrl)) {

                // track action
                this.trackViewPhoto(this.userData.photos[activePhotoIndex].bigUrl);
            }
        }
    }

    /**
     * Show profile actions
     */
    showProfileActions(): void {
        if (!this.isProfileOwner) {
            const actionSheet = this.actionSheet.create({
                buttons: [{
                    text: this.translate.instant('flag_profile'),
                    handler: () => {
                        this.flagModal = this.modal.create(FlagComponent, {
                            identityId: this.userId,
                            entityType: 'user_join'
                        });

                        this.flagModal.onDidDismiss((status: {reported: boolean}) => {
                            if (status.reported) {
                                const toast = this.toast.create({
                                    message: this.translate.instant('profile_reported'),
                                    closeButtonText: this.translate.instant('ok'),
                                    showCloseButton: true,
                                    duration: this.siteConfigs.getConfig('toastDuration')
                                });

                                toast.present();
                            }
                        });

                        this.flagModal.present();
                    }
                }, {
                    text: this.users.isUserBlocked(this.userData.user)
                        ? this.translate.instant('unblock_profile') 
                        : this.translate.instant('block_profile'),
                    handler: () => {
                        // unblock profile
                        if (this.users.isUserBlocked(this.userData.user)) {
                            this.users.unblockUser(this.userId).subscribe();

                            return;
                        }

                        // block profile
                        const confirm = this.alert.create({
                            message: this.translate.instant('block_profile_confirmation'),
                            buttons: [{
                                text: this.translate.instant('cancel')
                            }, {
                                text: this.translate.instant('block_profile'),
                                handler: () => {
                                    this.users.blockUser(this.userId).subscribe();
                                }
                            }]
                        });

                        confirm.present();
                    }
                }]
            });

            actionSheet.present();
        }
    }

    /**
     * Show photo actions
     */
    showPhotoActions(photoId: number | string): void {
        if (!this.isProfileOwner) {
            const actionSheet = this.actionSheet.create({
                buttons: [{
                    text: this.translate.instant('flag_photo'),
                    handler: () => {
                        this.flagModal = this.modal.create(FlagComponent, {
                            identityId: photoId,
                            entityType: 'photo_comments'
                        });

                        this.flagModal.onDidDismiss((status: {reported: boolean}) => {
                            if (status.reported) {
                                const toast = this.toast.create({
                                    message: this.translate.instant('photo_reported'),
                                    closeButtonText: this.translate.instant('ok'),
                                    showCloseButton: true,
                                    duration: this.siteConfigs.getConfig('toastDuration')
                                });

                                toast.present();
                            }
                        });

                        this.flagModal.present();
                    }
                }]
            });

            actionSheet.present();
        }
    }

    /**
     * Return back
     */
    returnBack(): void {
        this.nav.pop();
    }

    /**
     * View photos
     */
    viewPhotos(activeUrl = ''): void {
        const urls: Array<string> = [];
        let avatarUrl: string = '';

        if (this.userData.avatar) {
            avatarUrl = this.isProfileOwner 
                ? this.userData.avatar.pendingBigUrl 
                : this.userData.avatar.bigUrl;

            urls.push(avatarUrl);

            // don't track the avatar
            if (!this.isPhotoTracked(avatarUrl)) {
                this.trackedPhotosUrls.push(avatarUrl);
            }
        }

        // collect all photos
        if (this.isViewPhotoAllowed) {
            this.allUserPhotos.forEach(photo => {
                urls.push(photo.bigUrl);
            });
        }

        // show photos viewer
        this.previewPhotosModal = this.modal.create(PhotosViewerComponent, {
            activeIndex: activeUrl ? urls.indexOf(activeUrl) : 0,
            urls: urls,
            isFlagActive: !this.isProfileOwner,
            onPhotoViewedCallback: (url: string) => {
                // track the photos viewing 
                if (!this.isProfileOwner && !this.isPhotoTracked(url)) {
                    this.trackViewPhoto(url);
                }
            },
            onPhotoFlaggedCallback: (contentUrl: string) => {
                // try to define a kind of content
                if (avatarUrl === contentUrl) { // the avatar is related to user
                    this.showProfileActions();

                    return;
                }

                // find a photo using received url
                const photo: IPhotoData = this.allUserPhotos.find(photo => {
                    if (photo.bigUrl === contentUrl) {
                        return true;
                    }
                });

                if (photo) {
                    this.showPhotoActions(photo.id);
                }
            }
        });

        this.previewPhotosModal.present();
    }

    /**
     * Call user
     */
    callUser(): void {
        if (this.callPermission.isPromoted) {
            this.permissionsComponent.showAccessDeniedAlert();

            return;
        }

        const trackCreditsType: string = this.siteConfigs.getConfig('videoim_track_credits_type');

        // Even if this user has no timedCallPermission permission he can pass it if timedCallPermission is set to track interlocutor
        if (this.timedCallPermission.isPromoted && trackCreditsType != 'interlocutor') {
            this.permissionsComponent.showAccessDeniedAlert();

            return;
        }

        const permissions = this.userData.user.videoImCallPermission;

        if (permissions.isPermitted) {
            // setting active interlocutor data
            this.videoIm.setActiveInterlocutorData(this.userId, true);
        }
        else {
            if (permissions.errorMessage) {	
                const toaster = this.toast.create({	
                    message: permissions.errorMessage,	
                    closeButtonText: this.translate.instant('ok'),	
                    showCloseButton: true,	
                    duration: this.siteConfigs.getConfig('toastDuration')	
                });	
            
                toaster.present();	
            }
        }
    }

    /**
     * Load user
     */
    private loadUser(): void {
        this.isPageLoading = true;
        this.ref.markForCheck();

        const relations: Array<string> = [];

        if (this.isViewPhotoAllowed) {
            this.isPhotosLoaded = true;
            relations.push('photos');
        }

        if (this.siteConfigs.isPluginActive('bookmarks')) {
            this.isBookmarksLoaded = true;
            relations.push('bookmark');
        }

        if (this.siteConfigs.isPluginActive('matchmaking')) {
            this.isCompatibilityLoaded = true;
        }

        // load video
        if (this.siteConfigs.isPluginActive('cvideoupload')) {
            this.isUserVideoLoaded = true;
            relations.push('video');
        } else {
            this.isUserVideoLoaded = false;
        }

        // load user data
        this.users.loadFullUserData(this.userId, relations, false).subscribe(() => {
            this.userData = this.users.getUserWithFullData(this.userId);
            this.isUserBookmarked = this.userData.user.bookmark !== null;
            this.currentUserMatchType = this.userData.matchAction 
                ? this.userData.matchAction.type 
                : this.defaultUserMatchType;

            this.isChatAllowed = this.messages.isChatAllowed(this.userId);

            this.isPageLoading = false;
            this.isUserLoaded = true;
            this.ref.markForCheck();
        }, () => {
            const toaster= this.toast.create({
                message: this.translate.instant('profile_cannot_be_viewed'),
                closeButtonText: this.translate.instant('ok'),
                showCloseButton: true,
                duration: this.siteConfigs.getConfig('toastDuration')
            });

            toaster.present();

            // return to the previous page
            this.nav.pop();
        });
    }

    /**
     * Is photo tracked
     */
    private isPhotoTracked(url: string): boolean {
        return this.trackedPhotosUrls.indexOf(url) !== -1;
    }

    /**
     * Track view photo
     */
    private trackViewPhoto(url: string): void {
        this.trackedPhotosUrls.push(url);
        this.permissions.trackAction('photo', 'view').subscribe();
    }
}
