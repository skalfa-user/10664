import { Component, ChangeDetectionStrategy } from '@angular/core';
import { NavController } from 'ionic-angular';

// services
import { SiteConfigsService }  from 'services/site-configs';
import { AuthService } from 'services/auth';

// pages
import { LoginPage } from 'pages/user/login';
import { DashboardPage } from 'pages/dashboard';

@Component({
    selector: 'app-maintenance',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class AppMaintenancePage {
    /**
     * Constructor
     */
    constructor(
        private auth: AuthService,
        private siteConfigs: SiteConfigsService,
        private nav: NavController) {}

    /**
     * Do refresh
     */
    doRefresh(refresher): void {
        this.siteConfigs.loadConfigs().subscribe(() => {
            refresher.complete();

            // redirect to the maintenance page
            if (this.siteConfigs.getConfig('maintenanceMode') !== true) {
                this.nav.setRoot(!this.auth.isAuthenticated() ? LoginPage : DashboardPage);
            }

        }, () => refresher.complete());
    }
}
