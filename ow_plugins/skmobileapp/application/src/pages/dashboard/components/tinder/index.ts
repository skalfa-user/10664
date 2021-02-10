
import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, Input, ChangeDetectorRef, ViewChild, NgZone } from '@angular/core';
import { Geolocation, GeolocationOptions, Geoposition } from '@ionic-native/geolocation';
import { ToastController, NavController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { ISubscription } from 'rxjs/Subscription';
import { StackConfig, DragEvent, Direction } from 'angular2-swing';

// services
import { PermissionsService, IPermission } from 'services/permissions';
import { UserService, IUserResponse, IUserWithAvatar } from 'services/user';
import { ApplicationService } from 'services/application';
import { SiteConfigsService } from 'services/site-configs';
import { PersistentStorageService } from 'services/persistent-storage';
import { MatchActionsService } from 'services/match-actions';
import { PaymentsService } from 'services/payments';

// pages
import { ProfileViewPage } from 'pages/profile';

// components
import { MatchActionsComponent } from 'shared/components/match-actions';

@Component({
    selector: 'tinder',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        Geolocation
    ]
})

export class TinderComponent implements OnInit, OnDestroy {
    @Input() isDashboardLoading: boolean;
    @ViewChild(MatchActionsComponent) matchActionsComponent: MatchActionsComponent;

    isPreviewModeActive: boolean = false;
    swipeDirectionLeft: string = 'left'
    swipeDirectionRight: string = 'right'
    swipeDirection: string = '';
    userIdList: Array<number | string> = [];
    userList: Array<IUserResponse> = [];
    isCheckingLocationInProgress: boolean = false;
    my: IUserWithAvatar;
    cardsStackConfig: StackConfig;
    isNoUsersDescriptionVisible: boolean = false;

