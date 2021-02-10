import { Component, ChangeDetectionStrategy } from '@angular/core';
import { ViewController } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';

// service
import { SiteConfigsService } from 'services/site-configs';

@Component({
    selector: 'auth-providers',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class AuthProvidersComponent {
    authProviders$: Observable<Array<string>>;

    /**
     * Constructor
     */
    constructor(
        private view: ViewController, 
        private siteConfigs: SiteConfigsService) 
    {
        // init watchers
        this.authProviders$ = this.siteConfigs.watchConfig('authProviders');
    }

    /**
     * Dismiss
     */
    dismiss(): void {
        this.view.dismiss(null);
    }

    /**
     * Choose provider
     */
    chooseProvider(provider: string): void {
        this.view.dismiss(provider);
    }
}
