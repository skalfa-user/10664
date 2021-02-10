import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ViewChild, ChangeDetectorRef } from '@angular/core';
import { NavController, List, AlertController, ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';
import { Observable } from 'rxjs/Observable';
import { ISubscription } from 'rxjs/Subscription';

// services
import { GuestsService, IGuestListItem } from 'services/guests';
import { SiteConfigsService } from 'services/site-configs';
import { MessagesService } from 'services/messages';
import { ApplicationService } from 'services/application';

// components
import { MatchActionsComponent } from 'shared/components/match-actions';

// pages
import { ProfileViewPage } from 'pages/profile';
import { MessagesPage } from 'pages/messages';

@Component({
    selector: 'guests',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class GuestsPage implements OnInit, OnDestroy {
    @ViewChild(List) sliderGuestList: List;
    @ViewChild(MatchActionsComponent) matchActions: MatchActionsComponent;

    isGuestsFetched$: Observable<boolean>;
    guestList$: Observable<Array<IGuestListItem>>;

    private isTinderSearchModeSubscription: ISubscription;
    private notNotifiedGuestsSubscription: ISubscription;
    private notNotifiedGuestsCounter: number = 0;

    /**
     * Constructor
     */
    constructor(
        public application: ApplicationService,
        public guests: GuestsService, 
        public messages: MessagesService,
        public ref: ChangeDetectorRef,
        private siteConfigs: SiteConfigsService,
        private toast: ToastController,
        private alert: AlertController,
        private translate: TranslateService,
        private nav: NavController) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        this.guests.markAllGuestsAsNotified();
 
        // init watchers
        this.isGuestsFetched$ = this.guests.watchIsGuestsFetched();
        this.guestList$ = this.guests.watchGuestList();

        // watch tinder mode status
        this.isTinderSearchModeSubscription = this.siteConfigs
            .watchIsTinderSearchMode()
            .subscribe(() => this.ref.markForCheck());

        // watch new guests
        this.notNotifiedGuestsSubscription = this.guests
            .watchNotNotifiedGuestCount()
            .subscribe((newGuests: number) => {
                if (newGuests) {
                    const isGuestsPageActive = this.nav.last().instance instanceof GuestsPage;

                    if (isGuestsPageActive && !this.notNotifiedGuestsCounter) {
                        this.showNewGuestsNotification(newGuests);

                        return;
                    }

                    this.notNotifiedGuestsCounter = newGuests;
                }
            });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.guests.markAllGuestsAsRead().subscribe();
        this.isTinderSearchModeSubscription.unsubscribe();
        this.notNotifiedGuestsSubscription.unsubscribe();
    }

    /**
     * Page view did enter
     */
    ionViewDidEnter(): void {
        // show a notification about new guests
        if (this.notNotifiedGuestsCounter) {
            this.showNewGuestsNotification(this.notNotifiedGuestsCounter);
        }
    }

    /**
     * View profile
     */
    viewProfile(guestData: IGuestListItem): void {
        this.guests.markGuestsAsRead(guestData.guest.id);

        this.nav.push(ProfileViewPage, {
            userId: guestData.user.id
        });
    }

    /**
     * Show chat
     */
    showChat(guestData: IGuestListItem): void {
        this.guests.markGuestsAsRead(guestData.guest.id);
   
        this.nav.push(MessagesPage, {
            userId: guestData.user.id
        });
    }
 
    /**
     * Track guest list
     */
    trackGuestList(index: number, guestData: IGuestListItem): number {
        return guestData.guest.id;
    }
 
    /**
     * Remove guest confirmation
     */
    removeGuestConfirmation(guestData: IGuestListItem): void {
        const buttons: any[] = [{
            text: this.translate.instant('no'),
            handler: () => this.sliderGuestList.closeSlidingItems()
        }, {
            text: this.translate.instant('yes'),
            handler: () => {
                this.guests.deleteGuest(guestData.guest.id).subscribe();

                const toast = this.toast.create({
                    message: this.translate.instant('profile_removed_from_guests'),
                    closeButtonText: this.translate.instant('ok'),
                    showCloseButton: true,
                    duration: this.siteConfigs.getConfig('toastDuration')
                });
        
                toast.present();
            }
        }];

        const confirm = this.alert.create({
            message: this.translate.instant('delete_guest_confirmation'),
            buttons: buttons
        });

        confirm.present();
    }

    /**
     * Like user
     */
    likeUser(guestData: IGuestListItem): void {
        // mark guest as viewed and close sliding menu
        this.guests.markGuestsAsRead(guestData.guest.id);
        this.sliderGuestList.closeSlidingItems();

        // like user
        this.matchActions.likeUser(guestData.user.id, guestData.user.userName);
    }

    /**
     * Show new guests notification
     */
    private showNewGuestsNotification(newGuests: number) {
        const toast = this.toast.create({
            message: this.translate.instant('new_guests_counter', {
                count: newGuests
            }),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();

        this.guests.markAllGuestsAsNotified();
        this.notNotifiedGuestsCounter = 0;
    }
}
