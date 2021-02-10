import { IMapType } from 'store/types';
import { IMessage } from 'store/states';
import { IAppState } from 'store';
import { createSelector } from 'reselect'
import merge from 'lodash/merge';
import sortBy from 'lodash/sortBy';
import omit from 'lodash/omit';
import mergeWith from 'lodash/mergeWith';

import { getConversations } from 'store/reducers';

import {
    MESSAGES_BEFORE_MARK_READ,
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
    USERS_LOGOUT, 
    APPLICATION_RESET
} from 'store/actions';

// payloads
import {
    IMessageDataPayload,
    IEntitiesPayload,
    IByIdPayload,
    IMessagesAfterAddPayload
} from 'store/payloads';

/**
 * Messages initial state
 */
export const messagesInitialState: IMapType<IMessage> = {};

/**
 * Messages reducer
 */
export const messages = (currentState: IMapType<IMessage>, action: any): IMapType<IMessage> => {
    // add initial state
    if (!currentState) {
        currentState = messagesInitialState;
    }

    switch(action.type) {
        case MESSAGES_BEFORE_MARK_READ : 
            const messagesBeforeMarkReadPayload: IByIdPayload = action.payload;
            const processedReadMessages = {};

            messagesBeforeMarkReadPayload.id.forEach(messageId => {
                processedReadMessages[messageId] = {
                    ...currentState[messageId],
                    _isRead: true
                };
            });

            return merge({}, currentState, processedReadMessages);

        case MESSAGES_ERROR_MARK_READ :
            const messagesErrorMarkReadPayload: IByIdPayload = action.payload;
            const processedUnreadMessages = {};

            messagesErrorMarkReadPayload.id.forEach(messageId => {
                processedUnreadMessages[messageId] = {
                    ...currentState[messageId],
                    _isRead: false
                };
            });

            return merge({}, currentState, processedUnreadMessages);

        case MESSAGES_DELETE_MESSAGE :
            const messagesDeletePayload: IMessageDataPayload = action.payload;

            return omit(currentState, [
                messagesDeletePayload.id
            ]);

        case MESSAGES_RESEND_MESSAGE :
            const messagesResendMessagePayload: IMessageDataPayload = action.payload;

            return merge({}, currentState, {
                [messagesResendMessagePayload.id]: {
                    ...messagesResendMessagePayload,
                    _isError: false,
                    _errorDescription: '',
                    _isPending: true
                }
            });

        case MESSAGES_BEFORE_ADD :
            const messagesBeforeAddPayload: IMessageDataPayload = action.payload;

            return merge({}, currentState, {
                [messagesBeforeAddPayload.id]: {
                    ...messagesBeforeAddPayload,
                    _isPending: true
                }
            });

        case MESSAGES_AFTER_ADD :
            const messagesAfterAddPayload: IMessagesAfterAddPayload = action.payload;

            return mergeWith({}, currentState, {
                [messagesAfterAddPayload.id]: {
                    ...messagesAfterAddPayload.message,
                    _isPending: false,
                    file: {}
                }
            }, (objValue, srcValue, key) => {
                switch(key) {
                    case 'attachments' :
                    case 'file' :
                        return srcValue; // replace values

                    default :
                }
            });

        case MESSAGES_ERROR_ADD :
            const messagesErrorAddPayload: IMessagesAfterAddPayload = action.payload;

            return merge({}, currentState, {
                [messagesErrorAddPayload.id]: {
                    ...currentState[messagesErrorAddPayload.id],
                    _isError: true,
                    _errorDescription: messagesErrorAddPayload.message ? messagesErrorAddPayload.message : ''
                }
            });

        case MESSAGES_LOAD_HISTORY :
        case MESSAGES_LOAD_MESSAGE :
        case MESSAGES_UPDATE :
        case MESSAGES_LOAD :
            const messagesLoadPayload: IEntitiesPayload = action.payload;

            if (messagesLoadPayload.entities.messages) {
                return merge({}, currentState, messagesLoadPayload.entities.messages);
            }

            return currentState;

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return messagesInitialState;
    }
 
    return currentState; 
};

