import { conversations, conversationsInitialState } from './';
import omit from 'lodash/omit';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload,
    IMessageDataPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import { 
    IConversationData,
    IConversations,
    IUser, 
    IAvatarData,
    IMessage
} from 'store/states';

import {
    MESSAGES_DELETE_MESSAGE,
    MESSAGES_BEFORE_ADD,
    MESSAGES_LOAD_HISTORY,
    MESSAGES_UPDATE,
    MESSAGES_LOAD,
    CONVERSATIONS_BEFORE_MARK_UNREAD,
    CONVERSATIONS_ERROR_MARK_UNREAD,
    CONVERSATIONS_BEFORE_MARK_READ,
    CONVERSATIONS_ERROR_MARK_READ,
    CONVERSATIONS_BEFORE_DELETE,
    CONVERSATIONS_AFTER_DELETE,
    CONVERSATIONS_ERROR_DELETE,
    CONVERSATIONS_SET,
    APPLICATION_RESET, 
    USERS_LOGOUT
} from 'store/actions';

// selectors
import {
    getConversationWithUserData,
    getConversationByUserId,
    isMessageListFetched,
    isConversationNew,
    getNewConversationsCount,
    isConversationListFetched,
    getConversationList,
    getConversation,
    IConversationListItem
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Conversations reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(conversations(undefined, '')).toEqual(conversationsInitialState);
    });

    it('should handle MESSAGES_DELETE_MESSAGE action and do not mutate a previous state', () => {
        const conversationId1: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const message1: IMessage = {
            id: messageId1,
            conversation: conversationId1
        };

        const conversationData1: IConversationData = {
            id: conversationId1,
            messages: [
                messageId1,
                messageId2
            ]
        };

        const conversationId2: string = '1-2';
        const messageId3: number = 3;

        const conversationData2: IConversationData = {
            id: conversationId2,
            messages: [
                messageId3
            ]
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1,
                [conversationId2]: conversationData2
            },
            allIds: [conversationId1, conversationId2]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IMessageDataPayload = message1;

        expect(conversations(state, {
            type: MESSAGES_DELETE_MESSAGE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId1]: {
                    id: conversationId1,
                    messages: [messageId2]
                },
                [conversationId2]: {
                    id: conversationId2,
                    messages: [messageId3]
                },
            },
            allIds: [conversationId1, conversationId2]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_DELETE_MESSAGE action and remove empty conversations do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;

        const message1: IMessage = {
            id: messageId1,
            conversation: conversationId
        };

        const conversationData: IConversationData = {
            id: conversationId,
            messages: [
                messageId1
            ]
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IMessageDataPayload = message1;

        expect(conversations(state, {
            type: MESSAGES_DELETE_MESSAGE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
            },
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_BEFORE_ADD and do not mutate a previous state', () => {
        const conversationId1: string = '1-1';
        const conversationData1: IConversationData = {
            id: conversationId1
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1
            },
            allIds: [conversationId1]
        };

        const controlState: IConversations = cloneDeep(state);

        const messageId2: number = 2;
        const conversationId2: string = '1-2';
        const opponentId2: number = 2;

        const payload: IMessageDataPayload = {
            id: messageId2,
            conversation: conversationId2,
            opponentId: opponentId2
        };

        expect(conversations(state, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1,
                [conversationId2]: {
                    id: conversationId2,
                    user: opponentId2,
                    _isMessageListFetched: true,
                    _isPending: true, 
                    messages: [messageId2]
                },
            },
            allIds: [conversationId2, conversationId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_BEFORE_ADD and the _isPending property should be copied from a previous state', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;
        const opponentId: number = 1;

        const conversationData: IConversationData = {
            id: conversationId,
            user: opponentId,
            _isPending: false,
            messages: [
                messageId
            ]
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const messageId2: number = 2;

        const payload: IMessageDataPayload = {
            id: messageId2,
            conversation: conversationId,
            opponentId: opponentId
        };

        expect(conversations(state, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    user: opponentId,
                    _isMessageListFetched: true,
                    id: conversationId,
                    _isPending: false,
                    messages: [messageId, messageId2]
                }
            },
            allIds: [conversationId] 
        });
    });

    it('should handle MESSAGES_UPDATE, MESSAGES_LOAD_HISTORY and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationId,
                        messages: [messageId]
                    }
                }
            }
        };

        // update messages
        expect(conversations(state, {
            type: MESSAGES_UPDATE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    messages: [messageId]
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // load history messages
        expect(conversations(state, {
            type: MESSAGES_LOAD_HISTORY,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    messages: [messageId]
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('MESSAGES_LOAD_HISTORY, MESSAGES_UPDATE should correctly merge messages ids with existing messages ids and avoid duplicates', () => {
        const conversationId1: string = '1-1';
 
        const messageId2: number = 2;
        const messageId3: number = 3;
        const messageId4: number = 4;

        const conversationData1: IConversationData = {
            id: conversationId1,
            messages: [
                messageId4 // it should be merged with the new ones
            ]
        }; 

        const conversationId2: string = '1-2';
        const messageId5: number = 5;
        const messageId6: number = 6;

        const conversationData2: IConversationData = {
            id: conversationId2,
            messages: [
                messageId5,
                messageId6
            ]
        }; 

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1,
                [conversationId2]: conversationData2
            },
            allIds: [conversationId1, conversationId2]
        };

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId1]: {
                        id: conversationId1,
                        messages: [
                            messageId2,
                            messageId3,
                            messageId4
                        ]
                    }
                }
            }
        };

        // load history
        expect(conversations(state, {
            type: MESSAGES_LOAD_HISTORY,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId1]: {
                    ...conversationData1,
                    messages: [
                        messageId4, 
                        messageId2,
                        messageId3
                    ]
                },
                [conversationId2]: conversationData2
            },
            allIds: [conversationId1, conversationId2]
        });

        // update messages
        expect(conversations(state, {
            type: MESSAGES_UPDATE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId1]: {
                    ...conversationData1,
                    messages: [
                        messageId4, 
                        messageId2,
                        messageId3
                    ]
                },
                [conversationId2]: conversationData2
            },
            allIds: [conversationId1, conversationId2]
        });
    });

    it('should handle MESSAGES_LOAD and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationId,
                        messages: [messageId]
                    }
                }
            }
        };

        expect(conversations(state, {
            type: MESSAGES_LOAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isMessageListFetched: true,
                    messages: [messageId]
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('MESSAGES_LOAD should replace messages ids with a new ones in conversations', () => {
        const conversationId1: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;
        const messageId3: number = 3;

        const conversationData1: IConversationData = {
            id: conversationId1,
            messages: [
                messageId1 // it should be replaced with new ones
            ]
        };

        const conversationId2: string = '1-2';
        const messageId4: number = 4;

        const conversationData2: IConversationData = {
            id: conversationId2,
            messages: [
                messageId4
            ]
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1,
                [conversationId2]: conversationData2
            },
            allIds: [conversationId1, conversationId2]
        };

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId1]: {
                        id: conversationId1,
                        messages: [
                            messageId2, 
                            messageId3
                        ]
                    }
                }
            }
        };

        expect(conversations(state, {
            type: MESSAGES_LOAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId1]: {
                    ...conversationData1,
                    _isMessageListFetched: true,
                    messages: [
                        messageId2, 
                        messageId3
                    ]
                },
                [conversationId2]: conversationData2 
            },
            allIds: [conversationId1, conversationId2]
        });
    });

    it('MESSAGES_LOAD should return the current state of conversations store if payload is empty', () => {
        const conversationId: string = '1-1';

        const conversationData: IConversationData = {
            id: conversationId
        }; 

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const payload: IEntitiesPayload = {
            entities: {
            }
        };

        expect(conversations(state, {
            type: MESSAGES_LOAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData
                }
            },
            allIds: [conversationId]
        });
    });
 
    it('should handle CONVERSATIONS_BEFORE_MARK_READ and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_BEFORE_MARK_READ,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isRead: true
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_ERROR_MARK_READ and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            _isRead: false
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_ERROR_MARK_READ,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isRead: false
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_BEFORE_MARK_UNREAD and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_BEFORE_MARK_UNREAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isRead: false
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_ERROR_MARK_UNREAD and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            _isRead: true
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_ERROR_MARK_UNREAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isRead: true
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_BEFORE_DELETE and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isHidden: true
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_AFTER_DELETE and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {},
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_ERROR_DELETE and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId
        };

        expect(conversations(state, {
            type: CONVERSATIONS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isHidden: false
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_SET', () => {
        const conversationId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationId
                    }
                }
            },
            result: [conversationId]
        };

        expect(conversations(undefined, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    id: conversationId,
                    _isPending: false
                }
            },
            allIds: [conversationId]
        });
    });

    it('should handle CONVERSATIONS_SET and do not mutate a previous state', () => {
        const conversationId: string = '1-1';

        const conversationData: IConversationData = {
            id: conversationId,
            isOpponentRead: false
        };
    
        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);;

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationId
                    }
                }
            },
            result: [conversationId]
        };

        expect(conversations(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isPending: false
                }               
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('CONVERSATIONS_SET should clear lost data and merge with old properties', () => {
        const conversationId1: string = '1-1';
        const conversationId2: string = '2-1';
        const conversationId3: string = '3-1';

        const conversationData1: IConversationData = {
            id: conversationId1,
            _isHidden: true,
            _isRead: false // this property should stayed after merging
        };

        const conversationData2: IConversationData = {
            id: conversationId2
        };

        const conversationData3: IConversationData = {
            id: conversationId3
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1,
                [conversationId2]: conversationData2
            },
            allIds: [conversationId1, conversationId2]
        };

        const controlState: IConversations = cloneDeep(state);
    
        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId3]: conversationData3,
                    [conversationId1]: {
                        id: conversationData1.id,
                        isNew: false
                    }
                }
            },
            result: [conversationId3, conversationId1]
        };

        expect(conversations(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId3]: {
                    ...conversationData3,
                    _isPending: false
                },
                [conversationId1]: {
                    ...conversationData1,
                    isNew: false,
                    _isPending: false
                }
            },
            allIds: [conversationId3, conversationId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('CONVERSATIONS_SET should reset the _isRead flag if an old lastMessageTimestamp different from a new one and the message is not reply', () => {
        const conversationId: string = '1-1';
        const lastMessageTimestamp: number = 1;

        const conversationData: IConversationData = {
            id: conversationId,
            lastMessageTimestamp: lastMessageTimestamp,
            _isRead: true
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);
    
        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationData.id,
                        ...omit(conversationData, [
                            '_isRead'
                        ]),
                        lastMessageTimestamp: lastMessageTimestamp + 1,
                        isReply: false
                    }
                }
            },
            result: [conversationId]
        };

        expect(conversations(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    lastMessageTimestamp: lastMessageTimestamp + 1,
                    _isRead: false,
                    _isPending: false,
                    isReply: false
                }
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('CONVERSATIONS_SET should merge all pending conversations with an empty payload', () => {
        const conversationId: string = '1-1';

        const conversationData: IConversationData = {
            id: conversationId,
            _isPending: true
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
            },
            result: []
        };

        expect(conversations(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: conversationData           
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('CONVERSATIONS_SET should merge all pending conversations with a not empty payload', () => {
        const conversationId1: string = '1-1';
        const conversationData1: IConversationData = {
            id: conversationId1,
            _isPending: true
        };

        const conversationId2: string = '1-2';
        const conversationData2: IConversationData = {
            id: conversationId2
        };

        const conversationId3: string = '1-3';
        const conversationData3: IConversationData = {
            id: conversationId3
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1
            },
            allIds: [conversationId1]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId2]: conversationData2,
                    [conversationId3]: conversationData3
                }
            },
            result: [conversationId2, conversationId3]
        };

        expect(conversations(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId1]: conversationData1,
                [conversationId2]: {
                    ...conversationData2,
                    _isPending: false
                },
                [conversationId3]: {
                    ...conversationData3,
                    _isPending: false
                }
            },
            allIds: [conversationId1, conversationId2, conversationId3]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('CONVERSATIONS_SET should remove all pending conversations if they already reflected in a payload', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            _isPending: true
        };

        const state: IConversations = { // fake state
            isFetched: true,
            byId: {
                [conversationId]: conversationData
            },
            allIds: [conversationId]
        };

        const controlState: IConversations = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationData.id,
                        ...omit(conversationData, [
                            '_isPending'
                        ])
                    },
                }
            },
            result: [conversationId]
        };

        expect(conversations(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [conversationId]: {
                    ...conversationData,
                    _isPending: false
                },           
            },
            allIds: [conversationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(conversations(undefined, {
            type: USERS_LOGOUT
        })).toEqual(conversationsInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(conversations(undefined, {
            type: APPLICATION_RESET
        })).toEqual(conversationsInitialState)
    });

    it('getNewConversationsCount should return correct value', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: true
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewConversationsCount()(fakeRedux.getState())).toEqual(1);
    });

    it('getNewConversationsCount should not return any value if conversation list marked as read', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: true,
            _isRead: true
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewConversationsCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getNewConversationsCount should not return any value if conversation list marked as hidden', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: true,
            _isHidden: true
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewConversationsCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getNewConversationsCount should return 0 if conversation list is empty', () => {
        const state: IAppState = { // fake state
            conversations: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewConversationsCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getNewConversationsCount should use the isNew property while counting new conversations if there are no any _isRead properties otherwise it should use the _isRead property', () => {
        const conversationId1: string = '1-1';
        const conversationData1: IConversationData = {
            id: conversationId1,
            isNew: true
        };

        const conversationId2: string = '1-2';
        const conversationData2: IConversationData = {
            id: conversationId2,
            _isRead: true
        };

        const conversationId3: string = '1-3';
        const conversationData3: IConversationData = {
            id: conversationId3,
            _isRead: false,
            isNew: false
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId1]: conversationData1,
                    [conversationId2]: conversationData2,
                    [conversationId3]: conversationData3
                },
                allIds: [conversationId1, conversationId2, conversationId3]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewConversationsCount()(fakeRedux.getState())).toEqual(2);
    });

    it('getConversationByUserId should return correct value', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IAppState = { // fake state
            users: {
                [userId]: {
                    id: userId,
                    conversation: conversationId
                }
            },
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationByUserId(fakeRedux.getState(), userId)).toEqual(conversationData);
    });

    it('getConversationWithUserData should return correct value', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';
        const avatarId: number = 1;

        const conversationData: IConversationData = {
            id: conversationId,
            user: userId
        };

        const avatar: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const user: IUser = {
            id: userId,
            conversation: conversationId,
            avatar: avatarId
        };

        const state: IAppState = { // fake state
            users: {
                [userId]: user
            },
            avatars: {
                active: {
                    [avatarId]: avatar
                }
            },
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationWithUserData(fakeRedux.getState(), conversationId)).toEqual({
            conversation: conversationData,
            user: user,
            avatar: avatar
        });
    });

    it('getConversationWithUserData should return undefined for absent conversation', () => {
        const conversationId: string = '1-1';

        const state: IAppState = { // fake state
            users: {
            },
            conversations: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationWithUserData(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('getConversationWithUserData should return undefined if a conversation marked as hidden', () => {
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            _isHidden: true
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationWithUserData(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('getConversationByUserId should return undefined if there is a lost reference between users and conversations data store', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';

        const state: IAppState = { // fake state
            users: {
                [userId]: {
                    id: userId,
                    conversation: conversationId
                }
            },
            conversations: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationByUserId(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('getConversationByUserId should return undefined if there is no any conversation and user', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: {},
            conversations: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationByUserId(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('getConversationByUserId should return undefined if a conversation marked as hidden', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            _isHidden: true
        };

        const state: IAppState = { // fake state
            users: {
                [userId]: {
                    id: userId,
                    conversation: conversationId
                }
            },
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationByUserId(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('isMessageListFetched should return a positive boolean value if list loaded', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId,
            _isMessageListFetched: true
        };

        const state: IAppState = { // fake state
            users: {
                [userId]: {
                    id: userId,
                    conversation: conversationId
                }
            },
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isMessageListFetched(fakeRedux.getState(), userId)).toBeTruthy();
    });

    it('isMessageListFetched should return a negative boolean value if list not loaded', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';
        const conversationData: IConversationData = {
            id: conversationId
        };

        const state: IAppState = { // fake state
            users: {
                [userId]: {
                    id: userId,
                    conversation: conversationId
                }
            },
            conversations: {
                byId: {
                    [conversationId]: conversationData
                },
                allIds: [conversationId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isConversationListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('isConversationListFetched should return a positive boolean value if list loaded', () => {
        const state: IAppState = { // fake state
            conversations: {
                isFetched: true
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isConversationListFetched(fakeRedux.getState())).toBeTruthy();
    });

    it('isConversationListFetched should return a negative boolean value if list not loaded', () => {
        const state: IAppState = { // fake state
            conversations: {
                isFetched: false
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isConversationListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('getConversationList should return correct value and skipping hidden conversations', () => {
        const conversationId1: string = '1-1';
        const userId1: number = 1;
        const avatarId1: number = 1;

        const user1: IUser = {
            id: userId1,
            avatar: avatarId1
        };

        const avatar1: IAvatarData = {
            id: avatarId1,
            userId: userId1
        };

        const conversation1: IConversationData = {
            id: conversationId1,
            user: userId1
        }

        const conversationId2: string = '2-1';
        const userId2: number = 2;

        const conversation2: IConversationData = {
            id: conversationId2,
            user: userId2,
            _isHidden: true
        }

        const user2: IUser = {
            id: userId2
        };

        const state: IAppState = { // fake state
            users: { 
                [userId1] : user1,
                [userId2] : user2
            },
            avatars: {
                active: {
                    [avatarId1]: avatar1
                }
            },
            conversations: {
                byId: {
                    [conversationId1]: conversation1,
                    [conversationId2]: conversation2
                },
                allIds: [conversationId1, conversationId2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationList()(fakeRedux.getState())).toEqual([{
            conversation: conversation1,
            avatar: avatar1,
            user: user1
        }]);
    });

    it('getConversationList should return correct value and skipping not relevant users and ignore case sensitive in usernames', () => {
        const conversationId1: string = '1-1';
        const userId1: number = 1;
        const userName1 = 'test';

        const user1: IUser = {
            id: userId1,
            userName: userName1
        };

        const conversation1: IConversationData = {
            id: conversationId1,
            user: userId1
        }

        const conversationId2: string = '2-1';
        const userId2: number = 2;
        const userName2 = 'test2';

        const conversation2: IConversationData = {
            id: conversationId2,
            user: userId2
        }

        const user2: IUser = {
            id: userId2,
            userName: userName2
        };

        const state: IAppState = { // fake state
            users: { 
                [userId1] : user1,
                [userId2] : user2
            },
            conversations: {
                byId: {
                    [conversationId1]: conversation1,
                    [conversationId2]: conversation2
                },
                allIds: [conversationId1, conversationId2]
            },
            avatars: {
                active: {}
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationList(userName2.toUpperCase())(fakeRedux.getState())).toEqual([{
            conversation: conversation2,
            user: user2,
            avatar: undefined
        }]);
    });

    it('getConversationList should return an undefined value if conversation list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            conversations: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversationList()(fakeRedux.getState())).toBeUndefined();
    });

    it('getConversation should return correct value', () => {
        const conversationId: string = '1-1';

        const conversation: IConversationData = {
            id: conversationId
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation 
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversation(fakeRedux.getState(), conversationId)).toEqual(conversation);
    });

    it('getConversation should return undefined for absent conversations', () => {
        const conversationId: string = '1-1';

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversation(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('getConversation should return undefined for hidden conversations', () => {
        const conversationId: string = '1-1';

        const conversation: IConversationData = {
            id: conversationId,
            _isHidden: true
        };

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation 
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConversation(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('isConversationNew should return a positive boolean value if there is no local variable _isRead and the isNew property equals to true', () => {
        const conversationId: string = '1-1';
 
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: true
        };

        const conversationItem: IConversationListItem = {
            conversation: conversationData,
            user: undefined, 
            avatar: undefined
        };

        expect(isConversationNew(conversationItem)).toBeTruthy();
    });

    it('isConversationNew should return a negative boolean value if there is no local variable _isRead and the isNew property equals to false', () => {
        const conversationId: string = '1-1';
 
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: false
        };

        const conversationItem: IConversationListItem = {
            conversation: conversationData,
            user: undefined, 
            avatar: undefined
        };

        expect(isConversationNew(conversationItem)).toBeFalsy();
    });

    it('isConversationNew should return a negative boolean value if there is a local variable _isRead equals to true, using the isNew property should be avoided', () => {
        const conversationId: string = '1-1';
 
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: true,
            _isRead: true
        };

        const conversationItem: IConversationListItem = {
            conversation: conversationData,
            user: undefined, 
            avatar: undefined
        };

        expect(isConversationNew(conversationItem)).toBeFalsy();
    });

    it('isConversationNew should return a positive boolean value if there is a local variable _isRead equals to false, using the isNew property should be avoided', () => {
        const conversationId: string = '1-1';
 
        const conversationData: IConversationData = {
            id: conversationId,
            isNew: false,
            _isRead: false
        };

        const conversationItem: IConversationListItem = {
            conversation: conversationData,
            user: undefined, 
            avatar: undefined
        };

        expect(isConversationNew(conversationItem)).toBeTruthy();
    });
});
