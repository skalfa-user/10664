
import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef, Input } from '@angular/core';
import { ToastController, NavController, AlertController, ActionSheetController } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';
import { Keyboard } from '@ionic-native/keyboard';
import { ISubscription } from 'rxjs/Subscription';
import { TranslateService } from 'ng2-translate';

// services
import { MessagesService, IConversationListItem } from 'services/messages';
import { MatchedUsersService, IMatchedUserListItem } from 'services/matched-users';
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';

// pages
import { MessagesPage } from 'pages/messages';

// base messages page
import { BaseMessagesPage } from 'pages/messages/base.messages'

@Component({
    selector: 'conversations',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class ConversationsComponent extends BaseMessagesPage implements OnInit, OnDestroy {
    @Input() isDashboardLoading: boolean;

    isMatchedUsersFetched$: Observable<boolean>;
    isConversationsFetched$: Observable<boolean>;
    conversationList: Array<IConversationListItem> = [];
    matchedUserList: Array<IMatchedUserListItem> = [];
    userNameFilter: string = '';

    private conversationListSubscription: ISubscription;
    private matchedUserListSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        public matchedUsers: MatchedUsersService,
        public user: UserService,
        public messages: MessagesService,
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        protected toast: ToastController,
        private actionSheet: ActionSheetController,
        private alert: AlertController,
        private nav: NavController,
        private ref: ChangeDetectorRef,
        private keyboard: Keyboard) 
    {
        super(
            siteConfigs, 
            translate, 
            toast, 
            messages, 
            user
        );
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // init watchers
        this.isConversationsFetched$ = this.messages.watchIsConversationsFetched();
        this.isMatchedUsersFetched$ = this.matchedUsers.watchIsMatchedUsersFetched();

        this.subscribeToSources();
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.unsubscribeFromSources();
    }

    /**
     * Username filter changed
     */
    userNameFilterChanged(): void {
        this.unsubscribeFromSources();
        this.subscribeToSources();

        this.ref.markForCheck();
    }

    /**
     * Show chat
     */
    showChat(userId: number): void {
        this.nav.push(MessagesPage, {
            userId: userId
        });
    }

    /**
     * Show conversation actions
     */
    showConversationActions(conversationData: IConversationListItem): void {
        const isConversationNew: boolean = this.messages.isConversationNew(conversationData);
        const buttons: any[] = [];

        // block (unblock) opponent
        if (!this.user.isUserBlocked(conversationData.user)) {
            buttons.push({
                text: this.translate.instant('block_profile'),
                handler: () => {
                    const buttons: any[] = [{
                        text: this.translate.instant('no')
                    }, {
                        text: this.translate.instant('yes'),
                        handler: () => {
                            this.blockUser(conversationData.user.id);
                            this.ref.markForCheck();
                        }
                    }];

                    const confirm = this.alert.create({
                        message: this.translate.instant('block_profile_confirmation'),
                        buttons: buttons
                    });

                    confirm.present();
                }
            });
        }
        else {
            buttons.push({
                text: this.translate.instant('unblock_profile'),
                handler: () => {
                    this.unblockUser(conversationData.user.id);
                    this.ref.markForCheck();
                }
            });
        }

        // delete conversation
        buttons.push({
            text: this.translate.instant('delete_conversation'),
            handler: () => {
                const buttons: any[] = [{
                    text: this.translate.instant('no')
                }, {
                    text: this.translate.instant('yes'),
                    handler: () => {
                        this.deleteConversation(conversationData.conversation.id);
                        this.ref.markForCheck();
                    }
                }];

                const confirm = this.alert.create({
                    message: this.translate.instant('delete_conversation_confirmation'),
                    buttons: buttons
                });

                confirm.present();
            }
        });

        // mark read/unread conversation
        buttons.push({
            text: isConversationNew
                ? this.translate.instant('mark_read_conversation')
                : this.translate.instant('mark_unread_conversation'),
            handler: () => {
                isConversationNew
                    ? this.markConversationAsRead(conversationData.conversation.id)
                    : this.markConversationAsUnread(conversationData.conversation.id);

                this.ref.markForCheck();
            }
        });

        const actionSheet = this.actionSheet.create({
            buttons: buttons
        });

        actionSheet.present();
    }
 
    /**
     * Close keyboard
     */
    closeKeyboard(): void {
        this.keyboard.close();
    }

    /**
     * Track conversation list
     */
    trackConversationList(index: number, conversationData: IConversationListItem): string | number {
        return conversationData.conversation.id;
    }

    /**
     * Subscribe to sources
     */
    private subscribeToSources(): void {
        this.conversationListSubscription = this.messages
            .watchConversationList(this.userNameFilter)
            .subscribe(conversations => {
                this.conversationList = conversations ?  conversations : [];
                this.ref.markForCheck();
            });

        this.matchedUserListSubscription = this.matchedUsers
            .watchMatchedUserList(this.userNameFilter)
            .subscribe(matchedUsers => {
                this.matchedUserList = matchedUsers ? matchedUsers : [];
                this.ref.markForCheck();
            });
    }

    /**
     * Unsubscribe from sources
     */
    private unsubscribeFromSources(): void {
        this.conversationListSubscription.unsubscribe();
        this.matchedUserListSubscription.unsubscribe();
    }
}
