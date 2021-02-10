import { IAppState } from 'store';
import merge from 'lodash/merge';
import omit from 'lodash/omit';
import pick from 'lodash/pick';
import mapValues from 'lodash/mapValues';
import mergeWith from 'lodash/mergeWith';
import isArray from 'lodash/isArray';
import forOwn from 'lodash/forOwn';
import uniq from 'lodash/uniq';
import find from 'lodash/find';
import { createSelector } from 'reselect'

import { getActiveAvatars, getUsers, getUser, isAvatarVisible } from 'store/reducers';

import { 
    IConversations,
    IConversationData,
    IUser,
    IAvatarData
} from 'store/states';

import {
    MESSAGES_DELETE_MESSAGE,
    MESSAGES_BEFORE_ADD,
    MESSAGES_LOAD_HISTORY,
    MESSAGES_UPDATE,
    MESSAGES_LOAD,
    CONVERSATIONS_BEFORE_MARK_READ,
    CONVERSATIONS_ERROR_MARK_READ,
    CONVERSATIONS_BEFORE_MARK_UNREAD,
    CONVERSATIONS_ERROR_MARK_UNREAD,
    CONVERSATIONS_BEFORE_DELETE,
    CONVERSATIONS_AFTER_DELETE,
    CONVERSATIONS_ERROR_DELETE,
    CONVERSATIONS_SET,
    USERS_LOGOUT, 
    APPLICATION_RESET,
} from 'store/actions';

// payloads
import {
    IEntitiesPayload,
    IMessageDataPayload,
    IByIdPayload
} from 'store/payloads';

/**
 * Conversations initial state
 */
export const conversationsInitialState: IConversations = {
    isFetched: false,
    byId: {},
    allIds: []
};

/**
 * Conversations reducer
 */
