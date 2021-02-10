
import { Component, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, OnDestroy, Input } from '@angular/core';
import { ISubscription } from 'rxjs/Subscription';
import { Keyboard } from '@ionic-native/keyboard';
import { NavController, ModalController, Modal } from 'ionic-angular';

// services
import { PersistentStorageService } from 'services/persistent-storage';
import { SiteConfigsService } from 'services/site-configs';
import { PermissionsService, IPermission } from 'services/permissions';
import { UserService, ISearchFilter, IUserResponse } from 'services/user';
import { PaymentsService } from 'services/payments';

// pages
import { ProfileViewPage } from 'pages/profile';

// components
import { UserSearchFilterComponent } from './components/search-filter';

// questions
import { QuestionManager } from 'services/questions/manager';

@Component({
    selector: 'search',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class SearchComponent implements OnInit, OnDestroy {
    @Input() isDashboardLoading: boolean;

    searchByUserNameFilter: string = '';
    userList: Array<IUserResponse> = [];
    isUserListLoading: boolean = false;

    private searchPermission: IPermission;
    private isSearchStarted: boolean = false;
    private searchFilters: Array<ISearchFilter> = [];
    private siteConfigsSubscription: ISubscription;
    private searchPermissionSubscription: ISubscription;
    private searchUsersSubscription: ISubscription;
    private filtersModal: Modal;

    /**
     * Constructor
     */
    constructor(
        public ref: ChangeDetectorRef,
        public payments: PaymentsService,
        private siteConfigs: SiteConfigsService,
        private nav: NavController,
        private users: UserService,
        private keyboard: Keyboard,
        private modal: ModalController,
        private permissions: PermissionsService,
        private persistentStorage: PersistentStorageService) 
    {
        // init search filters
        this.searchFilters = this.persistentStorage.getValue('search_filters', []);
        this.searchByUserNameFilter = this.persistentStorage.getValue('search_by_user_name_filter', '');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // start a searching
        if (this.isSearchAllowed) {
            this.searchUsers();
        }

        // watch the config updates
        this.siteConfigsSubscription = this.siteConfigs.watchConfigGroup([
            'isSearchByUserNameActive',
            'activePlugins'
        ]).subscribe(() => {
            if (!this.isSearchByUserNameAllowed && this.searchByUserNameFilter && this.isSearchAllowed) {
                this.searchByUserNameFilter = '';
                this.ref.markForCheck();

                // refresh the search list
                this.searchUsers();

                return;
            }

            this.ref.markForCheck();
        });

        // watch the permission updates
        this.searchPermissionSubscription = this.permissions
            .watchMe('base_search_users')
            .subscribe((permission: IPermission) => {
                this.searchPermission = permission;

                // make users search
                if (this.isSearchAllowed && !this.isSearchStarted) {
                    this.searchUsers();
                }

                this.ref.markForCheck();
            });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.siteConfigsSubscription.unsubscribe();
        this.searchPermissionSubscription.unsubscribe();

        // close the modal window
        if (this.filtersModal) {
            this.filtersModal.dismiss();
        }
    }

    /**
     * Is search by user name allowed
     */
    get isSearchByUserNameAllowed(): boolean {
        return this.siteConfigs.getConfig('isSearchByUserNameActive');
    }

    /**
     * Is search allowed
     */
    get isSearchAllowed(): boolean {
        return this.searchPermission && this.searchPermission.isAllowed === true;
    }

    /**
     * Show search modal
     */
    showSearchFilterModal(): void {
        this.filtersModal = this.modal.create(UserSearchFilterComponent, {
            filters: this.searchFilters // pass the collected filters
        });

        // capture the returned data
        this.filtersModal.onDidDismiss((filters?: Array<ISearchFilter>) => {
            if (filters && filters.length) {
                this.searchFilters = filters;
                this.searchByUserNameFilter = '';
                this.persistentStorage.setValue('search_filters', this.searchFilters);
                this.ref.markForCheck();

                if (this.isSearchAllowed) {
                    this.searchUsers();
                }
            }
        });

        this.filtersModal.present();
    }
 
    /**
     * Track user list
     */
    trackUserList(index: number, userData: IUserResponse): number | string {
        return userData.id;
    }

    /**
     * View profile
     */
    viewProfile(user: IUserResponse): void {
        this.nav.push(ProfileViewPage, {
            userId: user.id
        });
    }

    /**
     * Search users
     */
    searchUsers(): void {
        // stop previous requests 
        if (this.searchUsersSubscription) {
            this.searchUsersSubscription.unsubscribe();
        }

        this.isSearchStarted = true;
        this.isUserListLoading = true;
        this.keyboard.close();
 
        this.ref.markForCheck();

        let filters = [];

       // search only by a user name
       if (this.searchByUserNameFilter.trim()) {
            filters = filters.concat([{
                name: 'username',
                value: this.searchByUserNameFilter,
                type: QuestionManager.TYPE_TEXT
            }]);

            // remember the entered user name
            this.persistentStorage.setValue('search_by_user_name_filter', this.searchByUserNameFilter.trim());
        }
        else { // search using extra filters
            filters = this.searchFilters;

            // clear the user name
            this.persistentStorage.setValue('search_by_user_name_filter', '');
        }

        // search users
        this.searchUsersSubscription = this.users.searchUsers(filters).subscribe(userList => {
            this.userList = userList;
            this.isUserListLoading = false;
            this.ref.markForCheck();
        });
    }
}
