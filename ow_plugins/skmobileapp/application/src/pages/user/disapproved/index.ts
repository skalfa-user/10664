import { Component, ChangeDetectionStrategy } from '@angular/core';
import { NavParams, NavController } from 'ionic-angular';

// services
import { AuthService } from 'services/auth';
import { UserService } from 'services/user';

// import pages
import { DashboardPage } from 'pages/dashboard';
import { LoginPage } from 'pages/user/login';

@Component({
    selector: 'user-disapproved',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class UserDisapprovedPage {
    description: string;

    private status: string;

    /**
     * Constructor
     */
    constructor(
        private user: UserService,
        private navParams: NavParams,
        private auth: AuthService,
        private nav: NavController)
    {
        this.status = this.navParams.get('status');
        this.description = this.navParams.get('description');
    }

    /**
     * Is suspended
     */
    get isSuspended(): boolean {
        return this.status == 'suspended';
    }

    /**
     * Logout user
     */
    logout(): void {
        this.auth.logout();
        this.nav.setRoot(LoginPage); 
    }

    /**
     * Do refresh
     */
    doRefresh(refresher): void {
        this.user.loadMe().subscribe(() => { 
            refresher.complete();

            this.nav.setRoot(!this.auth.isAuthenticated() ? LoginPage : DashboardPage);
        }, () => refresher.complete());
    }
}