export const conversations = (currentState: IConversations, action: any): IConversations => {
    // add initial state
    if (!currentState) {
        currentState = conversationsInitialState;
    }

    switch(action.type) {
        case MESSAGES_DELETE_MESSAGE :
            const messagesDeletePayload: IMessageDataPayload = action.payload;
            const messagesCount = currentState.byId[messagesDeletePayload.conversation].messages.length;

            if (messagesCount - 1 > 0) {
                const conversation = {
                    [messagesDeletePayload.conversation]: currentState.byId[messagesDeletePayload.conversation]
                };

                return {
                    ...currentState,
                    byId: mergeWith({}, currentState.byId, conversation, (objValue, srcValue, key) => {
                        if (key == 'messages' && isArray(objValue)) {
                            return objValue.filter((messageId: string | number) => messageId !== messagesDeletePayload.id)
                        }
                    })
                };
            }

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    messagesDeletePayload.conversation
                ]),
                allIds: currentState.allIds.filter((conversationId: string) => conversationId !== messagesDeletePayload.conversation)
            };

        case MESSAGES_BEFORE_ADD :
            const messagesBeforeAddPayload: IMessageDataPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [messagesBeforeAddPayload.conversation]: {
                        id: messagesBeforeAddPayload.conversation,
                        user: messagesBeforeAddPayload.opponentId,
                        _isMessageListFetched: true,
                        _isPending: !currentState.byId[messagesBeforeAddPayload.conversation] 
                            ? true
                            : currentState.byId[messagesBeforeAddPayload.conversation]._isPending,
                        messages: !currentState.byId[messagesBeforeAddPayload.conversation]
                            ? [messagesBeforeAddPayload.id]
                            : [
                                ...currentState.byId[messagesBeforeAddPayload.conversation].messages,
                                messagesBeforeAddPayload.id // add a new message id at the end of array
                            ]
                    }
                }),
                allIds: currentState.byId[messagesBeforeAddPayload.conversation]
                    ? currentState.allIds
                    : [messagesBeforeAddPayload.conversation, ...currentState.allIds]
            };

        case MESSAGES_LOAD_HISTORY :
        case MESSAGES_UPDATE :
            const messagesLoadHistoryPayload: IEntitiesPayload = action.payload;

            if (messagesLoadHistoryPayload.entities.conversations) { 
                return {
                    ...currentState,
                    byId: mergeWith({}, currentState.byId, messagesLoadHistoryPayload.entities.conversations, (objValue, srcValue, key) => {
                        if (key == 'messages' && isArray(objValue)) {
                            return uniq(objValue.concat(srcValue)); // add new ids at the end of array
                        }
                    })
                };
            }

            return currentState;

        case MESSAGES_LOAD :
            const messagesLoadPayload: IEntitiesPayload = action.payload;

            if (messagesLoadPayload.entities.conversations) {
                const processedConversations = {};

                // add the additional flag
                forOwn(messagesLoadPayload.entities.conversations, (value, key) => {
                    processedConversations[key] = {
                        ...value,
                        _isMessageListFetched: true
                    };
                });

                return {
                    ...currentState,
                    byId: mergeWith({}, currentState.byId, processedConversations, (objValue, srcValue, key) => {
                        if (key == 'messages' && isArray(objValue)) {
                            return srcValue; // replace all ids
                        }
                    })
                };
            }

            return currentState;

        case CONVERSATIONS_BEFORE_MARK_UNREAD : // mark conversation as unread
            const beforeConversationMarkUnreadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [beforeConversationMarkUnreadPayload.id]: {
                        _isRead: false
                    }
                })
            };

        case CONVERSATIONS_ERROR_MARK_UNREAD : // mark conversation as read
            const errorConversationMarkUnreadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [errorConversationMarkUnreadPayload.id]: {
                        _isRead: true
                    }
                })
            };

        case CONVERSATIONS_BEFORE_MARK_READ : // mark conversation as read
            const beforeConversationMarkPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [beforeConversationMarkPayload.id]: {
                        _isRead: true
                    }
                })
            };

        case CONVERSATIONS_ERROR_MARK_READ : // mark conversation as unread
            const errorConversationMarkPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [errorConversationMarkPayload.id]: {
                        _isRead: false
                    }
                })
            };
 
        case CONVERSATIONS_BEFORE_DELETE : // mark conversation as hidden
            const beforeConversationDeletePayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [beforeConversationDeletePayload.id]: {
                        _isHidden: true
                    }
                })
            };

        case CONVERSATIONS_AFTER_DELETE :
            const afterConversationDeletePayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    afterConversationDeletePayload.id
                ]),
                allIds: currentState.allIds.filter((conversationId: string) => conversationId !== afterConversationDeletePayload.id)
            };

        case CONVERSATIONS_ERROR_DELETE : // mark conversation as visible
            const errorConversationDeletePayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [errorConversationDeletePayload.id]: {
                        _isHidden: false
                    }
                })
            };

        case CONVERSATIONS_SET :
            const conversationSetPayload: IEntitiesPayload = action.payload;

            const updatable = conversationSetPayload.result.length && conversationSetPayload.entities.conversations
                ? pick(currentState.byId, conversationSetPayload.result)
                : {};

            const newConversations = conversationSetPayload.result.length && conversationSetPayload.entities.conversations
                ? mapValues(conversationSetPayload.entities.conversations, conversation => {
                    // reset the _isRead flag
                    if (updatable[conversation.id] 
                            && conversation.lastMessageTimestamp !== updatable[conversation.id].lastMessageTimestamp 
                            && conversation.isReply !== true) {

                        return {
                            ...omit(conversation, ['avatar']),
                            _isRead: false,
                            _isPending: false
                        };
                    }

                    return {
                        ...omit(conversation, ['avatar']),
                        _isPending: false
                    };
                })
                : {};

            const pendingIds = [];
            const pendingConversations = {};

            // find all pending conversations
            currentState.allIds.forEach((conversationId: string) => {
                const currentConversationData = currentState.byId[conversationId];

                // don't merge pending conversations if they already in a payload
                if (currentConversationData._isPending && !find(newConversations, ['id', conversationId])) {
                    pendingIds.push(currentState.byId[conversationId].id);
                    pendingConversations[currentState.byId[conversationId].id] = merge({}, currentState.byId[conversationId]);
                }
            });

            return {
                isFetched: true,
                byId: conversationSetPayload.result.length && conversationSetPayload.entities.conversations
                    ? merge({}, updatable, newConversations, pendingConversations)
                    : pendingConversations,
                allIds: conversationSetPayload.result.length
                    ? [...pendingIds, ...conversationSetPayload.result]
                    : [...pendingIds]
            };

        case APPLICATION_RESET : // clear all conversations data
        case USERS_LOGOUT :  
            return conversationsInitialState;
    }

    return currentState; 
};

