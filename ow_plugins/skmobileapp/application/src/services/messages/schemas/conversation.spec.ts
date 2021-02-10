import { normalize } from 'normalizr';

// schema
import { conversationListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse } from 'services/user/responses';
import { IConversationResponse } from '../responses';

describe('Conversation schema', () => {
    it('normalizr should correct parse a response', () => {
        const conversationId: string = '1-1';
        const avatarId: number = 1;
        const userId: number = 1;

        const avatar: IAvatarResponse = {
            id: avatarId,
            active: true,
            bigUrl: 'test',
            pendingBigUrl: 'test',
            pendingUrl: 'test',
            url: 'test',
            userId: userId
        }; 

        const user: IUserResponse = {
            id: userId,
            userName: 'test'
        };

        const response: IConversationResponse = {
            id: conversationId,
            isNew: true,
            isReply: true,
            isOpponentRead: true,
            lastMessageTimestamp: 0,
            previewText: 'test',
            avatar: avatar,
            user: user
        };

        // normalize data
        expect(normalize([response], conversationListSchema)).toEqual({
            entities: {
                users: {
                    [userId]: { 
                        ...user,
                        avatar: avatarId,
                        conversation: conversationId
                    },
                },
                avatars: {
                    [avatarId]: avatar
                },
                conversations: {
                    [conversationId]: {
                        ...response,
                        user: userId,
                        avatar: avatarId
                    }
                }
            },
            result: [conversationId]
        });
    });
});
