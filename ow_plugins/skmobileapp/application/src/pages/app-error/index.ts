import { Component, ChangeDetectionStrategy } from '@angular/core';
import { NavController } from 'ionic-angular';

// services
import { AuthService } from 'services/auth';
import { BootstrapService } from 'services/bootstrap';
import { SiteConfigsService } from 'services/site-configs';

// pages
import { LoginPage } from 'pages/user/login';
import { DashboardPage } from 'pages/dashboard';
import { AppMaintenancePage } from 'pages/app-maintenance';

@Component({
    selector: 'app-error',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class AppErrorPage {
    /**
     * Constructor
     */
    constructor(
        public bootstrap: BootstrapService,
        private siteConfigs: SiteConfigsService,
        private auth: AuthService,
        private nav: NavController) {}

    /**
     * Redirect
     */
    redirect(): void {
        this.nav.setRoot(!this.auth.isAuthenticated() ? LoginPage : DashboardPage);
    }

    /**
     * Do refresh
     */
    doRefresh(refresher): void {
        this.bootstrap.loadDependencies(false).subscribe(() => { 
            refresher.complete();

            // redirect to the maintenance page
            if (this.siteConfigs.getConfig('maintenanceMode') === true) {
                this.nav.setRoot(AppMaintenancePage);

                return;
            }

            this.nav.setRoot(!this.auth.isAuthenticated() ? LoginPage : DashboardPage);
        }, () => refresher.complete());
    }

    /**
     * Logout user
     */
    logout(): void {
        this.auth.logout();
        this.nav.setRoot(LoginPage);
    } 
}
