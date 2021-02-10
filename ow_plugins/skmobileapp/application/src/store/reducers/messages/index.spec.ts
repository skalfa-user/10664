import { messages, messagesInitialState } from './';
import { IMapType } from 'store/types';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';
import omit from 'lodash/omit';


// payloads
import {
    IMessageDataPayload,
    IEntitiesPayload,
    IByIdPayload,
    IMessagesAfterAddPayload
} from 'store/payloads';


// store
import { IAppState } from 'store';

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
    APPLICATION_RESET, 
    USERS_LOGOUT
} from 'store/actions';

// selectors
import {
    getRealMessagesIds,
    getUnreadMessagesIdList,
    getFirstUnreadMessageId,
    getMessagesImageAttachmentList,
    getDeliveredMessageError,
    isMessageDeliveredWithError,
    isMessageInPending,
    getMessageList
} from 'store/reducers';

import { IMessage, IConversationData, IMessageAttachment } from 'store/states';

// fakes
import {
    createFakeFile,
    ReduxFake
} from 'test/fake';

describe('Messages reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(messages(undefined, '')).toEqual(messagesInitialState);
    });

    it('should handle MESSAGES_BEFORE_MARK_READ and do not mutate a previous state', () => {
        const messageId1: number = 1;

        const message1: IMessage = {
            id: messageId1
        };

        const messageId2: number = 2;

        const message2: IMessage = {
            id: messageId2
        };

        const messageId3: number = 3;

        const message3: IMessage = {
            id: messageId3
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId1]: message1,
            [messageId2]: message2,
            [messageId3]: message3
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: [messageId1, messageId2]
        };

        expect(messages(state, {
            type: MESSAGES_BEFORE_MARK_READ,
            payload: payload
        })).toEqual({
            [messageId1]: {
                ...message1,
                _isRead: true
            },
            [messageId2]: {
                ...message2,
                _isRead: true
            },
            [messageId3]: message3 
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_ERROR_MARK_READ and do not mutate a previous state', () => {
        const messageId1: number = 1;

        const message1: IMessage = {
            id: messageId1,
            _isRead: true
        };

        const messageId2: number = 2;

        const message2: IMessage = {
            id: messageId2,
            _isRead: true
        };

        const messageId3: number = 3;

        const message3: IMessage = {
            id: messageId3
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId1]: message1,
            [messageId2]: message2,
            [messageId3]: message3
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: [messageId1, messageId2]
        };

        expect(messages(state, {
            type: MESSAGES_ERROR_MARK_READ,
            payload: payload
        })).toEqual({
            [messageId1]: {
                ...message1,
                _isRead: false
            },
            [messageId2]: {
                ...message2,
                _isRead: false
            },
            [messageId3]: message3 
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_DELETE_MESSAGE and do not mutate a previous state', () => {
        const messageId1: number = 1;

        const message1: IMessage = {
            id: messageId1
        };

        const messageId2: number = 2;

        const message2: IMessage = {
            id: messageId2
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId1]: message1,
            [messageId2]: message2
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IMessageDataPayload = message1;

        expect(messages(state, {
            type: MESSAGES_DELETE_MESSAGE,
            payload: payload
        })).toEqual({
            [messageId2]: message2
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_RESEND_MESSAGE and do not mutate a previous state', () => {
        const messageId: number = 1;
        const errorMessage: string = 'error';

        const message: IMessage = {
            id: messageId,
            _isError: true,
            _errorDescription: errorMessage
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId]: message
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IMessageDataPayload = message;

        expect(messages(state, {
            type: MESSAGES_RESEND_MESSAGE,
            payload: payload
        })).toEqual({
            [messageId]: {
                ...message,
                _isError: false,
                _errorDescription: '',
                _isPending: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_ERROR_ADD and do not mutate a previous state', () => {
        const messageId: number = 1;
        const errorMessage: string = 'test';

        const message: IMessage = {
            id: messageId
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId]: message
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IMessagesAfterAddPayload = {
            id: messageId,
            message: errorMessage
        };

        expect(messages(state, {
            type: MESSAGES_ERROR_ADD,
            payload: payload
        })).toEqual({
            [messageId]: {
                ...message,
                _isError: true,
                _errorDescription: errorMessage
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_AFTER_ADD and do not mutate a previous state', () => {
        const messageId: number = 1;

        const message: IMessage = {
            id: messageId,
            _isPending: true
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId]: message
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IMessagesAfterAddPayload = {
            id: messageId,
            message: omit(message, [
                '_isPending'
            ]),
        };

        expect(messages(state, {
            type: MESSAGES_AFTER_ADD,
            payload: payload
        })).toEqual({
            [messageId]: {
                ...message,
                _isPending: false,
                file: {}
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('MESSAGES_AFTER_ADD should fully replace the attachments and file properties', () => {
        const messageId1: number = 1;
        const attachmentUrl1: string = 'test1';
        const attachmentUrl2: string = 'test2';
        const attachmentUrl3: string = 'test3';
        const file1: Blob = createFakeFile('test.txt', 100);

        const attachment1: IMessageAttachment = {
            downloadUrl: attachmentUrl1
        };

        const attachment2: IMessageAttachment = {
            downloadUrl: attachmentUrl2
        };

        const attachment3: IMessageAttachment = {
            downloadUrl: attachmentUrl3
        };

        const message1: IMessage = {
            id: messageId1,
            _isPending: true,
            attachments: [
                attachment1
            ],
            file: file1
        };

        const messageId2: number = 2;
        const file2: Blob = createFakeFile('test.txt', 100);
        const attachmentUrl4: string = 'test4';

        const attachment4: IMessageAttachment = {
            downloadUrl: attachmentUrl4
        };

        const message2: IMessage = {
            id: messageId2,
            attachments: [
                attachment4
            ],
            file: file2
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId1]: message1,
            [messageId2]: message2
        };

        const payload: IMessagesAfterAddPayload = {
            id: messageId1,
            message: {
                ...message1,
                attachments: [
                    attachment2,
                    attachment3
                ]
            }
        };

        expect(messages(state, {
            type: MESSAGES_AFTER_ADD,
            payload: payload
        })).toEqual({
            [messageId1]: {
                ...message1,
                _isPending: false,
                attachments: [
                    attachment2,
                    attachment3
                ],
                file: {}
            }, 
            [messageId2]: message2 // the second message should not be changed
        });
    });

    it('should handle MESSAGES_BEFORE_ADD', () => {
        const messageId: number = 1;

        const message: IMessage = {
            id: messageId
        };

        const payload: IMessageDataPayload = {
            ...message
        };

        expect(messages(undefined, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [messageId]: {
                ...message,
                _isPending: true
            }
        });
    });

    it('should handle MESSAGES_BEFORE_ADD and do not mutate a previous state', () => {
        const messageId1: number = 1;
        const messageId2: number = 2;

        const message: IMessage = {
            id: messageId1
        };

        const state: IMapType<IMessage> = { // fake state
            [messageId1]: message
        };

        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IMessageDataPayload = {
            ...message,
            text: 'test',
            id: messageId2
        };

        expect(messages(state, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [messageId1]: message,
            [messageId2]: {
                ...message,
                text: 'test',
                id: messageId2,
                _isPending: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_LOAD, MESSAGES_UPDATE, MESSAGES_LOAD_MESSAGE, MESSAGES_LOAD_HISTORY', () => {
        const messageId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                messages: {
                    [messageId]: {
                        id: messageId
                    }
                }
            },
            result: [messageId]
        };

        // load messages
        expect(messages(undefined, {
            type: MESSAGES_LOAD,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId
            }
        });

        // update message
        expect(messages(undefined, {
            type: MESSAGES_UPDATE,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId
            }
        });

        // load message
        expect(messages(undefined, {
            type: MESSAGES_LOAD_MESSAGE,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId
            }
        });

        // load history messages
        expect(messages(undefined, {
            type: MESSAGES_LOAD_HISTORY,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId
            }
        });
    });

    it('should handle MESSAGES_LOAD, MESSAGES_UPDATE, MESSAGES_LOAD_MESSAGE, MESSAGES_LOAD_HISTORY and do not mutate a previous state', () => {
        const messageId: number = 1;
        const text: string = 'test';

        const message: IMessage = {
            id: messageId
        };
    
        const state: IMapType<IMessage> = { // fake state
            [messageId]: message
        };
        
        const controlState: IMapType<IMessage> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                messages: {
                    [messageId]: {
                        text: text
                    }
                }
            },
            result: [messageId]
        };

        // load messages
        expect(messages(state, {
            type: MESSAGES_LOAD,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId,
                text: text
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // update message
        expect(messages(state, {
            type: MESSAGES_UPDATE,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId,
                text: text
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // load message
        expect(messages(state, {
            type: MESSAGES_LOAD_MESSAGE,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId,
                text: text
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // load history messages
        expect(messages(state, {
            type: MESSAGES_LOAD_HISTORY,
            payload: payload
        })).toEqual({
            [messageId]: {
                id: messageId,
                text: text
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(messages(undefined, {
            type: USERS_LOGOUT
        })).toEqual(messagesInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(messages(undefined, {
            type: APPLICATION_RESET
        })).toEqual(messagesInitialState)
    });

    it('getMessagesImageAttachmentList should return correct list of image urls all other file types should be avoided', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;

        const downloadUrl1: string = 'test';
        const downloadUrl2: string = 'test2';
        const downloadUrl3: string = 'test3';

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId
            ]
        }

        const message: IMessage = {
            id: messageId,
            attachments: [{
                downloadUrl: downloadUrl1,
                type: 'image'
            }, {
                downloadUrl: downloadUrl2,
                type: 'image'
            }, {
                downloadUrl: downloadUrl3,
                type: 'text'
            }]
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId]: message
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessagesImageAttachmentList(conversationId)(fakeRedux.getState())).toEqual([
            downloadUrl1,
            downloadUrl2
        ]);
    });

    it('getMessagesImageAttachmentList should return sorted urls (pending messages urls should be at the end of list)', () => {
        const conversationId: string = '1-1';

        const messageId1: number = 1;
        const messageId2: number = 2;
        const messageId3: number = 3;

        const downloadUrl1: string = 'test';
        const downloadUrl2: string = 'test2';
        const downloadUrl3: string = 'test3'; 

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2,
                messageId3
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            timeStamp: 1,
            _isPending: true,
            attachments: [{
                downloadUrl: downloadUrl1,
                type: 'image'
            }]
        }

        const message2: IMessage = {
            timeStamp: 2,
            id: messageId2,
            attachments: [{
                downloadUrl: downloadUrl2,
                type: 'image'
            }]
        }

        const message3: IMessage = {
            timeStamp: 3,
            id: messageId3,
            attachments: [{
                downloadUrl: downloadUrl3,
                type: 'image'
            }]
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1, 
                [messageId2]: message2,
                [messageId3]: message3
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessagesImageAttachmentList(conversationId)(fakeRedux.getState())).toEqual([
            downloadUrl2,
            downloadUrl3,
            downloadUrl1
        ]);
    });

    it('getMessagesImageAttachmentList should return an empty array if there are no any attachments', () => {
        const conversationId: string = '1-1';

        const conversation: IConversationData = {
            id: conversationId,
            messages: []
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessagesImageAttachmentList(conversationId)(fakeRedux.getState())).toEqual([]);
    });

    it('getMessagesImageAttachmentList should return an empty array if there are only hidden conversations', () => {
        const conversationId: string = '1-1';

        const downloadUrl: string = 'test';
        const messageId: number = 1;

        const conversation: IConversationData = {
            id: conversationId,
            _isHidden: true,
            messages: [
                messageId
            ]
        }

        const message: IMessage = {
            id: messageId,
            attachments: [{
                downloadUrl: downloadUrl,
                type: 'image'
            }]
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId]: message
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessagesImageAttachmentList(conversationId)(fakeRedux.getState())).toEqual([]);
    });

    it('getUnreadMessagesIdList should return a correct value', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: true,
            isAuthor: false
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: false,
            isAuthor: false
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUnreadMessagesIdList(conversationId)(fakeRedux.getState())).toEqual([
            messageId2
        ]);
    });

    it('getUnreadMessagesIdList should return an empty list if message list is empty', () => {
        const conversationId: string = '1-1';
        const state: IAppState = { // fake state
            conversations: {
                byId: {
                },
                allIds: []
            },
            messages: {}
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUnreadMessagesIdList(conversationId)(fakeRedux.getState())).toEqual([]);
    });


    it('getUnreadMessagesIdList should return an empty list if message list contains only read messages', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: true,
            isAuthor: false
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: true,
            isAuthor: false
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUnreadMessagesIdList(conversationId)(fakeRedux.getState())).toEqual([]);
    });

    it('getUnreadMessagesIdList should not return any ids if messages already marked as read', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: false,
            isAuthor: false,
            _isRead: true
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: false,
            isAuthor: false
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUnreadMessagesIdList(conversationId)(fakeRedux.getState())).toEqual([messageId2]);
    });

    it('getRealMessagesIds should return a correct value', () => {
        const conversationId: string = '1-1';
 
        const messageId1: string = 'a';
        const realMessageId1: number = 1;

        const messageId2: string = 'b';
        const realMessageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: realMessageId1
        }

        const message2: IMessage = {
            id: realMessageId2
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getRealMessagesIds(fakeRedux.getState(), [
            messageId1,
            messageId2
        ])).toEqual([
            realMessageId1,
            realMessageId2
        ]);
    });

    it('getRealMessagesIds should return an empty list if message list is empty', () => {
        const messageId1: string = 'a';
        const messageId2: string = 'b';
    
        const state: IAppState = { // fake state
            conversations: {
                byId: {
                },
                allIds: []
            },
            messages: {
            }
        };
    
        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);
    
        expect(getRealMessagesIds(fakeRedux.getState(), [
            messageId1,
            messageId2
        ])).toEqual([]);
    });

    it('getFirstUnreadMessageId should return a correct value', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: true,
            isAuthor: false
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: false,
            isAuthor: false
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstUnreadMessageId(fakeRedux.getState(), conversationId)).toEqual(message2.id);
    });

    it('getFirstUnreadMessageId should return an undefined value if message list is empty', () => {
        const conversationId: string = '1-1';
        const state: IAppState = { // fake state
            conversations: {
                byId: {
                },
                allIds: []
            },
            messages: {}
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstUnreadMessageId(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('getFirstUnreadMessageId should return an undefined value if message list contains only read messages', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: true,
            isAuthor: false
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: true,
            isAuthor: false
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstUnreadMessageId(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('getFirstUnreadMessageId should return an undefined value if message list contains only outcoming messages', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: true,
            isAuthor: true
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: false,
            isAuthor: true
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstUnreadMessageId(fakeRedux.getState(), conversationId)).toBeUndefined();
    });

    it('getFirstUnreadMessageId should avoid returning messages ids marked as read', () => {
        const conversationId: string = '1-1';
        const messageId1: number = 1;
        const messageId2: number = 2;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            isRecipientRead: false,
            isAuthor: false,
            _isRead: true
        }

        const message2: IMessage = {
            id: messageId2,
            isRecipientRead: false,
            isAuthor: false,
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1,
                [messageId2]: message2
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstUnreadMessageId(fakeRedux.getState(), conversationId)).toEqual(messageId2);
    });

    it('getMessageList should return correct value', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId
            ]
        }

        const message: IMessage = {
            id: 1
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId]: message
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessageList(conversationId)(fakeRedux.getState())).toEqual([
            message
        ]);
    });

    it('getMessageList should return sorted messages (pending messages should be at the end of list)', () => {
        const conversationId: string = '1-1';

        const messageId1: number = 1;
        const messageId2: number = 2;
        const messageId3: number = 3;

        const conversation: IConversationData = {
            id: conversationId,
            messages: [
                messageId1,
                messageId2,
                messageId3
            ]
        }

        const message1: IMessage = {
            id: messageId1,
            timeStamp: 1,
            _isPending: true 
        }

        const message2: IMessage = {
            timeStamp: 2,
            id: messageId2
        }

        const message3: IMessage = {
            timeStamp: 3,
            id: messageId3
        }

        const state: IAppState = { // fake state
            conversations: {
                byId: {
                    [conversationId]: conversation
                },
                allIds: [conversationId]
            },
            messages: {
                [messageId1]: message1, 
                [messageId2]: message2,
                [messageId3]: message3
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessageList(conversationId)(fakeRedux.getState())).toEqual([
            message2,
            message3,
            message1
        ]);
    });

    it('getMessageList should return an undefined value if message list empty', () => {
        const conversationId: string = '1-1';
        const state: IAppState = { // fake state
            conversations: {
                byId: {
                },
                allIds: []
            },
            messages: {}
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMessageList(conversationId)(fakeRedux.getState())).toBeUndefined();
    });

    it('isMessageInPending should return a negative boolean value if there is no local variable _isPending', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId
        };

        expect(isMessageInPending(message)).toBeFalsy();
    });

    it('isMessageInPending should return a positive boolean value if there is local variable _isPending equals to true', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId,
            _isPending: true
        };

        expect(isMessageInPending(message)).toBeTruthy();
    });

    it('isMessageInPending should return a negative boolean value if there is local variable _isPending equals to false', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId,
            _isPending: false
        };

        expect(isMessageInPending(message)).toBeFalsy();
    });

    it('isMessageDeliveredWithError should return a negative boolean value if there is no local variable _isError', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId
        };

        expect(isMessageDeliveredWithError(message)).toBeFalsy();
    });

    it('isMessageDeliveredWithError should return a positive boolean value if there is local variable _isError equals to true', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId,
            _isError: true
        };

        expect(isMessageDeliveredWithError(message)).toBeTruthy();
    });

    it('isMessageDeliveredWithError should return a negative boolean value if there is local variable _isError equals to false', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId,
            _isError: false
        };

        expect(isMessageDeliveredWithError(message)).toBeFalsy();
    });

    it('getDeliveredMessageError should return an empty string if there are no local variables _isError and _errorDescription', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId
        };

        expect(getDeliveredMessageError(message)).toEqual('');
    });

    it('getDeliveredMessageError should return an error if there are local variables _isError and _errorDescription', () => {
        const messageId: number = 1;
        const errorMessage: string = 'error';
 
        const message: IMessage = {
            id: messageId,
            _isError: true,
            _errorDescription: errorMessage
        };

        expect(getDeliveredMessageError(message)).toEqual(errorMessage);
    });

    it('getDeliveredMessageError should return an empty string if there are  local variables _isError and empty _errorDescription', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId,
            _isError: true,
            _errorDescription: ''
        };

        expect(getDeliveredMessageError(message)).toEqual('');
    });

    it('getDeliveredMessageError should return an empty string if there is only local variable _isError', () => {
        const messageId: number = 1;
 
        const message: IMessage = {
            id: messageId,
            _isError: true
        };

        expect(getDeliveredMessageError(message)).toEqual('');
    });
});
