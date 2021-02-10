import { normalize } from 'normalizr';

// schema
import { guestListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse, IMatchResponse } from 'services/user/responses';
import { IGuestResponse } from '../responses';

describe('GuestList schema', () => {
    it('normalizr should correct parse a response', () => {
        const guestId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;
        const userId: number = 1;
        const visitTimestamp: number = 1;

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
            userName: 'test'
        };

        const response: IGuestResponse = {
            id: guestId,
            viewed: false,
            visitTimestamp: visitTimestamp,
            avatar: avatar,
            matchAction: matchAction,
            user: user
        };

        // normalize data
        expect(normalize([response], guestListSchema)).toEqual({
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
                guests: {
                    [guestId]: {
                        ...response,
                        user: userId,
                        avatar: avatarId,
                        matchAction: matchActionId
                    }
                }
            },
            result: [guestId]
        });
    });
});