    private isTinderPageActive: boolean = true;
    private checkNewUsersHandler: number;
    private minThrowOutDistance: number = 700;
    private maxThrowOutDistance: number = 800;
    private checkingLocationTimeout: number = 5000;
    private isSearchStarted: boolean = false;
    private searchPermission: IPermission;
    private searchPermissionSubscription: ISubscription;
    private searchUsersSubscription: ISubscription;
    private siteConfigsSubscription: ISubscription;
    private userSubscription: ISubscription;
    private applicationLocationSubscription: ISubscription;
    private matchActionsSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public payments: PaymentsService,
        public siteConfigs: SiteConfigsService,
        private zone: NgZone,
        private application: ApplicationService,
        private nav: NavController,
        private matchActions: MatchActionsService,
        private persistentStorage: PersistentStorageService,
        private translate: TranslateService,
        private toast: ToastController,
        private geoLocation: Geolocation,
        private user: UserService,
        private permissions: PermissionsService,
        private ref: ChangeDetectorRef) 
    {
        // full list of options: https://github.com/gajus/swing#configuration
        this.cardsStackConfig = {
            // a value between 0 and 1 indicating the completeness of the throw out condition.
            throwOutConfidence: (offsetX, offsetY, element) => {
                return this.isCardThrowAllowed()
                    ? Math.min(Math.abs(offsetX) / (element.offsetWidth / 2), 1)
                    : 0;
            },
            allowedDirections: [Direction.LEFT, Direction.RIGHT],
            minThrowOutDistance: this.minThrowOutDistance,
            maxThrowOutDistance: this.maxThrowOutDistance
        };
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // watch likes/dislikes 
        this.matchActionsSubscription = this.matchActions.matchCreated$.subscribe(userId => {
            // delete the user from user list
            this.userList = this.userList.filter(user => user.id !== userId);

            if (!this.userList.length && this.isSearchAllowed && this.isUserLocationDefined) {
                this.searchUsers();
            }

            this.ref.markForCheck();
        });

        // watch the current user's location
        this.applicationLocationSubscription = this.application.watchLocation().subscribe(location => {
            // search users
            if (location.latitude && location.longitude && this.isSearchAllowed && !this.isSearchStarted) {
                this.searchUsers();
                this.ref.markForCheck();
            }
        });

        // watch the current user's data
        this.userSubscription = this.user.watchMe().subscribe(user => {
            this.my = user;
            this.ref.markForCheck();
        });

        // watch the config updates
        this.siteConfigsSubscription = this.siteConfigs.watchConfig('activePlugins').subscribe(() => this.ref.markForCheck());

        // watch the permission updates
        this.searchPermissionSubscription = this.permissions
            .watchMe('base_search_users')
            .subscribe((permission: IPermission) => {
                this.searchPermission = permission;

                // search users
                if (this.isSearchAllowed && !this.isSearchStarted && this.isUserLocationDefined) {
                    this.searchUsers();
                }

                this.ref.markForCheck();
            });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.matchActionsSubscription.unsubscribe();
        this.applicationLocationSubscription.unsubscribe();
        this.userSubscription.unsubscribe();
        this.siteConfigsSubscription.unsubscribe();
        this.searchPermissionSubscription.unsubscribe();

        // clear the timeout
        if (this.checkNewUsersHandler) {
            clearTimeout(this.checkNewUsersHandler);
        }

        // stop previous search requests 
        if (this.searchUsersSubscription) {
            this.searchUsersSubscription.unsubscribe();
        }

        this.isTinderPageActive = false;
    }

    /**
     * Is search allowed
     */
    get isSearchAllowed(): boolean {
        return this.searchPermission && this.searchPermission.isAllowed === true;
    }

    /**
     * Is user location defined
     */
    get isUserLocationDefined(): boolean {
        const location = this.application.getLocation();

        if (location.latitude && location.longitude) {
            return true;
        }

        return false;
    }
 
    /**
     * Get active user
     */
    get activeUser(): IUserResponse | undefined {
        if (this.userList.length) {
            return this.userList[this.userList.length - 1];
        }
    }

    /**
     * Show short profile info
     */
    showShortProfileInfo(): void {
        this.isPreviewModeActive = !this.isPreviewModeActive;
        this.ref.detectChanges();
    }

    /**
     * View profile
     */
    viewProfile(): void {
        const user = this.activeUser;

        if (user) {
            this.nav.push(ProfileViewPage, {
                userId: user.id
            });
        }
    }

    /**
     * Tinder card create match
     */
    tinderCardCreateMatch(isLike: boolean): void {
        const user = this.activeUser;

        if (user) {
            if (isLike) {
                this.application.isLanguageDirectionLtr() ? this.likeUser(user) : this.dislikeUser(user);

                return
            }

            // dislike
            this.application.isLanguageDirectionLtr() ? this.dislikeUser(user) : this.likeUser(user);
        }
    }

    /**
     * Like user
     */
    likeUser(user?: IUserResponse): void {
        if (!user) {
            user = this.activeUser;
        }

        if (user) {
            this.matchActionsComponent.likeUser(user.id, user.userName);
        }
    }

    /**
     * Dislike user
     */
    dislikeUser(user?: IUserResponse): void {
        if (!user) {
            user = this.activeUser;
        }

        if (user) {
            this.matchActionsComponent.dislikeUser(user.id, user.userName);
        }
    }

    /**
     * Tinder card stop moving
     */
    tinderCardStopMoving(): void {
        if (!this.isCardThrowAllowed()) {
            switch (this.swipeDirection) {
                case this.swipeDirectionLeft :
                    this.tinderCardCreateMatch(false);
                    break;

                case this.swipeDirectionRight :
                    this.tinderCardCreateMatch(true);
                    break;

                default :
            }
        }

        this.swipeDirection = '';
        this.ref.detectChanges();
    }

    /**
     * Tinder card is moving
     */
    tinderCardMoving(event: DragEvent): void {
        let newCardDirection: string = '';

        if (event.throwDirection == Direction.LEFT || event.throwDirection == Direction.RIGHT) {
            newCardDirection = event.throwDirection == Direction.LEFT 
                ? this.swipeDirectionLeft 
                : this.swipeDirectionRight;
        }

        if (newCardDirection !== this.swipeDirection) {
            this.swipeDirection = newCardDirection;

            this.ref.detectChanges();
        }
    }

    /**
     * Check location
     */
    checkLocation(): void {
        this.isCheckingLocationInProgress = true;
        this.ref.markForCheck();

        const locationOptions: GeolocationOptions = {
            timeout: this.checkingLocationTimeout,
            enableHighAccuracy: false
        };

        // set app location
        this.geoLocation.getCurrentPosition(locationOptions).then((location: Geoposition) => {
            const coordinates: Coordinates = location.coords;

            if (coordinates) {
                this.application.setLocation(coordinates.latitude, coordinates.longitude);
            }

            this.isCheckingLocationInProgress = false;
            this.ref.markForCheck();
        }).catch(() => {
            const toast = this.toast.create({
                message: this.translate.instant('location_error_desc'),
                closeButtonText: this.translate.instant('ok'),
                showCloseButton: true,
                duration: this.siteConfigs.getConfig('toastDuration')
            });

            toast.present();

            this.isCheckingLocationInProgress = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Search users
     */
    searchUsers(): void {
        // stop previous requests 
        if (this.searchUsersSubscription) {
            this.searchUsersSubscription.unsubscribe();
        }

        // clear the timeout
        if (this.checkNewUsersHandler) {
            clearTimeout(this.checkNewUsersHandler);
        }

        this.isSearchStarted = true;
        this.isNoUsersDescriptionVisible = false;
        this.ref.detectChanges();

        // search users
        this.searchUsersSubscription = this.user.tinderSearchUsers(this.userIdList).subscribe(userList => {
            this.userIdList = userList.map(user => user.id);
            this.userList = userList;
            this.ref.detectChanges();

            // try to load users some later
            if (!this.userList.length && this.isSearchAllowed && this.isTinderPageActive) {
                this.isNoUsersDescriptionVisible = true;
                this.ref.detectChanges();

                this.zone.runOutsideAngular(() => {
                    this.checkNewUsersHandler = window.setTimeout(() => {
                        if (this.isSearchAllowed) {
                            this.searchUsers();
                        }
                    }, this.siteConfigs.getConfig('tinderSearchTimeout'));
                });
            }
        });
    }

    /**
     * Is card throw allowed
     */
    private isCardThrowAllowed(): boolean {
        return Boolean(this.persistentStorage.
                getValue('user_dislike_pressed', false)) && Boolean(this.persistentStorage.getValue('user_like_pressed', false));
    }
}
