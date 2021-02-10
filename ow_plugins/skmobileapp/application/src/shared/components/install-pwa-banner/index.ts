import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit } from '@angular/core';
import { App as IonicApp, AlertController, ModalController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// service
import { ApplicationService } from 'services/application';
import { BootstrapService } from 'services/bootstrap';
import { PersistentStorageService } from 'services/persistent-storage';
import { DateUtilsService } from 'services/date-utils';
import { SiteConfigsService } from 'services/site-configs';

// import shared components
import { DownloadPwaComponent } from 'shared/components/download-pwa';

@Component({
    selector: 'install-pwa-banner',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        DateUtilsService
    ]
})

export class InstallPwaBannerComponent implements OnInit {
    private isAppLoaded: boolean = false;
    private defaultShortPeriod: number = 300; // 5 minutes
    private defaultLongPeriod: number = 3600 * 24 * 9999;

    /**
     * Constructor
     */
    constructor(
        private ionicApp: IonicApp,
        private siteConfigs: SiteConfigsService,
        private modal: ModalController,
        private translate: TranslateService,
        private alert: AlertController,
        private dateUtils: DateUtilsService,
        private persistanceStorage: PersistentStorageService,
        private ref: ChangeDetectorRef,
        private application: ApplicationService,
        private bootstrap: BootstrapService) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        this.ionicApp.getRootNav().viewDidEnter.subscribe(() => {
            this.ref.markForCheck();
        });

        // watch the app bootstrap loading process
        this.bootstrap.applicationDependenciesLoaded$.subscribe(isAppLoaded => {
            this.isAppLoaded = isAppLoaded;
            this.ref.markForCheck();
        });

        // watch configs changes
        this.siteConfigs.watchConfigGroup([
            'installPwaBannerShortPeriod',
            'installPwaBannerLongPeriod'
        ]).subscribe(configs => {
            const [shortPeriod, longPeriod] = configs;

            if (shortPeriod && longPeriod) {
                // update install pwa banner time periods
                this.persistanceStorage.setValue('install_pwa_banner_short_period', shortPeriod);
                this.persistanceStorage.setValue('install_pwa_banner_long_period', longPeriod);

                this.ref.markForCheck();
            }
        });
    }

    /**
     * Install
     */
    install(): void {
        const modal = this.modal.create(DownloadPwaComponent);
        modal.present();

        this.setInstallPwaBannerExpireTime(this.getInstallPwaLongTimePeriod());
        this.ref.markForCheck();
    }

    /**
     * Close
     */
    close(): void {
        const confirm = this.alert.create({
            title: this.translate.instant('pwa_install_close_confirm_title'),
            message: this.translate.instant('pwa_install_close_confirm_message', {
                appName: this.appName
            }),
            buttons: [{
                text: this.translate.instant('pwa_install_close_confirm_dismiss'),
                handler: () => {
                    this.setInstallPwaBannerExpireTime(this.getInstallPwaLongTimePeriod());
                    this.ref.markForCheck();
                }
            }, {
                text: this.translate.instant('pwa_install_close_confirm_later'),
                handler: () => {
                    this.setInstallPwaBannerExpireTime(this.getInstallPwaShortTimePeriod());
                    this.ref.markForCheck();
                }
            }]
        });

        confirm.present();
    }

   /**
     * Is download available
     */
    get isDownloadAvailable(): boolean {
        return this.application.isAppReadyForDownload() 
            && this.isInstallPwaBannerExpired() 
            && this.isAppLoaded === true
            && this.application.isAppRunningInMobileSafari();
    }

    /**
     * App name
     */
    get appName(): string {
        return this.application.getConfig('name');
    }

    /**
     * App description
     */
    get appDescription(): string {
        return this.application.getConfig('description');
    }

    /**
     * Get install pwa short time period
     */
    private getInstallPwaShortTimePeriod(): number {
        return parseInt(this.persistanceStorage.getValue('install_pwa_banner_short_period', this.defaultShortPeriod));
    }

    /**
     * Get install pwa long time period
     */
    private getInstallPwaLongTimePeriod(): number {
        return parseInt(this.persistanceStorage.getValue('install_pwa_banner_long_period', this.defaultLongPeriod));
    }

    /**
     * Is install pwa banner expired
     */
    private isInstallPwaBannerExpired(): boolean {
        if (this.persistanceStorage.getValue('install_pwa_banner_expire_time', 0) <= this.dateUtils.getUnixTime()) {
            return true;
        }

        return false;
    }

    /**
     * Set install pwa banner expire time
     */
    private setInstallPwaBannerExpireTime(seconds: number): void {
        this.persistanceStorage.setValue('install_pwa_banner_expire_time', this.dateUtils.getUnixTime() + seconds);
    }
}
