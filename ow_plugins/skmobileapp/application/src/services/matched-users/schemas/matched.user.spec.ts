import { normalize } from 'normalizr';

// schema
import { matchedUserListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse } from 'services/user/responses';
import { IMatchedUserResponse } from '../responses';

describe('Matched user list schema', () => {
    it('normalizr should correct parse a response', () => {
        const matchedUserId: number = 1;
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

        const response: IMatchedUserResponse = {
            id: matchedUserId,
            isViewed: false,
            isNew: false,
            avatar: avatar,
            user: user
        };

        // normalize data
        expect(normalize([response], matchedUserListSchema)).toEqual({
            entities: {
                users: {
                    [userId]: { 
                        ...user,
                        avatar: avatarId,
                        matchUser: matchedUserId
                    },
                },
                avatars: {
                    [avatarId]: avatar
                },
                matchedUsers: {
                    [matchedUserId]: {
                        ...response,
                        user: userId,
                        avatar: avatarId
                    }
                }
            },
            result: [matchedUserId]
        });
    });
});
