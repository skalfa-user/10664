import { normalize } from 'normalizr';

// schema
import { hotListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse } from 'services/user/responses';
import { IHotListResponse } from '../responses';

describe('HotList schema', () => {
    it('normalizr should correct parse a response', () => {
        const hotListId: number = 1;
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

        const response: IHotListResponse = {
            id: hotListId,
            avatar: avatar,
            user: user
        };

        // normalize data
        expect(normalize([response], hotListSchema)).toEqual({
            entities: {
                users: {
                    [userId]: { 
                        ...user,
                        avatar: avatarId,
                        hotList: hotListId
                    },
                },
                avatars: {
                    [avatarId]: avatar
                },
                hotList: {
                    [hotListId]: {
                        ...response,
                        user: userId,
                        avatar: avatarId
                    }
                }
            },
            result: [hotListId]
        });
    });
});
