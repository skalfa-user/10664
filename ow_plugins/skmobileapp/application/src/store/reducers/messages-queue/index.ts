import { IMessage } from 'store/states';
import { IAppState } from 'store';

import {
    MESSAGES_RESEND_MESSAGE,
    MESSAGES_AFTER_ADD,
    MESSAGES_ERROR_ADD,
    CONVERSATIONS_AFTER_DELETE,
    MESSAGES_BEFORE_ADD,
    USERS_LOGOUT, 
    APPLICATION_RESET
} from 'store/actions';

// payloads
import {
    IByIdPayload,
    IMessageDataPayload
} from 'store/payloads'

/**
 * Messages queue initial state
 */
export const messagesQueueInitialState: Array<IMessage> = [];

/**
 * Messages queue reducer
 */
export const messagesQueue = (currentState: Array<IMessage>, action: any): Array<IMessage> => {
    // add initial state
    if (!currentState) {
        currentState = messagesQueueInitialState;
    }

    switch(action.type) {
        case MESSAGES_AFTER_ADD :
        case MESSAGES_ERROR_ADD :
            return currentState.length ? currentState.slice(1) : [];

        case CONVERSATIONS_AFTER_DELETE :
            const afterConversationDeletePayload: IByIdPayload = action.payload;

            return currentState.filter((message: IMessage) => message.conversation !== afterConversationDeletePayload.id)

        case MESSAGES_BEFORE_ADD :
        case MESSAGES_RESEND_MESSAGE :
            const messagesBeforeAddPayload: IMessageDataPayload = action.payload;

            return [
                ...currentState, 
                messagesBeforeAddPayload
            ];

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return messagesQueueInitialState;
    }
 
    return currentState; 
};

// selectors 

export const getMessagesQueue = (appState: IAppState) => appState.messagesQueue;

/**
 * Get first message from queue
 */
export function getFirstMessageFromQueue(appState: IAppState): IMessage | undefined {
    if (getMessagesQueue(appState).length) {
        return getMessagesQueue(appState)[0];
    }
}
