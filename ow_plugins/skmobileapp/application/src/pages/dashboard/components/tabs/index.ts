
import { Component, ChangeDetectionStrategy, Input, Output, ChangeDetectorRef, EventEmitter, OnInit, OnDestroy } from '@angular/core';
import { ISubscription } from 'rxjs/Subscription';
import { Observable } from 'rxjs/Observable';

// services
import { DashboardService } from 'services/dashboard';
import { SiteConfigsService } from 'services/site-configs';
import { MatchedUsersService } from 'services/matched-users';
import { MessagesService } from 'services/messages';

@Component({
    selector: 'dashboard-tabs',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class DashboardTabsComponent implements OnInit, OnDestroy {
    @Input() activeComponent: string;
    @Input() activeSubComponent: string;
    @Output() componentChanged = new EventEmitter();

    newMatchedUsersCount$: Observable<number>;
    newConversationsCount$: Observable<number>;

    private siteConfigsSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public siteConfigs: SiteConfigsService,
        public dashboard: DashboardService,
        private messages: MessagesService,
        private matchedUsers: MatchedUsersService,
        private ref: ChangeDetectorRef) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        // watch configs changes
        this.siteConfigsSubscription = this.siteConfigs.watchConfigGroup([
            'searchMode',
            'activePlugins'
        ]).subscribe(() => {
            // check sub components state
            switch (this.activeSubComponent) {
                case this.dashboard.hotListPage :
                    // the hot list plugin is deactivated
                    if (!this.siteConfigs.isPluginActive('hotlist')) {
                        // redirect to an appropriate page
                        this.siteConfigs.isTinderSearchAllowed()
                            ? this.changeComponent(this.activeComponent, this.dashboard.tinderPage)
                            : this.changeComponent(this.activeComponent, this.dashboard.browsePage)
                    }

                    break;

                // tinder search is activated 
                case this.dashboard.browsePage :
                    if (this.siteConfigs.isTinderSearchMode()) {
                        this.changeComponent(this.activeComponent, this.dashboard.tinderPage);
                    }

                    break;

                // browse search is activated 
                case this.dashboard.tinderPage :
                    if (this.siteConfigs.isBrowseSearchMode()) {
                        this.changeComponent(this.activeComponent, this.dashboard.browsePage);
                    }

                    break;

                default :
            }

            this.ref.markForCheck();
        });

        // init watchers
        this.newMatchedUsersCount$ = this.matchedUsers.watchNewMatchedUsersCount();
        this.newConversationsCount$ = this.messages.watchNewConversationsCount();
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.siteConfigsSubscription.unsubscribe();
    }

    /**
     * Change component
     */
    changeComponent(componentName: string, subComponentName?: string): void {
        this.componentChanged.emit({
            componentName: componentName,
            subComponentName: subComponentName
        });
    }
}
