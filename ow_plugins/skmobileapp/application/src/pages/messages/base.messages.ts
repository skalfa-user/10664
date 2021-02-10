import { TranslateService } from 'ng2-translate';
import { ToastController } from 'ionic-angular';

// services
import { MessagesService } from 'services/messages';
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';

export abstract class BaseMessagesPage {
    /**
     * Constructor
     */
    constructor(
        protected siteConfigs: SiteConfigsService,
        protected translate: TranslateService,
        protected toast: ToastController,
        protected messages: MessagesService, 
        protected user: UserService) {}

    /**
     * Delete conversation
     */
    protected deleteConversation(conversationId: string | number): void {
        this.messages.deleteConversation(conversationId).subscribe();

        const toast = this.toast.create({
            message: this.translate.instant('conversation_has_been_deleted'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();
    }

    /**
     * Unblock user
     */
    protected unblockUser(userId: number): void {
        this.user.unblockUser(userId).subscribe();

        const toast = this.toast.create({
            message: this.translate.instant('profile_unblocked'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();
    }

    /**
     * Block user
     */
    protected blockUser(userId: number): void {
        this.user.blockUser(userId).subscribe();

        const toast = this.toast.create({
            message: this.translate.instant('profile_blocked'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();
    }

    /**
     * Mark conversation as unread 
     */
    protected markConversationAsUnread(conversationId: string | number): void {
        this.messages.markConversationAsUnRead(conversationId);

        const toast = this.toast.create({
            message: this.translate.instant('conversation_has_been_marked_as_unread'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();
    }

    /**
     * Mark conversation as read 
     */
    protected markConversationAsRead(conversationId: string | number): void {
        this.messages.markConversationAsRead(conversationId);

        const toast = this.toast.create({
            message: this.translate.instant('conversation_has_been_marked_as_read'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: this.siteConfigs.getConfig('toastDuration')
        });

        toast.present();
    }
}
