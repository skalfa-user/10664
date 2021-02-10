import { normalize } from 'normalizr';

// schema
import { messageListSchema } from './';

// responses
import { IMessageResponse } from 'services/messages/responses';

describe('Message schema', () => {
    it('normalizr should correct parse a response', () => {
        const messageId1: number = 1;
        const conversationId: string = '1_1';

        const message1: IMessageResponse = {
            id: messageId1,
            text: 'test',
            isSystem: false,
            date: 'test',
            dateLabel: 'test',
            isAuthor: false,
            attachments: [],
            isAuthorized: false,
            timeStamp: 1,
            updateStamp: 1,
            isRecipientRead: false,
            opponentId: 1,
            conversation: {
                id: conversationId 
            }
        };

        const messageId2: number = 2;
        const message2: IMessageResponse = {
            id: messageId2,
            text: 'test',
            isSystem: false,
            date: 'test',
            dateLabel: 'test',
            isAuthor: false,
            attachments: [],
            isAuthorized: false,
            timeStamp: 1,
            updateStamp: 1,
            isRecipientRead: false,
            opponentId: 1,
            conversation: {
                id: conversationId 
            }
        };

        const response: Array<IMessageResponse> = [
            message1,
            message2
        ];

        // normalize data
        expect(normalize(response, messageListSchema)).toEqual({
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationId,
                        messages: [messageId1, messageId2]
                    }
                },
                messages: {
                    [messageId1]: {
                        ...message1,
                        conversation: conversationId
                    },
                    [messageId2]: {
                        ...message2,
                        conversation: conversationId
                    }
                }
            },
            result: [messageId1, messageId2]
        });
    });

    it('normalizr should correct parse a response and use temp messages ids instead original', () => {
        const messageId1: number = 1;
        const conversationId: string = '1_1';

        const message1: IMessageResponse = {
            id: messageId1,
            text: 'test',
            isSystem: false,
            date: 'test',
            dateLabel: 'test',
            isAuthor: false,
            attachments: [],
            isAuthorized: false,
            timeStamp: 1,
            isRecipientRead: false,
            opponentId: 1,
            conversation: {
                id: conversationId 
            }
        };

        const messageId2: number = 2;
        const messageTempId2: string = 'test';

        const message2: IMessageResponse = {
            id: messageId2,
            text: 'test',
            isSystem: true,
            date: 'test',
            dateLabel: 'test',
            isAuthor: false,
            attachments: [],
            isAuthorized: false,
            timeStamp: 1,
            updateStamp: 1,
            isRecipientRead: false,
            opponentId: 1,
            conversation: {
                id: conversationId 
            },
            tempId: messageTempId2
        };

        const response: Array<IMessageResponse> = [
            message1,
            message2
        ];

        // normalize data
        expect(normalize(response, messageListSchema)).toEqual({
            entities: {
                conversations: {
                    [conversationId]: {
                        id: conversationId,
                        messages: [messageId1, messageTempId2]
                    }
                },
                messages: {
                    [messageId1]: {
                        ...message1,
                        conversation: conversationId
                    },
                    [messageTempId2]: {
                        ...message2,
                        conversation: conversationId
                    }
                }
            },
            result: [messageId1, messageTempId2]
        });
    });
});
