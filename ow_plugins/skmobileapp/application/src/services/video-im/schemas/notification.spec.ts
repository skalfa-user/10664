import { normalize } from 'normalizr';

// schema
import { notificationListSchema } from './';

// responses
import { IUserResponse, IAvatarResponse } from 'services/user/responses';
import { INotificationResponse } from '../responses';

describe('VideoIm schema', () => {
    it('normalizr should correct parse a response', () => {
        const notificationId1: number = 1;
        const notificationId2: number = 2;
        const avatarId: number = 1;
        const userId: number = 1;

        const avatar: IAvatarResponse = {
            id: avatarId
        }; 

        const user: IUserResponse = {
            id: userId,
            userName: 'test'
        };

        const notification1: INotificationResponse = {
            id: notificationId1,
            avatar: avatar,
            user: user
        };

        const notification2: INotificationResponse = {
            id: notificationId2,
            avatar: avatar,
            user: user
        };

        // normalize data
        expect(normalize([notification1, notification2], notificationListSchema)).toEqual({
            entities: {
                users: {
                    [userId]: { 
                        ...user,
                        avatar: avatarId
                    },
                },
                avatars: {
                    [avatarId]: avatar
                },
                notifications: {
                    [notificationId1]: {
                        ...notification1,
                        user: userId,
                        avatar: avatarId
                    },
                    [notificationId2]: {
                        ...notification2,
                        user: userId,
                        avatar: avatarId
                    }
                }
            },
            result: [notificationId1, notificationId2]
        });
    });
});