// selectors 

export const getMessages = (appState: IAppState) => appState.messages;


/**
 * Get delivered message error
 */
export function getDeliveredMessageError(message: IMessage): string {
    if (message._isError === true && message._errorDescription) {
        return message._errorDescription;
    }

    return '';
}

/**
 * Is message delivered with error
 */
export function isMessageDeliveredWithError(message: IMessage): boolean {
    if (message._isError !== undefined) {
        return message._isError;
    }

    return false;
}

/**
 * Is message in pending
 */
export function isMessageInPending(message: IMessage): boolean {
    if (message._isPending !== undefined) {
        return message._isPending;
    }

    return false;
}

/**
 * Get message list
 */
export function getMessageList(conversationId: string): Function {
    return createSelector(
        [getConversations, getMessages],
        (conversations, messages): Array<IMessage> | undefined => {
            if (conversations.byId[conversationId]
                    && !conversations.byId[conversationId]._isHidden
                    && conversations.byId[conversationId].messages
                    && conversations.byId[conversationId].messages.length) {

                let messageList = [];

                conversations.byId[conversationId].messages.forEach((messageId: number) => {
                    messageList.push(messages[messageId]);
                });

                messageList = sortBy(messageList, [
                    (message) => message._isPending === true ? 1 : 0,
                    (message) => message.timeStamp 
                ]);

                return messageList;
            }
    });
}

/**
 * Get messages image attachment list
 */
export function getMessagesImageAttachmentList(conversationId: string): Function {
    return createSelector(
        [getConversations, getMessages],
        (conversations, messages): Array<string> => {
            const urlList: Array<string> = [];

            if (conversations.byId[conversationId]
                    && !conversations.byId[conversationId]._isHidden
                    && conversations.byId[conversationId].messages
                    && conversations.byId[conversationId].messages.length) {

                let messageList = [];

                conversations.byId[conversationId].messages.forEach((messageId: number) => {
                    messageList.push(messages[messageId]);
                });

                messageList = sortBy(messageList, [
                    (message) => message._isPending === true ? 1 : 0,
                    (message) => message.timeStamp 
                ]);

                messageList.forEach(message => {
                    if (message.attachments && message.attachments.length) {
                        message.attachments.forEach(attachment => {
                            if (attachment.type == 'image') {
                                urlList.push(attachment.downloadUrl);
                            }
                        });
                    }
                });
            }

            return urlList;
    });
}

/**
 * Get first unread message id
 */
export function getFirstUnreadMessageId(appState: IAppState, conversationId: string): number | undefined {
    const messageList = getMessageList(conversationId)(appState);

    if (messageList) {
        const message = messageList.find((message) => 
                message.isRecipientRead === false && message.isAuthor === false && message._isRead !== true);

        if (message) {
            return message.id;
        }
    }
}

/**
 * Get real messages ids
 */
export function getRealMessagesIds(appState: IAppState, messagesIds: Array<string | number>): Array<number | string> {
    const messages = getMessages(appState);
    const realMessagesIds: Array<number | string> = [];

    messagesIds.forEach(messageId => {
        if (messages[messageId]) {
            realMessagesIds.push(messages[messageId].id);
        }
    });

    return realMessagesIds;
}

/**
 * Get unread message id list
 */
export function getUnreadMessagesIdList(conversationId: string): Function {
    return createSelector(
        [getConversations, getMessages],
        (conversations, messages): Array<number | string> => {
            const idList: Array<number | string> = [];

            if (conversations.byId[conversationId]
                    && !conversations.byId[conversationId]._isHidden
                    && conversations.byId[conversationId].messages
                    && conversations.byId[conversationId].messages.length) {

                conversations.byId[conversationId].messages.forEach((messageId: number | string) => {
                    if (messages[messageId].isRecipientRead === false 
                            && messages[messageId].isAuthor === false && messages[messageId]._isRead !== true) {

                        idList.push(messageId);
                    }
                });
            }

            return idList;
    });
}
