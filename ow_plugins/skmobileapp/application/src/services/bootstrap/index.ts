import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { NgRedux } from '@angular-redux/store';
import { ReplaySubject } from 'rxjs/ReplaySubject';

// services
import { I18nService } from 'services/i18n';
import { PermissionsService } from 'services/permissions';
import { ApplicationService } from 'services/application';
import { SiteConfigsService } from 'services/site-configs';
import { GuestsService } from 'services/guests';
import { CompatibleUsersService } from 'services/compatible-users';
import { ServerEventsService } from 'services/server-events';
import { AuthService } from 'services/auth';
import { HotListService } from 'services/hot-list';
import { VideoImService } from 'services/video-im';
import { MatchedUsersService } from 'services/matched-users';
import { MessagesService } from 'services/messages';
import { AdMobService } from 'services/admob';

// store
import { IAppState } from 'store';
import { USERS_LOGOUT } from 'store/actions';

@Injectable()
export class BootstrapService {
    public applicationDependenciesLoaded$: ReplaySubject<boolean> = new ReplaySubject(1);

    /**
     * Constructor
     */
    constructor(
        private admob: AdMobService,
        private ngRedux: NgRedux<IAppState>,
        private auth: AuthService,
        private guests: GuestsService,
        private compatibleUsers: CompatibleUsersService,
        private permissions: PermissionsService,
        private application: ApplicationService,
        private serverEvents: ServerEventsService,
        private i18n: I18nService,
        private siteConfigs: SiteConfigsService,
        private hotList: HotListService,
        private videoIm: VideoImService,
        private matchedUsers: MatchedUsersService,
        private messages: MessagesService) 
    {
        // init watchers
        this.watchSiteConfigUpdates();
        this.watchPermissionsUpdates();
        this.watchUserGuestsUpdates();
        this.watchMatchedUsersUpdates();
        this.watchHotListUpdates();
        this.watchVideoImNotifications();
        this.watchCompatibleUsersUpdates();
        this.watchConversationsUpdates();
        this.watchMessagesUpdates();
        this.watchMessagesQueue();
        this.watchUserLogout();
    }

    /**
     * Load dependencies
     */
    loadDependencies(clearOldData: boolean = true): Observable<any> {
        this.applicationDependenciesLoaded$.next(false);

        // stop server events
        this.serverEvents.stop();
 
        // clear old data
        if (clearOldData) {
            this.i18n.resetLang();

            this.application.resetApplication();
        }

        // try to load dependencies
        let loadDependencies: Observable<any> = Observable.forkJoin( 
            this.siteConfigs.loadConfigs(),
            this.i18n.loadTranslations()
        );

        loadDependencies.subscribe(() => {
            this.applicationDependenciesLoaded$.next(true);

            // start server events
            this.serverEvents.start();

            // init admob (it works only inside native apps)
            if (!this.application.isAppRunningInExternalBrowser()) {
                this.initAdmob();
            }
        }, () => {});

        return loadDependencies;
    }

    /**
     * Init admob
     */
    private initAdmob(): void {
        const sources: Array<Observable<any>> = [];

        // remove a previously created banner
        if (this.admob.isBannerCreated()) {
            sources.push(this.admob.hideBanner());
            sources.push(this.admob.removeBanner());
        }

        // create a new one
        sources.push(this.admob.createBanner(this.siteConfigs.getConfig('admobId')));

        Observable.concat(sources).subscribe();
    }

    /**
     * Watch site configs updates
     */
    private watchSiteConfigUpdates(): void {
        this.serverEvents.watchData('configs').subscribe(response => {
            this.siteConfigs.setConfigs(response.data);
        });
    }

    /**
     * Watch permissions updates
     */
    private watchPermissionsUpdates(): void {
        this.serverEvents.watchData('permissions').subscribe(response => {
            this.permissions.updatePermissions(response.data);
        });
    }

    /**
     * Watch user guests updates
     */
    private watchUserGuestsUpdates(): void {
        this.serverEvents.watchData('guests').subscribe(response => {
            this.guests.setGuests(response.data);
        });
    }

    /**
     * Watch matched users updates
     */
    private watchMatchedUsersUpdates(): void {
        this.serverEvents.watchData('matchedUsers').subscribe(response => {
            this.matchedUsers.setMatchedUsers(response.data);
        });
    }

    /**
     * Watch hot list updates
     */
    private watchHotListUpdates(): void {
        this.serverEvents.watchData('hotList').subscribe(response => {
            this.hotList.setHotList(response.data);
        });
    }

    /**
     * Watch compatible users updates
     */
    private watchCompatibleUsersUpdates(): void {
        this.serverEvents.watchData('compatibleUsers').subscribe(response => {
            this.compatibleUsers.setCompatibleUsers(response.data);
        });
    }

    /**
     * Watch conversations updates
     */
    private watchConversationsUpdates(): void {
        this.serverEvents.watchData('conversations').subscribe(response => {
            this.messages.setConversations(response.data);
        });
    }

    /**
     * Watch messages updates
     */
    private watchMessagesUpdates(): void {
        this.serverEvents.watchData('messages').subscribe(response => {
            this.messages.updateMessages(response.data);
        });
    }

    /**
     * Watch messages queue
     */
    private watchMessagesQueue(): void {
        this.messages.watchMessagesQueue().subscribe(message => {
            if (message) {
                message.file ? this.messages.sendImageMessage(message) : this.messages.sendTextMessage(message);
            }
        });
    }

    /**
     * Watch user logout
     */
    private watchUserLogout(): void {
        this.auth.watchLogout$.subscribe(() => this.ngRedux.dispatch({
            type: USERS_LOGOUT,
            payload: {}
        }));
    }

    /**
     * Watch video im notifications
     */
    private watchVideoImNotifications(): void {
        this.serverEvents.watchData('videoIm').subscribe(response => {
            this.videoIm.addNotifications(response.data);
        });
    }
}
