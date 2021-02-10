import { Component, ChangeDetectionStrategy, Input, Output, EventEmitter, ChangeDetectorRef } from '@angular/core';
import { ActionSheetController, AlertController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { MessagesService, IMessage } from 'services/messages';
import { IPermission } from 'services/permissions';

@Component({
    selector: 'plain-message',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class PlainMessageComponent {
    @Input() prevMessage: IMessage;
    @Input() set messageData(message: IMessage) {
        this.message = message;

        if (this.message.isAuthorized && this.isMessageWaitingForAuthorize) {
            this.isMessageWaitingForAuthorize = false;
            this.ref.markForCheck();
        }
    };

    @Input() set setReadMessagePermission(permission: IPermission) {
        this.readMessagePermission = permission;

        // reload the message list in the parent component
        if (this.readMessagePermission.isAllowed && !this.message.isAuthorized) {
            this.needToAuthorize.emit();

            this.isMessageWaitingForAuthorize = true;
            this.ref.markForCheck();
        }
    };

    @Output() readMessageDenied: EventEmitter<undefined> = new EventEmitter();
    @Output() needToAuthorize: EventEmitter<undefined> = new EventEmitter();
    @Output() previewImage: EventEmitter<string> = new EventEmitter();

    message: IMessage;
    isMessageLoading: boolean = false;
    isMessageWaitingForAuthorize: boolean = false;   
    readMessagePermission: IPermission;

    /**
     * Constructor
     */
    constructor(
        public messages: MessagesService,
        private actionSheet: ActionSheetController,
        private translate: TranslateService,
        private alert: AlertController,
        private ref: ChangeDetectorRef) {}

    /**
     * View photo 
     */
    viewPhoto(url: string): void {
        this.previewImage.emit(url);
    }

    /**
     * Show message
     */
    showMessage(): void {
        this.isMessageLoading = true;
        this.ref.markForCheck();
 
        // load message data
        this.messages.loadMessage(this.message.id).subscribe(() => {
            this.isMessageLoading = false;
            this.ref.markForCheck();
        });
    }

    /**
     * Show purchases page
     */
    showPurchasesPage(): void {
        this.readMessageDenied.emit();
    }

    /**
     * Show messages actions
     */
    showMessageActions(): void {
        const buttons: any[] = [];

        // delete message
        buttons.push({
            text: this.translate.instant('delete_message'),
            handler: () => {
                const buttons: any[] = [{
                    text: this.translate.instant('no')
                }, {
                    text: this.translate.instant('yes'),
                    handler: () => this.messages.deleteMessage(this.message)
                }];

                const confirm = this.alert.create({
                    message: this.translate.instant('delete_message_confirmation'),
                    buttons: buttons
                });

                confirm.present();
            }
        });

        // resend message
        buttons.push({
            text: this.translate.instant('resend_message'),
            handler: () => this.messages.resendMessage(this.message)
        });

        const actionSheet = this.actionSheet.create({
            subTitle: this.messages.getDeliveredMessageError(this.message),
            buttons: buttons
        });

        actionSheet.present();
    }
}
