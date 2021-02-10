import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef, ViewChild } from '@angular/core';
import {
    Slides,
    ModalController,
    NavController,
    Modal,
    NavParams,
    ToastController
} from 'ionic-angular';
import {TranslateService} from 'ng2-translate';
import { ISubscription } from 'rxjs/Subscription';

// services
import { ApplicationService } from 'services/application';
import { UserService } from 'services/user'
import { AuthService } from 'services/auth';
import { DashboardService } from 'services/dashboard';
import { SiteConfigsService } from 'services/site-configs';
import { MatchedUsersService, IMatchedUserListItem } from 'services/matched-users';
import { VideoImService } from 'services/video-im';
import { PushNotificationsService } from 'services/push';
import { PersistentStorageService } from 'services/persistent-storage';

// pages
import { MessagesPage } from 'pages/messages';

// components
import { MatchedUserPageComponent } from './components/matched-user';
import { VideoImConfirmationComponent } from './components/video-im/confirmation';
import { VideoImChatComponent } from './components/video-im/chat';

@Component({
    selector: 'dashboard',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        DashboardService
    ]
})

export class DashboardPage implements OnInit, OnDestroy {
    @ViewChild('componentsSlider') set content(componentsSlider: Slides) {
        this.componentsSlider = componentsSlider;
    }

    isPageLoading: boolean = false;

    private isDashboardActive: boolean = true;
    private applicationLocationSubscription: ISubscription;
    private matchedUserSubscription: ISubscription;
    private incomingVideoImCallSubscription: ISubscription;
    private activeCallerIdSubscription: ISubscription;
    private pushNotificationsSubscription: ISubscription;
    private componentsSlider: Slides;
    private matchedUsersModal: Modal;
    private videoImConfirmationModal: Modal;
    private videoImChatModal: Modal;
    private purchaseNotified = false;

    // push notification constants
    private static PUSH_NOTIFICATION_MESSAGE: string = 'message';
    private static PUSH_NOTIFICATION_MATCHED_USER: string = 'matchedUser';

    /**
     * Constructor
     */
    constructor(
        public siteConfigs: SiteConfigsService,
        public dashboard: DashboardService,
        public application: ApplicationService,
        private auth: AuthService,
        private ref: ChangeDetectorRef,
        private user: UserService,
        private modal: ModalController,
        private pushNotifications: PushNotificationsService,
        private persistentStorage: PersistentStorageService,
        private matchedUsers: MatchedUsersService,
        private videoIm: VideoImService,
        private nav: NavController,
        private navParams: NavParams,
        private toast: ToastController,
        private translate: TranslateService
    ) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        if (this.navParams.get('purchase')) {
            let locationSearch = '?purchase=1';

            if (this.navParams.get('credits')) {
                locationSearch += '&credits=' + this.navParams.get('credits')
            }

            location.search = locationSearch;

            return;
        }

        if (this.navParams.get('fail')) {
            let locationSearch = '?fail=1';

            location.search = locationSearch;

            return;
        }

        const locationArr = location.search.split('?').pop().split('&').reduce((carry, search) => {
            const parts = search.split('=');
            carry[parts[0]] = parts[1];

            return carry;
        }, {});

        if (!this.purchaseNotified && locationArr.hasOwnProperty('purchase')) {
            if (locationArr.hasOwnProperty('credits')) {
                this.showNotification('credits_updated', {
                    count: locationArr['credits']
                });
            } else {
                this.showNotification('membership_updated');
            }

            this.purchaseNotified = true;
        } else if (!this.purchaseNotified && locationArr.hasOwnProperty('fail')) {
            this.showNotification('purchase_cancelled');
            this.purchaseNotified = true;
        }

        // disallow sliders moving
        this.componentsSlider.followFinger = false;

        // watch the current user's location
        this.applicationLocationSubscription = this.application.watchLocation().subscribe(location => {
            if (location.latitude && location.longitude) {
                this.user.updateLocation(location.latitude, location.longitude).subscribe();
            }
        });

        this.watchIncomingVideoImCall();
        this.watchActiveInterlocutorData();

        // load user data
        if (!this.user.isUserLoaded(this.user.getUser(this.auth.getUserId()))) {
            this.isPageLoading = true;
            this.ref.markForCheck();

            this.user.loadMe().subscribe(() => {
                this.isPageLoading = false;
                this.ref.markForCheck();

                // watch push notifications
                this.pushNotificationsSubscription = this.pushNotifications.watchNotifications().subscribe(data => {
                    this.processPushNotification(data);
                });
            });

            return;
        }

