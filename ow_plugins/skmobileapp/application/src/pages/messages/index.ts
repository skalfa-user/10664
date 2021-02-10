import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, AfterViewChecked, ChangeDetectorRef, ViewChild } from '@angular/core';
import { Modal, InfiniteScroll, Content, ModalController, NavParams, AlertController, ToastController, ActionSheetController, NavController } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';
import { TranslateService } from 'ng2-translate';

// services
import { MessagesService, IConversationData, IMessage, IMessageAttachment } from 'services/messages';
import { UserService, IUserWithAvatar } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';
import { AuthService } from 'services/auth';
import { PermissionsService, IPermission } from 'services/permissions';
import { MatchedUsersService } from 'services/matched-users';

// pages
import { DashboardPage } from 'pages/dashboard';
import { ProfileViewPage } from 'pages/profile';

// components
import { PermissionsComponent } from 'shared/components/permissions';
import { FileUploaderComponent, IFileUploadResult } from  'shared/components/file-uploader';
import { PhotosViewerComponent } from 'shared/components/photos-viewer';

// base messages page
import { BaseMessagesPage } from './base.messages'

@Component({
    selector: 'messages',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class MessagesPage extends BaseMessagesPage implements OnInit, OnDestroy, AfterViewChecked {
    @ViewChild(Content) content: Content;
    @ViewChild(PermissionsComponent) permissionsComponent: PermissionsComponent;
    @ViewChild(InfiniteScroll) infiniteScroll: InfiniteScroll = null;
    @ViewChild(FileUploaderComponent) fileUploader: FileUploaderComponent;

    isPageLoading: boolean = true;
    opponent: IUserWithAvatar;
    messageList: Array<IMessage> = [];
    readMessagePermission: IPermission;
    unreadMessageId: number = 0;
    message: string = '';
    unreadMessagesIds: Array<string | number> = [];
    isContentScrollerActive: boolean = false;

    private isPrevPageProfile: boolean = false;
    private previewAttachmentImagesModal: Modal;
    private userId: number;
    private isNewConversation: boolean = false;
    private conversationId: string;
    private isConversationsFetchedSubscription: ISubscription;
    private opponentDataSubscription: ISubscription;
    private messageListSubscription: ISubscription;
    private permissionsSubscription: ISubscription;
    private siteConfigsSubscription: ISubscription;
    private unreadMessagesSubscription: ISubscription;
    private contentScrollSubscription: ISubscription;
    private newChatPermission: IPermission;
    private replyChatPermission: IPermission;
    private isMessageListLoading: boolean = false;
    private isAllowedMarkConversationAsRead: boolean = true;
    private isNeedToScrollContent: boolean = false;
    private isLoadHistoryReachedEnd: boolean = false;
    private isMessagesPageActive: boolean = true;
    private scrollThreshold: number = 300;

    /**
     * Constructor
     */
    constructor(
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        protected toast: ToastController,
        protected messages: MessagesService,
        protected user: UserService,
        private permissions: PermissionsService,
        private auth: AuthService,
        private nav: NavController,
        private actionSheet: ActionSheetController,
        private alert: AlertController,
        private ref: ChangeDetectorRef,
        private modal: ModalController,
        private matchedUsers: MatchedUsersService,
        private navParams: NavParams) 
    {
        super(
            siteConfigs, 
            translate, 
            toast, 
            messages, 
            user
        );

        this.userId = this.navParams.get('userId');
        this.conversationId = this.auth.getUserId() + '_' + this.userId;
        this.isPrevPageProfile = this.navParams.get('isPrevPageProfile') === true;
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        // init watchers
        this.isConversationsFetchedSubscription = this.messages.watchIsConversationsFetched().subscribe(isFetched => {
            if (isFetched) {
                this.initMessageList();
            }
        });

        // watch opponent user data changes
        this.opponentDataSubscription = this.user.watchUserWithAvatar(this.userId).subscribe(userData => {
            this.opponent = userData;
            this.ref.markForCheck();
        });

        // watch message list changes
        this.messageListSubscription = this.messages.watchMessageList(this.conversationId).subscribe(messages => {
            this.messageList = messages ?  messages : [];

            if (this.messageList.length) {
                this.isNewConversation = false;
            }

            this.ref.markForCheck();
        });

        // watch permissions changes
        this.permissionsSubscription = this.permissions.watchMeGroup([
            'mailbox_reply_to_chat_message',
            'mailbox_send_chat_message',
            'mailbox_read_chat_message'
        ]).subscribe((permissions) => {
            [this.replyChatPermission, this.newChatPermission, this.readMessagePermission] = permissions;

            this.ref.markForCheck();
        });

        // watch configs changes
        this.siteConfigsSubscription = this.siteConfigs.watchConfigGroup([
            'attachMaxUploadSize',
            'validImageMimeTypes',
            'messagesLimit'
        ]).subscribe(() => { 
            this.ref.markForCheck();
        });

        // watch unread message ids list
        this.unreadMessagesSubscription = this.messages.watchUnreadMessagesIdList(this.conversationId).subscribe(ids => {
            this.unreadMessagesIds = ids;

            // scroll content to the bottom
            if (this.isContentScrolledToBottom()) {
                this.isNeedToScrollContent = true;
                this.ref.markForCheck();

                return;
            }

            this.findFirstUnreadMessageId();
            this.ref.markForCheck();
        });

        // watch content scrolling
        this.contentScrollSubscription = this.content.ionScrollEnd.subscribe(() => {
            if (this.isContentScrolledToBottom()) {
                this.isContentScrollerActive = false;
                this.markUnreadMessages();
                this.ref.detectChanges();

                return;
            }

            this.isContentScrollerActive = true;
            this.ref.detectChanges();
        });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.isConversationsFetchedSubscription.unsubscribe();
        this.opponentDataSubscription.unsubscribe();
        this.messageListSubscription.unsubscribe();
        this.permissionsSubscription.unsubscribe();
        this.siteConfigsSubscription.unsubscribe();
        this.unreadMessagesSubscription.unsubscribe();
        this.contentScrollSubscription.unsubscribe();
    }

    /**
     * View rendered
     */
    async ngAfterViewChecked(): Promise<any> {
        // scroll content
        if (this.isNeedToScrollContent && this.isMessagesPageActive) {
            this.isNeedToScrollContent = false;
            this.ref.markForCheck();
 
            await this.content.scrollToBottom(0);
        }
    }

    /**
     * Page is going to be active
     */
    ionViewWillEnter(): void {
        this.unreadMessageId = 0;
        this.isMessagesPageActive = true;
 
        // enable the active history loader
        if (this.infiniteScroll) {
            this.infiniteScroll.enable(true);
        }

        this.findFirstUnreadMessageId();
        this.ref.markForCheck();
    }

    /**
     * Page is going to be inactive
     */
    ionViewWillLeave(): void {
        this.isMessagesPageActive = false;
        this.ref.markForCheck();

        // disable the active history loader
        if (this.infiniteScroll) {
            this.infiniteScroll.enable(false);
        }

        // close the preview attachments modal window if it still active
        if (this.previewAttachmentImagesModal) {
            this.previewAttachmentImagesModal.dismiss();
        }

        // mark the matched user as read
        const matchedUserData = this.matchedUsers.getMatchedUserData(this.userId);

        if (matchedUserData && this.matchedUsers.isMatchedUserNew(matchedUserData)) {
            this.matchedUsers.markMatchedUserAsRead(matchedUserData.matchedUser.id);
        }

        // mark the conversation as read
        const conversationData = this.messages.getConversationWithUserData(this.conversationId);

        if (this.isAllowedMarkConversationAsRead 
                    && conversationData && this.messages.isConversationNew(conversationData)) {

            this.messages.markConversationAsRead(this.conversationId).subscribe();
        }
    }

    /**
     * Is loading history allowed
     */
    get isLoadingHistoryAllowed(): boolean {
        return !this.isPageLoading && !this.isLoadHistoryReachedEnd 
                && this.messageList.length >= this.messagesLimit && this.isMessagesPageActive;
    }

    /**
     * Is send message area allowed
     */
    get isSendMessageAreaAllowed(): boolean {
        // start a new conversation
        if (this.isNewConversation && 
                (this.newChatPermission.isAllowed || this.newChatPermission.isPromoted)) {

            return true;
        }

        // continue the conversation
        return this.replyChatPermission.isAllowed || this.replyChatPermission.isPromoted;
    }

    /**
     * Is send message area promoted
     */
    get isSendMessageAreaPromoted(): boolean {
        // start a new conversation
        if (this.isNewConversation && this.newChatPermission.isPromoted) {
            return true;
        }

        // continue the conversation
        return this.replyChatPermission.isPromoted
    }

    /**
     * Is message valid
     */
    get isMessageValid(): boolean {
        return this.message.trim() != '' || this.isSendMessageAreaPromoted;
    }

    /**
     * Get image mime types
     */
    get getImageMimeTypes(): Array<string> {
        return this.siteConfigs.getConfig('validImageMimeTypes');
    }

    /**
     * Get attach max upload size
     */
    get getAttachMaxUploadSize(): number {
        return this.siteConfigs.getConfig('attachMaxUploadSize');
    }

    /**
     * Get messages limit
     */
    get messagesLimit(): number {
        return this.siteConfigs.getConfig('messagesLimit');
    }

    /**
     * Preview attached images
     */
    previewAttachedImages(currentUrl: string): void {
        const urls: Array<string> = this.messages.getMessagesImageAttachmentList(this.conversationId);

        // show photos viewer
        this.previewAttachmentImagesModal = this.modal.create(PhotosViewerComponent, {
            activeIndex: urls.indexOf(currentUrl),
            urls: urls
        });

        this.previewAttachmentImagesModal.present();
    }

    /**
     * Show image chooser
     */
    showImageChooser(): void {
        // check sending message permission
        if (this.isSendMessageAreaPromoted || !this.isSendMessageAreaAllowed) {
            this.permissionsComponent.showAccessDeniedAlert();

            return;
        }

        this.fileUploader.showFileChooser();
    }

    /**
     * Send image message
     */
    sendImageMessage(response: IFileUploadResult): void {
        // check sending message permission
        if (this.isSendMessageAreaPromoted || !this.isSendMessageAreaAllowed) {
            this.permissionsComponent.showAccessDeniedAlert();

            return;
        }

        const image: IMessageAttachment = {
            downloadUrl: window.URL.createObjectURL(response.data),
            fileName: response.data.name,
            fileSize: response.data.size,
            type: 'image'
        };

        const message: IMessage = {
            conversation: this.conversationId,
            opponentId: this.userId,
            attachments: [
                image
            ],
            file: response.data
        };

        this.messages.addMessage(message);
        this.unreadMessageId = 0;
        this.isNeedToScrollContent = true;
        this.ref.markForCheck();
    }

    /**
     * Send message
     */
    sendMessage(): void {
        // check sending message permission
        if (this.isSendMessageAreaPromoted || !this.isSendMessageAreaAllowed) {
            this.permissionsComponent.showAccessDeniedAlert();

            return;
        }

        const message: IMessage = {
            text: this.message,
            conversation: this.conversationId,
            opponentId: this.userId
        };

        this.messages.addMessage(message);
 
        this.message = '';
        this.unreadMessageId = 0;

        this.isNeedToScrollContent = true;
        this.ref.markForCheck();
    }

    /**
     * View profile
     */
    viewProfile(): void {
        // don't open the profile page twice it takes a lot of resources
        if (this.isPrevPageProfile) {
            this.nav.pop();

            return;
        }

        this.nav.push(ProfileViewPage, {
            userId: this.userId,
            isPrevPageMessages: true
        });
    }

    /**
     * Track message list
     */
    trackMessageList(index: number, message: IMessage): number | string {
        return message.id;
    }

    /**
     * Load history
     */
    loadHistory(): void {
        const [firstMessage] = this.messageList;

        this.messages.loadHistoryMessages(this.userId, firstMessage.id, this.messagesLimit).subscribe((messages) => {
            if (this.infiniteScroll) {
                if (!messages.length || messages.length < this.messagesLimit) {
                    this.isLoadHistoryReachedEnd = true;
                    this.ref.markForCheck();
                }

                this.infiniteScroll.complete();
            }
        });
    }

    /**
     * Show conversation actions
     */
    showConversationActions(): void {
        const buttons: any = [];

        // block / unblock a user
        buttons.push({
            text: this.user.isUserBlocked(this.opponent.user)
                ? this.translate.instant('unblock_profile')
                : this.translate.instant('block_profile'),
            handler: () => {
                // unblock the user
                if (this.user.isUserBlocked(this.opponent.user)) {
                    this.unblockUser(this.opponent.user.id);

                    return;
                }

                // block the user
                const confirm = this.alert.create({
                    message: this.translate.instant('block_profile_confirmation'),
                    buttons: [
                        {
                            text: this.translate.instant('cancel')
                        },
                        {
                            text: this.translate.instant('block_profile'),
                            handler: () => this.blockUser(this.opponent.user.id)
                        }
                    ]
                });

                confirm.present();
            }
        });

        const conversation: IConversationData = this.messages.getConversationByUserId(this.userId);

        // do we have a conversation?
        if (conversation) {
            // delete conversation
            buttons.push({
                text: this.translate.instant('delete_conversation'),
                handler: () => {
                    const buttons: any[] = [{
                        text: this.translate.instant('no')
                    }, {
                        text: this.translate.instant('yes'),
                        handler: () => {
                            this.deleteConversation(conversation.id);
                            this.nav.setRoot(DashboardPage);
                        }
                    }];

                    const confirm = this.alert.create({
                        message: this.translate.instant('delete_conversation_confirmation'),
                        buttons: buttons
                    });

                    confirm.present();
                }
            });

            // mark as unread conversation
            buttons.push({
                text: this.translate.instant('mark_unread_conversation'),
                handler: () => {
                    this.isAllowedMarkConversationAsRead = false;
                    this.markConversationAsUnread(conversation.id);

                    this.nav.setRoot(DashboardPage);
                }
            });
        }

        const actionSheet = this.actionSheet.create({
            buttons: buttons
        });

        actionSheet.present();
    }

    /**
     * Load message list
     */
    loadMessageList(initLoading: boolean = true): void {
        if (this.isMessageListLoading) {
            return;
        }
 
        if (initLoading) {
            this.isPageLoading = true;
            this.ref.markForCheck();
        }

        this.isMessageListLoading = true;

        this.messages.loadMessages(this.userId, this.messagesLimit).subscribe(() => {
            if (initLoading) {
                this.isPageLoading = false;
                this.isNeedToScrollContent = true; 
                this.findFirstUnreadMessageId();
            }

            this.isMessageListLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Is system message params equals
     */
    isSystemMessageParamsEquals(text: string, entityType: string, eventName: string): boolean {
        if (text) {
            const params: any = JSON.parse(text);

            if (params.entityType == entityType && params.eventName == eventName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find first unread message
     */
    private findFirstUnreadMessageId(): void {
        const firstUnreadMessageId: number = this.messages.getFirstUnreadMessageId(this.conversationId);

        if (firstUnreadMessageId) {
            this.unreadMessageId = firstUnreadMessageId;
        }
    }

    /**
     * Init message list
     */
    private initMessageList(): void {
        // start a new conversation
        if (!this.messages.getConversationByUserId(this.userId)) {
            this.isNewConversation = true;
            this.isPageLoading = false;
            this.ref.markForCheck();

            return;
        }

        // load messages
        if (!this.messages.isMessageListFetched(this.userId)) {
            this.loadMessageList();

            return;
        }

        // messages already loaded
        this.isPageLoading = false;
        this.isNeedToScrollContent = true;
        this.ref.markForCheck();
    }

    /**
     * Mark unread messages
     */
    private markUnreadMessages(): void {
        if (this.unreadMessagesIds && this.unreadMessagesIds.length) {
            this.messages.markMessagesAsRead(this.unreadMessagesIds).subscribe();
            this.unreadMessagesIds = [];
        }
    }

    /**
     * Is content scrolled to the bottom
     */
    private isContentScrolledToBottom(): boolean {
        const dimensions = this.content.getContentDimensions();

        const scrollTop = this.content.scrollTop;
        const contentHeight = dimensions.contentHeight;
        const scrollHeight = dimensions.scrollHeight;

        if ((scrollTop + contentHeight + this.scrollThreshold) >= scrollHeight) {
            return true;
        }

        return false
    }
}