// selectors

export interface IConversationListItem {
    conversation: IConversationData;
    user: IUser;
    avatar: IAvatarData;
}

export const getConversations = (appState: IAppState) => appState.conversations;

/**
 * Is message list fetched
 */
export function isMessageListFetched(appState: IAppState, userId: number): boolean {
    const conversation = getConversationByUserId(appState, userId);

    if (conversation && conversation._isMessageListFetched === true) {
        return true;
    }

    return false;
}

/**
 * Is conversation new
 */
export function isConversationNew(conversationData: IConversationListItem): boolean {
    if (conversationData.conversation._isRead !== undefined) {
        return !conversationData.conversation._isRead;
    }

    return conversationData.conversation.isNew;
}

/**
 * Get conversation with user data
 */
export function getConversationWithUserData(appState: IAppState, conversationId: string): IConversationListItem | undefined {
    const conversation: IConversationData = getConversations(appState).byId[conversationId];

    if (conversation && !conversation._isHidden) {
        const users = getUsers(appState);
        const activeAvatars = getActiveAvatars(appState);

        const user = conversation.user ? users[conversation.user] : undefined;

        return {
            conversation: conversation,
            user: user,
            avatar: user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                ? activeAvatars[user.avatar] 
                : undefined
        };
    }
}

/**
 * Get conversation
 */
export function getConversation(appState: IAppState, conversationId: string): IConversationData | undefined {
    const conversation: IConversationData = getConversations(appState).byId[conversationId];

    if (conversation && !conversation._isHidden) {
        return conversation;
    }
}

/**
 * Get conversation by user id
 */
export function getConversationByUserId(appState: IAppState, userId: number): IConversationData | undefined {
    const user = getUser(appState, userId);

    if (user && user.conversation) {
        const conversation = getConversation(appState, user.conversation);

        if (conversation && !conversation._isHidden) {
            return conversation;
        }
    }
}

/**
 * Is conversation list fetched
 */
export function isConversationListFetched(appState: IAppState): boolean {   
    return getConversations(appState).isFetched;
}

/**
 * Get new conversations count
 */
export function getNewConversationsCount(): Function {
    return createSelector(
        [getConversations],
        (conversations): number => {
            let notReadCount = 0;

            conversations.allIds.forEach((conversationId: string) => {
                if (conversations.byId[conversationId]._isHidden !== true) {
                    if (typeof conversations.byId[conversationId]._isRead !== 'undefined') {
                        if (conversations.byId[conversationId]._isRead === false) {
                            notReadCount++;
                        }
                    }
                    else if (conversations.byId[conversationId].isNew) {
                        notReadCount++;
                    }
                }
            });

            return notReadCount;
    });
}

/**
 * Get message list
 */
export function getConversationList(userNameFilter: string = ''): Function {
    return createSelector(
        [getConversations, getActiveAvatars, getUsers],
        (conversations, activeAvatars, users): Array<IConversationListItem> | undefined => {
            if (conversations.allIds.length) {
                const conversationList = [];

                conversations.allIds.forEach((conversationId: string) => {
                    const conversation = conversations.byId[conversationId];

                    // skip hidden conversations
                    if (conversation._isHidden === true) {
                        return;
                    }

                    const user = conversation.user ? users[conversation.user] : undefined;

                    if (user && (!userNameFilter || user.userName.toLowerCase().startsWith(userNameFilter.toLocaleLowerCase()))) {
                        const avatar = user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                            ? activeAvatars[user.avatar] 
                            : undefined;

                        conversationList.push({
                            conversation: conversation,
                            avatar: avatar,
                            user: user
                        });
                    }
                });

                return conversationList;
            }
    });
}
