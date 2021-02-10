import { Injectable } from '@angular/core';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';
import { Observable } from 'rxjs/Observable';
import isEqual from 'lodash/isEqual';
import { Response } from '@angular/http';

// services
import { SiteConfigsService } from 'services/site-configs';
import { MatchActionsService } from 'services/match-actions';
import { UserService } from 'services/user';
import { SecureHttpService } from 'services/http';
import { StringUtilsService } from 'services/string-utils';
import { DateUtilsService } from 'services/date-utils';
import { FileUploaderService, IFileUploadResult, IFileUploadOptions } from 'services/file-uploader';

// responses
import { IConversationResponse, IMessageResponse } from './responses';

// schemas
import { conversationListSchema, messageListSchema } from './schemas';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload,
    IMessageDataPayload,
    IMessagesAfterAddPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';
import { IConversationData, IMessage } from 'store/states';

import {
    getRealMessagesIds,
    getUnreadMessagesIdList,
    getFirstUnreadMessageId,
    getConversationWithUserData,
    isMessageInPending,
    isMessageDeliveredWithError,
    getDeliveredMessageError,
    getFirstMessageFromQueue,
    getMessageList,
    getConversationByUserId,
    isMessageListFetched,
    isConversationNew,
    getNewConversationsCount,
    isConversationListFetched,
    getConversation,
    getConversationList,
    getMessagesImageAttachmentList,
    IConversationListItem
} from 'store/reducers';

import {
    MESSAGES_BEFORE_MARK_READ,
    MESSAGES_AFTER_MARK_READ,
    MESSAGES_ERROR_MARK_READ,
    MESSAGES_DELETE_MESSAGE,
    MESSAGES_RESEND_MESSAGE,
    MESSAGES_ERROR_ADD,
    MESSAGES_AFTER_ADD,
    MESSAGES_BEFORE_ADD,
    MESSAGES_LOAD_HISTORY,
    MESSAGES_LOAD_MESSAGE,
    MESSAGES_UPDATE,
    MESSAGES_LOAD,
    CONVERSATIONS_BEFORE_MARK_UNREAD,
    CONVERSATIONS_AFTER_MARK_UNREAD,
    CONVERSATIONS_ERROR_MARK_UNREAD,
    CONVERSATIONS_BEFORE_MARK_READ,
    CONVERSATIONS_AFTER_MARK_READ,
    CONVERSATIONS_ERROR_MARK_READ,
    CONVERSATIONS_SET,
    CONVERSATIONS_BEFORE_DELETE,
    CONVERSATIONS_AFTER_DELETE,
    CONVERSATIONS_ERROR_DELETE
} from 'store/actions';

export { IConversationData, IMessage, IMessageAttachment } from 'store/states';
export { IConversationListItem } from 'store/reducers';

@Injectable()
export class MessagesService {
    /**
     * Constructor
     */
    constructor (
        private fileUploader: FileUploaderService,
        private dateUtils: DateUtilsService,
        private stringUtils: StringUtilsService,
        private http: SecureHttpService,
        private ngRedux: NgRedux<IAppState>,
        private user: UserService,
        private matchActions: MatchActionsService,
        private siteConfigs: SiteConfigsService) {}

    /**
     * Get delivered message error
     */
    getDeliveredMessageError(message: IMessage): string {
        return getDeliveredMessageError(message);
    }

    /**
     * Is message delivered with error
     */
    isMessageDeliveredWithError(message: IMessage): boolean {
        return isMessageDeliveredWithError(message);
    }

    /**
     * Is message in pending
     */
    isMessageInPending(message: IMessage): boolean {
       return isMessageInPending(message);
    }

    /**
     * Delete message
     */
    deleteMessage(message: IMessage): void {
        const payload: IMessageDataPayload = message;

        this.ngRedux.dispatch({
            type: MESSAGES_DELETE_MESSAGE,
            payload: payload
        });

        if (message.file && message.attachments.length) {
            const [firstAttachment] = message.attachments;

            window.URL.revokeObjectURL(firstAttachment.downloadUrl);
        }
    }

    /**
     * Resend message
     */
    resendMessage(message: IMessage): void {
        const payload: IMessageDataPayload = {
            ...message
        };

        this.ngRedux.dispatch({
            type: MESSAGES_RESEND_MESSAGE,
            payload: payload
        });
    }

    /**
     * Add message
     */
    addMessage(message: IMessage): void {
        const payload: IMessageDataPayload = {
            ...message,
            id: this.stringUtils.getRandomString(),
            isAuthorized: true,
            isAuthor: true,
            timeStamp: this.dateUtils.getUnixTime()
        };

        this.ngRedux.dispatch({
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        });
    }

    /**
     * Update messages
     */
    updateMessages(messages: Array<IMessageResponse>): void {
        const payload: IEntitiesPayload = normalize(messages, messageListSchema);

        this.ngRedux.dispatch({
            type: MESSAGES_UPDATE,
            payload: payload
        });
    }

