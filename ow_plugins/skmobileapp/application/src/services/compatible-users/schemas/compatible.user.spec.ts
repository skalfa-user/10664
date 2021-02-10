import { normalize } from 'normalizr';

// schema
import { userListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse, IMatchResponse } from 'services/user/responses';
import { ICompatibleUserResponse } from '../responses';

describe('Compatible users schema', () => {
    it('normalizr should correct parse a response', () => {
        const compatibleUserId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;
        const userId: number = 1;
        const compatibility: number = 1;

        const avatar: IAvatarResponse = {
            id: avatarId,
            active: true,
            bigUrl: 'test',
            pendingBigUrl: 'test',
            pendingUrl: 'test',
            url: 'test',
            userId: userId
        }; 

        const matchAction: IMatchResponse = {
            id: matchActionId,
            type: 'like',
            isMutual: false,
            userId: userId,
            createStamp: 1,
            isRead: false,
            isNew: false,
            user: {
                id: userId
            }
        };

        const user: IUserResponse = {
            id: userId,
            userName: 'test',
            compatibility: compatibility
        };

        const response: ICompatibleUserResponse = {
            id: compatibleUserId,
            avatar: avatar,
            matchAction: matchAction,
            user: user
        };

        // normalize data
        expect(normalize([response], userListSchema)).toEqual({
            entities: {
                users: {
                    [userId]: { 
                        ...user,
                        avatar: avatarId,
                        matchAction: matchActionId
                    },
                },
                avatars: {
                    [avatarId]: avatar
                },
                matchActions: {
                    [matchActionId]: matchAction,
                },
                compatibleUsers: {
                    [compatibleUserId]: {
                        ...response,
                        user: userId,
                        avatar: avatarId,
                        matchAction: matchActionId
                    }
                }
            },
            result: [compatibleUserId]
        });
    });
});
