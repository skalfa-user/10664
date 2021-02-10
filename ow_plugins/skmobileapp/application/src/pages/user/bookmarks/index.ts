import { Component, ChangeDetectionStrategy, ViewChild, ChangeDetectorRef, OnInit, OnDestroy } from '@angular/core';
import { NavController, List, ToastController, AlertController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { Observable } from 'rxjs/Observable';
import { ISubscription } from 'rxjs/Subscription';

// services
import { BookmarksService, IBookmarkListItem } from 'services/bookmarks';
import { SiteConfigsService } from 'services/site-configs';
import { MessagesService } from 'services/messages';
import { ApplicationService } from 'services/application';

// shared components
import { MatchActionsComponent } from 'shared/components/match-actions';

// pages
import { ProfileViewPage } from 'pages/profile';
import { MessagesPage } from 'pages/messages';

@Component({
    selector: 'bookmarks',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        BookmarksService
    ]
})

export class BookmarksPage implements OnInit, OnDestroy {
    @ViewChild(List) sliderBookmarkList: List;
    @ViewChild(MatchActionsComponent) matchActions: MatchActionsComponent;

    isBookmarksFetched$: Observable<boolean>;
    bookmarkList$: Observable<Array<IBookmarkListItem>>;

    private isTinderSearchModeSubscription: ISubscription;
    private isBookmarksFetchedSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public ref: ChangeDetectorRef,
        public application: ApplicationService,
        public messages: MessagesService,
        private alert: AlertController,
        private toast: ToastController,
        private translate: TranslateService,        
        private siteConfigs: SiteConfigsService,
        private bookmarks: BookmarksService, 
        private nav: NavController) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        //init watchers
        this.isBookmarksFetched$ = this.bookmarks.watchIsBookmarksFetched();
        this.bookmarkList$ = this.bookmarks.watchBookmarkList();

        // watch tinder mode status
        this.isTinderSearchModeSubscription = this.siteConfigs
            .watchIsTinderSearchMode()
            .subscribe(() => this.ref.markForCheck());

        // check if we need to load the bookmark list
        this.isBookmarksFetchedSubscription = this.isBookmarksFetched$.subscribe(isBookmarksFetched => {
            if (!isBookmarksFetched) {
                this.bookmarks.loadBookmarkList();
            }
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.isTinderSearchModeSubscription.unsubscribe();
        this.isBookmarksFetchedSubscription.unsubscribe();
    }

    /**
     * Track bookmark list
     */
    trackBookmarkList(index: number, bookmarkData: IBookmarkListItem): number | string {
        return bookmarkData.bookmark.id;
    }

    /**
     * View profile
     */
    viewProfile(bookmarkData: IBookmarkListItem): void {
        this.nav.push(ProfileViewPage, {
            userId: bookmarkData.user.id
        });
    }

    /**
     * Like user
     */
    likeUser(bookmarkData: IBookmarkListItem): void {
        this.sliderBookmarkList.closeSlidingItems();

        // like user
        this.matchActions.likeUser(bookmarkData.user.id, bookmarkData.user.userName);
    }

    /**
     * Show chat
     */
    showChat(bookmarkData: IBookmarkListItem): void {   
        this.nav.push(MessagesPage, {
            userId: bookmarkData.user.id
        });
    }

    /**
     * Remove bookmark confirmation
     */
    removeBookmarkConfirmation(bookmarkData: IBookmarkListItem): void {
        const buttons: any[] = [{
            text: this.translate.instant('no'),
            handler: () => this.sliderBookmarkList.closeSlidingItems()
        }, {
            text: this.translate.instant('yes'),
            handler: () => {
                this.bookmarks.deleteBookmark(bookmarkData.bookmark.id, bookmarkData.user.id).subscribe();

                const toast = this.toast.create({
                    message: this.translate.instant('profile_removed_from_bookmarks'),
                    closeButtonText: this.translate.instant('ok'),
                    showCloseButton: true,
                    duration: this.siteConfigs.getConfig('toastDuration')
                });
        
                toast.present();
            }
        }];

        const confirm = this.alert.create({
            message: this.translate.instant('delete_bookmark_confirmation'),
            buttons: buttons
        });

        confirm.present();
    }
}