    /**
     * Load history messages
     */
    loadHistoryMessages(userId: number, firstMessageId: number | string, limit: number): Observable<Array<IMessageResponse>> {
        const messages = this.http.get('/mailbox/messages/history/user/' + userId, {
            beforeMessageId: firstMessageId,
            limit: limit
        });

        // normalize response
        messages.subscribe(response => {
            const payload: IEntitiesPayload = normalize(response, messageListSchema);

            this.ngRedux.dispatch({
                type: MESSAGES_LOAD_HISTORY,
                payload: payload
            });
        }, () => {});

        return messages;
    }

    /**
     * Load message
     */
    loadMessage(messageId: number | string): Observable<IMessageResponse> {
        const message = this.http.get('/mailbox/messages/' + messageId); 

        // normalize response
        message.subscribe(response => {
            this.ngRedux.dispatch({
                type: MESSAGES_LOAD_MESSAGE,
                payload: normalize([response], messageListSchema)
            });
        }, () => {});

        return message;
    }

    /**
     * Load messages
     */
    loadMessages(userId: number, limit: number): Observable<Array<IMessageResponse>> {
        const messages = this.http.get('/mailbox/messages/user/' + userId, {
            limit: limit
        });

        // normalize response
        messages.subscribe(response => {
            const payload: IEntitiesPayload = normalize(response, messageListSchema);

            this.ngRedux.dispatch({
                type: MESSAGES_LOAD,
                payload: payload
            });
        }, () => {});

        return messages;
    }

    /**
     * Mark messages as read
     */
    markMessagesAsRead(messageIdsList: Array<number | string>): Observable<any> {
        const payload: IByIdPayload = {
            id: messageIdsList
        };

        this.ngRedux.dispatch({
            type: MESSAGES_BEFORE_MARK_READ,
            payload: payload
        });

        const markMessages: Observable<any> = this.http.put('/mailbox/messages', {
            ids: this.getRealMessagesIds(messageIdsList)
        });

        markMessages.subscribe(() => {
            this.ngRedux.dispatch({
                type: MESSAGES_AFTER_MARK_READ,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: MESSAGES_ERROR_MARK_READ,
                payload: payload
            });
        });

        return markMessages;
    }

    /**
     * Delete conversation
     */
    deleteConversation(conversationId: string | number): Observable<any> {
        const payload: IByIdPayload = {
            id: conversationId
        };

        this.ngRedux.dispatch({
            type: CONVERSATIONS_BEFORE_DELETE,
            payload: payload
        });

        const deleteConversation: Observable<any> = this.http.delete('/mailbox/conversations/' + conversationId);

        deleteConversation.subscribe(() => {
            this.ngRedux.dispatch({
                type: CONVERSATIONS_AFTER_DELETE,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: CONVERSATIONS_ERROR_DELETE,
                payload: payload
            });
        });

        return deleteConversation;
    }

    /**
     * Mark conversation as read
     */
    markConversationAsRead(conversationId: string | number): Observable<any> {
        const payload: IByIdPayload = {
            id: conversationId
        };

        this.ngRedux.dispatch({
            type: CONVERSATIONS_BEFORE_MARK_READ,
            payload: payload
        });

        const markConversation: Observable<any> = this.http.put('/mailbox/conversations/' + conversationId, {
            isRead: true
        });

        markConversation.subscribe(() => {
            this.ngRedux.dispatch({
                type: CONVERSATIONS_AFTER_MARK_READ,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: CONVERSATIONS_ERROR_MARK_READ,
                payload: payload
            });
        });

        return markConversation;
    }

    /**
     * Mark conversation as unread
     */
    markConversationAsUnRead(conversationId: string | number): Observable<any> {
        const payload: IByIdPayload = {
            id: conversationId
        };

        this.ngRedux.dispatch({
            type: CONVERSATIONS_BEFORE_MARK_UNREAD,
            payload: payload
        });

        const markConversation: Observable<any> = this.http.put('/mailbox/conversations/' + conversationId, {
            isRead: false
        });

        markConversation.subscribe(() => {
            this.ngRedux.dispatch({
                type: CONVERSATIONS_AFTER_MARK_UNREAD,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: CONVERSATIONS_ERROR_MARK_UNREAD,
                payload: payload
            });
        });

        return markConversation;
    }

    /**
     * Is conversation new
     */
    isConversationNew(conversationData: IConversationListItem): boolean {
        return isConversationNew(conversationData);
    }

    /**
     * Set conversations
     */
    setConversations(conversations: Array<IConversationResponse>): void {
        const payload: IEntitiesPayload = normalize(conversations, conversationListSchema);

        this.ngRedux.dispatch({
            type: CONVERSATIONS_SET,
            payload: payload
        });
    }

    /**
     * Is chat allowed
     */
    isChatAllowed(recipientId: number): boolean {
        if (this.siteConfigs.isTinderSearchMode()) {
            const match = this.matchActions.getMatch(recipientId);

            if (match && match.isMutual) { // do we have a mutual connection?
                return true;
            }

            const user = this.user.getUser(recipientId);

            // check existing conversation
            if (user && user.conversation != null && this.getConversation(user.conversation)) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Watch messages queue
     */
    watchMessagesQueue(): Observable<IMessage> | undefined {
        return this.ngRedux.select((appState: IAppState) => getFirstMessageFromQueue(appState), isEqual);
    }

    /**
     * Watch message list
     */
    watchMessageList(conversationId: string): Observable<Array<IMessage> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getMessageList(conversationId)(appState), isEqual);
    }

