import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, OnDestroy, Input } from '@angular/core';
import { ModalController, NavController } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';
import { Observable } from 'rxjs/Observable';

// services
import { UserService, IUserWithAvatar } from 'services/user';
import { GuestsService } from 'services/guests';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';
import { ApplicationService } from 'services/application';
import { PaymentsService } from 'services/payments';

// pages
import { EditUserQuestionsPage } from 'pages/user/edit/questions';
import { EditUserPhotosPage } from 'pages/user/edit/photos';
import { AppSettingsPage } from 'pages/app-settings/settings';
import { GuestsPage } from 'pages/user/guests';
import { BookmarksPage } from 'pages/user/bookmarks';
import { CompatibleUsersPage } from 'pages/user/compatible-users';
import { InitialPaymentsPage } from 'pages/payments/initial';
import { ProfileViewPage } from 'pages/profile';

// import shared components
import { DownloadPwaComponent } from 'shared/components/download-pwa';

@Component({
    selector: 'profile',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class ProfileComponent implements OnInit, OnDestroy {
    @Input() isDashboardLoading: boolean;

    my$: Observable<IUserWithAvatar>;
    newGuestsCount$: Observable<number>;

    profileEditPhotosPage = EditUserPhotosPage;
    profileEditPage = EditUserQuestionsPage;    
    appSettingsPage = AppSettingsPage;
    guestsPage = GuestsPage;
    bookmarksPage = BookmarksPage;
    compatibleUsersPage = CompatibleUsersPage;
    initialPaymentsPage = InitialPaymentsPage; 

    private siteConfigSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public payments: PaymentsService,
        public siteConfigs: SiteConfigsService,
        private guests: GuestsService,
        private ref: ChangeDetectorRef,
        private auth: AuthService,
        private user: UserService, 
        private modal: ModalController, 
        private application: ApplicationService,
        private nav: NavController) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        // watch configs changes
        this.siteConfigSubscription = this.siteConfigs
            .watchConfig('activePlugins')
            .subscribe(() => this.ref.markForCheck());

        // init watchers
        this.my$ = this.user.watchMe();
        this.newGuestsCount$ = this.guests.watchNewGuestCount();
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.siteConfigSubscription.unsubscribe();
    }

    /**
     * Is installation guide allowed
     */
    get isInstallationGuideAllowed(): boolean {
        return this.application.isAppReadyForDownload() && this.siteConfigs.getConfig('isDemoModeActivated');
    }

    /**
     * Show profile
     */
    showProfile(): void {
        this.nav.push(ProfileViewPage, {
            userId: this.auth.getUserId()
        });
    }

    /**
     * Show installation guide
     */
    showInstallationGuide(): void {
        const modal = this.modal.create(DownloadPwaComponent);

        modal.present();
    }
}
