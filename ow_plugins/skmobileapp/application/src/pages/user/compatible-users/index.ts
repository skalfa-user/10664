import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ViewChild, ChangeDetectorRef } from '@angular/core';
import { NavController, List } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';
import { ISubscription } from 'rxjs/Subscription';

// services
import { CompatibleUsersService, ICompatibleUserListItem } from 'services/compatible-users';
import { SiteConfigsService } from 'services/site-configs';
import { MessagesService } from 'services/messages';
import { ApplicationService } from 'services/application';

// components
import { MatchActionsComponent } from 'shared/components/match-actions';

// pages
import { ProfileViewPage } from 'pages/profile';
import { MessagesPage } from 'pages/messages';

@Component({
    selector: 'compatible-users',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class CompatibleUsersPage implements OnInit, OnDestroy {
    @ViewChild(List) sliderUserList: List;
    @ViewChild(MatchActionsComponent) matchActions: MatchActionsComponent;

    isUsersFetched$: Observable<boolean>;
    userList$: Observable<Array<ICompatibleUserListItem>>;

    private isTinderSearchModeSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public application: ApplicationService,
        public messages: MessagesService,
        public ref: ChangeDetectorRef,
        private siteConfigs: SiteConfigsService,
        private compatibleUsers: CompatibleUsersService, 
        private nav: NavController) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        // init watchers
        this.isUsersFetched$ = this.compatibleUsers.watchIsCompatibleUsersFetched();
        this.userList$ = this.compatibleUsers.watchCompatibleUserList();

        // watch tinder mode status
        this.isTinderSearchModeSubscription = this.siteConfigs
            .watchIsTinderSearchMode()
            .subscribe(isActive => this.ref.markForCheck());
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.isTinderSearchModeSubscription.unsubscribe();
    }

    /**
     * View profile
     */
    viewProfile(userData: ICompatibleUserListItem): void {
        this.nav.push(ProfileViewPage, {
            userId: userData.user.id
        });
    }

    /**
     * Show chat
     */
    showChat(userData: ICompatibleUserListItem): void {  
        this.nav.push(MessagesPage, {
            userId: userData.user.id
        });
    }
 
    /**
     * Track user list
     */
    trackUserList(index: number, userData: ICompatibleUserListItem): number {
        return userData.compatibleUser.id;
    }
 
    /**
     * Like user
     */
    likeUser(userData: ICompatibleUserListItem): void {
        this.sliderUserList.closeSlidingItems();

        // like user
        this.matchActions.likeUser(userData.user.id, userData.user.userName);
    }
}