    /**
     * Watch unread messages id list
     */
    watchUnreadMessagesIdList(conversationId: string): Observable<Array<number | string>> {
        return this.ngRedux.select((appState: IAppState) => getUnreadMessagesIdList(conversationId)(appState), isEqual);
    }

    /**
     * Watch new conversations count
     */
    watchNewConversationsCount(): Observable<number> {
        return this.ngRedux.select((appState: IAppState) => getNewConversationsCount()(appState));
    }

    /**
     * Watch is conversations fetched
     */
    watchIsConversationsFetched(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isConversationListFetched(appState));
    }

    /**
     * Watch conversation list
     */
    watchConversationList(userNameFilter: string = ''): Observable<Array<IConversationListItem> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getConversationList(userNameFilter)(appState), isEqual);
    }

    /**
     * Watch is message list fetched
     */
    watchIsMessageListFetched(userId: number): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isMessageListFetched(appState, userId));
    }

    /**
     * Is message list fetched
     */
    isMessageListFetched(userId: number): boolean {
        return isMessageListFetched(this.ngRedux.getState(), userId);
    }

    /**
     * Get first unread message id
     */
    getFirstUnreadMessageId(conversationId: string): number | undefined {
        return getFirstUnreadMessageId(this.ngRedux.getState(), conversationId);
    }

    /**
     * Get real messages ids
     */
    getRealMessagesIds(messagesIds: Array<string | number>): Array<number | string> {
        return getRealMessagesIds(this.ngRedux.getState(), messagesIds);
    }

    /**
     * Get conversation
     */
    getConversation(conversationId: string): IConversationData | undefined {
        return getConversation(this.ngRedux.getState(), conversationId);
    }

    /**
     * Get conversation with user data
     */
    getConversationWithUserData(conversationId: string): IConversationListItem | undefined {
        return getConversationWithUserData(this.ngRedux.getState(), conversationId);
    }

    /**
     * Get messages image attachment list
     */
    getMessagesImageAttachmentList(conversationId: string): Array<string> {
        return getMessagesImageAttachmentList(conversationId)(this.ngRedux.getState());
    }

    /**
     * Get conversation by user id
     */
    getConversationByUserId(userId: number): IConversationData | undefined {
        return getConversationByUserId(this.ngRedux.getState(), userId);
    }

    /**
     * Send text message
     */
    sendTextMessage(message: IMessage): Observable<any> {
        const sendMessage: Observable<any> = this.http.post('/mailbox/messages', message, {}, false);

        sendMessage.subscribe((response: IMessageResponse) => {
            this.processSuccessfulMessageResponse(message, response);
        }, (error) => this.processFailMessageResponse(message, error));

        return sendMessage;
    }

    /**
     * Send image message
     */
    sendImageMessage(message: IMessage): Observable<any> {
        const fileUploadOptions: IFileUploadOptions = {
            uri: '/mailbox/photo-messages',
            fileName: 'file',
            allowedMimeTypes: [],
            maxFileSize: 0,
            isBroadcastError: false
        };

        const sendImageMessage: Observable<any> = this.fileUploader.upload(message.file, fileUploadOptions, {
            id: message.id,
            opponentId: message.opponentId 
        });

        sendImageMessage.subscribe((response: IFileUploadResult) => {
            switch(response.type) {
                case FileUploaderService.SUCCESS_RESULT :
                    this.processSuccessfulMessageResponse(message, response.data);

                    const [firstAttachment] = message.attachments;
                    window.URL.revokeObjectURL(firstAttachment.downloadUrl);
                    break;

                case FileUploaderService.UPLOAD_ERROR_RESULT :
                    this.processFailMessageResponse(message, response.data);
                    break; 
            }
        });

        return sendImageMessage;
    }

    /**
     * Process fail message response
     */
    processFailMessageResponse(message: IMessage, error: Response): void {
        // try to extract the error details
        let errorDetails: any = '';

        try {
            errorDetails = error ? error.json() : '';
        }
        catch (e) {}

        const payload: IMessagesAfterAddPayload = {
            id: message.id,
            message: errorDetails && errorDetails.messagesError ? errorDetails.messagesError : ''
        };

        this.ngRedux.dispatch({
            type: MESSAGES_ERROR_ADD, 
            payload: payload
        });

        // if the error is not related with the  mailbox we will broadcast it
        if (!errorDetails || !errorDetails.messagesError) {
            this.http.broadcastError(error);
        }
    }

    /**
     * Process successful message response
     */
    processSuccessfulMessageResponse(message: IMessage, response: IMessageResponse): void {
        // normalize the response
        const result = normalize([response], messageListSchema);

        const payload: IMessagesAfterAddPayload = {
            id: message.id,
            message: result.entities.messages[message.id]
        };

        this.ngRedux.dispatch({
            type: MESSAGES_AFTER_ADD,
            payload: payload
        });
    }
}
