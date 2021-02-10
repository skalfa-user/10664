import { normalize } from 'normalizr';
import omit from 'lodash/omit';

// schema
import { userSchema } from './';

// responses
import {
    IBookmarkResponse,
    IMatchResponse,
    IViewQuestionResponse,
    IUserResponse, 
    IAvatarResponse,
    IPermissionResponse,
    IPhotoResponse,
    IDistanceResponse
} from '../responses';

describe('User schema', () => {
    it('normalizr should correct parse a response', () => {
        const matchId: number  = 1;
        const userId: number = 1;
        const avatarId: number = 1;
        const bookmarkId: number = 1;
        const photoId: number = 1;
        const permissionId: string = 'test';

        const avatar: IAvatarResponse = {
            id: avatarId,
            active: true,
            bigUrl: 'test',
            pendingBigUrl: 'test',
            pendingUrl: 'test',
            url: 'test',
            userId: userId
        }; 

        const permission: IPermissionResponse = { 
            id: permissionId,
            permission: 'test',
            isAllowedAfterTracking: false,
            authorizedByCredits: true,
            creditsCost: 10,
            isAllowed: false,
            isPromoted: false,
            user: {
                id: userId,
            }
        };

        const photo: IPhotoResponse = {
            id: photoId,
            approved: true,
            bigUrl: 'test',
            url: 'test',
            userId: userId
        };

        const distance: IDistanceResponse = {
            distance: 1,
            unit: 'km'
        };

        const viewQuestions: IViewQuestionResponse = {
            0: {
                order: 1,
                section: 'test', 
                items: [{
                    name: 'test', 
                    label: 'test', 
                    value: 'test'
                }]
            }
        };

        const match: IMatchResponse = {
            id: matchId,
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

        const bookmark: IBookmarkResponse = {
            id: bookmarkId,
            user: userId
        };

        const response: IUserResponse = {
            id: 1,
            aboutMe: 'test',
            age: 45,
            email: 'test@gmail.ru',
            isAdmin: true,
            isOnline: false,
            token: null,
            type: 'test',
            userName: 'test',
            avatar: avatar,
            permissions: [ permission ],
            photos: [ photo ],
            compatibility: 0,
            isBlocked: false,
            distance: distance,
            viewQuestions: viewQuestions,
            matchAction: match,
            bookmark: bookmark
        };

        // normalize data
        expect(normalize(response, userSchema)).toEqual({
            entities: {
                avatar: {
                    [avatarId]: avatar
                },
                photos: {
                    [photoId]: photo
                },
                bookmark: {
                    [bookmarkId]: bookmark
                },
                matchAction: {
                    [matchId]: match
                },
                permissions: {
                    [permissionId]: {
                        ...omit(permission, ['user']),
                        userId: userId
                    }
                },
                user: {
                    [userId]: {
                        ...response,
                        avatar: avatarId,
                        bookmark: bookmarkId,
                        matchAction: matchId,
                        photos: [photoId],
                        permissions: [permissionId]
                    }
                }
            },
            result: userId
        });
    });
});
