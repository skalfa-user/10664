import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { ModalController, Nav, ActionSheetController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { ISubscription } from 'rxjs/Subscription';

// services
import { AuthService } from 'services/auth';
import { UserService, IUserWithAvatar } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { LoginPage } from 'pages/user/login';
import { GdprUserDataPage } from 'pages/app-settings/gdpr/gdpr-user-data';
import { GdprThirdPartyPage } from 'pages/app-settings/gdpr/gdpr-third-party';
import { EmailNotificationsPage } from 'pages/app-settings/email-notifications';
import { PreferencesPage } from 'pages/app-settings/preferences';

// shared components
import { CustomPageComponent } from 'shared/components/custom-page';

@Component({
    selector: 'app-settings',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class AppSettingsPage implements OnInit, OnDestroy {
    me: IUserWithAvatar;
    isPageLoading: boolean = false;
    gdprUserDataPage = GdprUserDataPage;
    gdprThirdPartyPage = GdprThirdPartyPage;

    private siteConfigsSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public siteConfigs: SiteConfigsService,
        private ref: ChangeDetectorRef,
        private actionSheet: ActionSheetController,
        private user: UserService,
        private auth: AuthService,
        private nav: Nav,
        private modal: ModalController, 
        private translate: TranslateService) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        this.me = this.user.getMe();

        // watch configs changes
        this.siteConfigsSubscription = this.siteConfigs.watchConfigGroup([
            'activePlugins',
            'gdprThirdPartyServices'
        ]).subscribe(() => this.ref.markForCheck());
    }


    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.siteConfigsSubscription.unsubscribe();
    }

    /**
     * Show privacy policy modal
     */
    showPrivacyPolicyModal(): void {
        const modal = this.modal.create(CustomPageComponent, {
            title: this.translate.instant('privacy_policy_page_header'),
            pageName: 'privacy_policy_page_content'
        });

        modal.present();
    }

    /**
     * Show terms of use modal
     */
    showTermsOfUseModal(): void {
        const modal = this.modal.create(CustomPageComponent, {
            title: this.translate.instant('tos_page_header'),
            pageName: 'tos_page_content'
        });

        modal.present();
    }

    /**
     * Logout user
     */
    logout(): void {
        this.auth.logout();
        this.nav.setRoot(LoginPage);
    }

    /**
     * Delete account confirmation
     */
    deleteAccountConfirmation(): void {
        const actionSheet = this.actionSheet.create({
            title: this.translate.instant('app_settings_delete_account_confirmation'),
            buttons: [{
                text: this.translate.instant('app_settings_delete_account_button'),
                handler: () => this.deleteAccount()                
            }, {
                text: this.translate.instant('cancel')
            }]
        });

        actionSheet.present();
    }

    /**
     * Show notifications settings
     */
    showNotificationsSettings(): void {
        this.nav.push(EmailNotificationsPage);
    }

    /**
     * Show preferences
     */
    showPreferences(preferenceSection: string, title: string): void {
        this.nav.push(PreferencesPage, {
            section: preferenceSection, 
            title: title
        });
    }

    /**
     * Delete user account
     */
    private deleteAccount(): void {
        this.isPageLoading = true;
        this.ref.markForCheck();

        this.user.deleteMe().subscribe(() => {
            this.logout();
        });
    }
}
