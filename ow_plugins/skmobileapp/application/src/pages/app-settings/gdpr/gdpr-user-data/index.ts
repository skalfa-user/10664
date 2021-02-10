import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { SiteConfigsService } from 'services/site-configs';
import { UserService, IUserWithAvatar } from 'services/user';
import { GdprService } from 'services/gdpr';

// pages
import { EditUserQuestionsPage } from 'pages/user/edit/questions';

@Component({
    selector: 'gdpr-user-data',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        GdprService
    ]
})

export class GdprUserDataPage implements OnInit {
    profileEditPage = EditUserQuestionsPage;
    my$: Observable<IUserWithAvatar>;

    /**
     * Constructor
     */
    constructor(
        public siteConfigs: SiteConfigsService,
        private user: UserService,
        private gdpr: GdprService,
        private toast: ToastController,
        private translate: TranslateService) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        // init watchers
        this.my$ = this.user.watchMe();
    }

    /**
     * Request user data to download
     */
    requestUserDataToDownload() {
        const toast = this.toast.create({
            message: this.translate.instant('gdpr_request_download_feedback'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();

        this.gdpr.requestUserDataToDownload().subscribe();
    }

    /**
     * Request user data to delete
     */
    requestUserDataToDelete() {
        const toast = this.toast.create({
            message: this.translate.instant('gdpr_request_deletion_feedback'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();

        this.gdpr.requestUserDataToDelete().subscribe();
    }
}
