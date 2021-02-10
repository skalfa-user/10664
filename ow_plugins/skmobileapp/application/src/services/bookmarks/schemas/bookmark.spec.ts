import { normalize } from 'normalizr';

// schema
import { bookmarkListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse, IMatchResponse } from 'services/user/responses';
import { IBookmarkResponse } from '../responses';

describe('Bookmarks schema', () => {
    it('normalizr should correct parse a response', () => {
        const bookmarkId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;
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

        const response: IBookmarkResponse = {
            id: bookmarkId,
            avatar: avatar,
            matchAction: matchAction,
            user: user
        };

        // normalize data
        expect(normalize([response], bookmarkListSchema)).toEqual({
            entities: {
                users: {
                    [userId]: { 
                        ...user,
                        avatar: avatarId,
                        matchAction: matchActionId,
                        bookmark: bookmarkId
                    },
                },
                avatars: {
                    [avatarId]: avatar
                },
                matchActions: {
                    [matchActionId]: matchAction,
                },
                bookmarks: {
                    [bookmarkId]: {
                        ...response,
                        user: userId,
                        avatar: avatarId,
                        matchAction: matchActionId
                    }
                }
            },
            result: [bookmarkId]
        });
    });
});
