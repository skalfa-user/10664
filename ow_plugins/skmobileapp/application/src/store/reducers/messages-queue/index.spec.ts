import { messagesQueue, messagesQueueInitialState } from './';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

import { IAppState } from 'store';

// payloads
import {
    IByIdPayload,
    IMessageDataPayload
} from 'store/payloads';

import {
    MESSAGES_RESEND_MESSAGE,
    MESSAGES_AFTER_ADD,
    MESSAGES_ERROR_ADD,
    CONVERSATIONS_AFTER_DELETE,
    MESSAGES_BEFORE_ADD,
    USERS_LOGOUT, 
    APPLICATION_RESET
} from 'store/actions';

import { IMessage } from 'store/states';

// selectors
import {
    getFirstMessageFromQueue
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';


describe('Messages queue reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(messagesQueue(undefined, '')).toEqual(messagesQueueInitialState);
    });

    it('should handle MESSAGES_AFTER_ADD, MESSAGES_ERROR_ADD and do not mutate a previous state', () => {
        const conversationId: string = '1-1';
        const message: IMessage = {
            conversation: conversationId
        };

        const state: Array<IMessage> = [ // fake state
            message
        ];

        const controlState: Array<IMessage> = cloneDeep(state);

        // after adding messages
        expect(messagesQueue(state, {
            type: MESSAGES_AFTER_ADD,
            payload: {}
        })).toEqual([
        ]);

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // error adding messages
        expect(messagesQueue(state, {
            type: MESSAGES_ERROR_ADD,
            payload: {}
        })).toEqual([
        ]);

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle CONVERSATIONS_AFTER_DELETE and do not mutate a previous state', () => {
        const conversationId1: string = '1-1';
        const message1: IMessage = {
            conversation: conversationId1
        };

        const conversationId2: string = '1-2';
        const message2: IMessage = {
            conversation: conversationId2
        };

        const state: Array<IMessage> = [ // fake state
            message1,
            message2
        ];
        
        const controlState: Array<IMessage> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: conversationId1
        };

        expect(messagesQueue(state, {
            type: CONVERSATIONS_AFTER_DELETE,
            payload: payload
        })).toEqual([
            message2
        ]);

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_BEFORE_ADD, MESSAGES_RESEND_MESSAGE', () => {
        const messageId: number = 1;

        const message: IMessage = {
            id: messageId
        };

        const payload: IMessageDataPayload = {
            ...message
        };
 
        // before add message
        expect(messagesQueue(undefined, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual([
            message
        ]);

        // resend message
        expect(messagesQueue(undefined, {
            type: MESSAGES_RESEND_MESSAGE,
            payload: payload
        })).toEqual([
            message
        ]);
    });

    it('should handle MESSAGES_BEFORE_ADD, MESSAGES_RESEND_MESSAGE and do not mutate a previous state', () => {
        const messageId1: number = 1;
        const messageId2: number = 2;

        const message: IMessage = {
            id: messageId1
        };
    
        const state: Array<IMessage> = [ // fake state
            message
        ];
        
        const controlState: Array<IMessage> = cloneDeep(state);

        const payload: IMessageDataPayload = {
            ...message,
            text: 'test',
            id: messageId2
        };

        // before add message
        expect(messagesQueue(state, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual([{
            id: messageId1
        }, {
            ...message,
            text: 'test',
            id: messageId2
        }]);

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // before add message
        expect(messagesQueue(state, {
            type: MESSAGES_RESEND_MESSAGE,
            payload: payload
        })).toEqual([{
            id: messageId1
        }, {
            ...message,
            text: 'test',
            id: messageId2
        }]);

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(messagesQueue(undefined, {
            type: USERS_LOGOUT
        })).toEqual(messagesQueueInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(messagesQueue(undefined, {
            type: APPLICATION_RESET
        })).toEqual(messagesQueueInitialState)
    });

    it('getFirstMessageFromQueue should return correct value', () => {
        const messageId: number = 1;

        const message: IMessage = {
            id: messageId
        };

        const state: IAppState = { // fake state
            messagesQueue: [
                message
            ]
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstMessageFromQueue(fakeRedux.getState())).toEqual(message);
    });

    it('getFirstMessageFromQueue should return undefined if there are no any messages', () => {
        const state: IAppState = { // fake state
            messagesQueue: []
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstMessageFromQueue(fakeRedux.getState())).toBeUndefined();
    });
});