        // watch push notifications
        this.pushNotificationsSubscription = this.pushNotifications.watchNotifications().subscribe(data => {
            this.processPushNotification(data);
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.applicationLocationSubscription.unsubscribe();

        this.stopWatchIncomingVideoImCall();
        this.stopWatchActiveInterlocutorData();

        if (this.pushNotificationsSubscription) {
            this.pushNotificationsSubscription.unsubscribe();
        }
    }

    /**
     * Change component
     */
    changeComponent(component: {componentName: string, subComponentName?: string}): void {
        const componentIndex = this.dashboard.getComponentIndexByName(component.componentName);

        if (componentIndex !== -1) {
            this.dashboard.setActiveComponent(component.componentName, component.subComponentName);
            this.componentsSlider.slideTo(componentIndex);

            this.ref.markForCheck();
        }
    }

    /**
     * Components slider did change
     */
    componentsSliderDidChange(): void {
        if (this.componentsSlider.getActiveIndex() <= this.dashboard.components.length - 1) {
            this.dashboard.setActiveComponentByIndex(this.componentsSlider.getActiveIndex());
            this.ref.markForCheck();
        }
    }

    /**
     * Page is going to be active
     */
    ionViewWillEnter(): void {
        this.isDashboardActive = true;
        this.watchNotNotifiedMatchedUser();
    }

    /**
     * Page is going to be inactive
     */
    ionViewWillLeave(): void {
        this.isDashboardActive = false;
        this.stopWatchingNotNotifiedMatchedUser();
    }


    /**
     * Stop watching not notified matched user
     */
    private stopWatchingNotNotifiedMatchedUser(): void {
        if (this.matchedUserSubscription) {
            this.matchedUserSubscription.unsubscribe();
        }

        // close matched users modal window
        if (this.matchedUsersModal) {
            this.matchedUsersModal.dismiss();
        }
    }

    /**
     * Watch for not notified matched user
     */
    private watchNotNotifiedMatchedUser(): void {
        this.matchedUserSubscription = this.matchedUsers.watchNotNotifiedMatchedUser().subscribe(matchedUser => {
            if (matchedUser && !this.matchedUsersModal) {
                this.showMatchedUserModalWindow(matchedUser);
            }
        });
    }

    /**
     * Show matched user modal window
     */
    private showMatchedUserModalWindow(matchedUser: IMatchedUserListItem): void {
        // temporally unsubscribe from matched users source
        this.stopWatchingNotNotifiedMatchedUser();

        this.matchedUsersModal = this.modal.create(MatchedUserPageComponent, {
            matchedUser: matchedUser
        });

        this.matchedUsersModal.onDidDismiss((result: {isShowChat: boolean}) => {
            this.matchedUsersModal = null;

            if (result && result.isShowChat) {
                this.nav.push(MessagesPage, {
                    userId: matchedUser.user.id
                });

                return;
            }

            // subscribe the source again
            if (this.isDashboardActive) {
                this.watchNotNotifiedMatchedUser();
            }
        });

        this.matchedUsersModal.present();
    }



    /**
     * Stop watching first incoming video im notification id
     */
    private stopWatchIncomingVideoImCall(): void {
        if (this.incomingVideoImCallSubscription) {
            this.incomingVideoImCallSubscription.unsubscribe();
        }

        // close video im confirmation modal window
        if (this.videoImConfirmationModal) {
            this.videoImConfirmationModal.dismiss();
        }
    }

    /**
     * Watch first incoming video im notification id
     */
    private watchIncomingVideoImCall(): void {
        this.incomingVideoImCallSubscription = this.videoIm.watchFirstCallingUserId().subscribe(callData => {
            if (callData && !this.videoImConfirmationModal) {
                this.showVideoImConfirmationModalWindow(callData.userId, callData.sessionId);
            }
        });
    }

    /**
     * Show video im confirmation modal window
     */
    private showVideoImConfirmationModalWindow(callerId: number, sessionId: string): void {
        this.videoImConfirmationModal = this.modal.create(VideoImConfirmationComponent, {
            userId: callerId,
            sessionId: sessionId
        });

        this.videoImConfirmationModal.onDidDismiss(() => {
            this.videoImConfirmationModal = null;
        });

        this.videoImConfirmationModal.present();
    }



    /**
     * Stop watching first incoming video im notification id
     */
    private stopWatchActiveInterlocutorData(): void {
        if (this.activeCallerIdSubscription) {
            this.activeCallerIdSubscription.unsubscribe();
        }
    }

    /**
     * Watch first incoming video im notification id
     */
    private watchActiveInterlocutorData(): void {
        this.activeCallerIdSubscription = this.videoIm.watchActiveInterlocutorData().subscribe(activeInterlocutorData => {
            if (activeInterlocutorData.userId) {
                if (this.videoImChatModal) {
                    this.videoImChatModal.dismiss();
                    this.videoImChatModal = null;
                }

                this.videoImChatModal = this.modal.create(VideoImChatComponent, {
                    userId: activeInterlocutorData.userId,
                    isMeInitiator: activeInterlocutorData.isMeInitiator
                });

                this.videoImChatModal.present();
            }
        });
    }

    /**
     * Process notification
     */
    private processPushNotification(notification: any): void {
        if (notification.additionalData &&
                    notification.additionalData.uuid != this.persistentStorage.getValue('latest_push_uuid')) {

            // don't process foreground push notices
            if (!notification.additionalData.foreground) {
                this.persistentStorage.setValue('latest_push_uuid', notification.additionalData.uuid);

                switch (notification.additionalData.type) {
                    // redirect to the chat page
                    case DashboardPage.PUSH_NOTIFICATION_MESSAGE :
                        if (notification.additionalData.senderId && notification.additionalData.conversationId) {
                            this.nav.push(MessagesPage, {
                                userId: notification.additionalData.senderId
                            });
                        }

                        break;

                    // redirect to the messages page
                    case DashboardPage.PUSH_NOTIFICATION_MATCHED_USER :
                        this.changeComponent({
                            componentName: 'conversations'
                        });

                        // mark matched user
                        this.matchedUsers.markMatchedUserAsNotified(notification.additionalData.id).subscribe();

                        break;

                    default :
                }
            }
        }
    }

    protected showNotification(lang: string, params = {}): void {
        const notificationToaster = this.toast.create({
            message: this.translate.instant(lang, params),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        notificationToaster.present();
    }
}
