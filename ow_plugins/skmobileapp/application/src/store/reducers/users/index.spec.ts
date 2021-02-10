import { users, usersInitialState } from './';
import { IMapType } from 'store/types';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

// payloads
import {
    IWrappedEntitiesPayload,
    IEntitiesPayload, 
    IEntityPayload, 
    IAvatarAfterUploadPayload,
    IMessageDataPayload,
    IMatchActionDataPayload,
    IPhotosAfterUploadPayload,
    IByIdPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';
import { 
    IUser, 
    IAvatarData, 
    IUserViewQuestions, 
    IBookmarkData, 
    IPhotoData, 
    IMatchAction 
} from 'store/states';

import {
    AVATARS_AFTER_UPLOAD,
    AVATARS_BEFORE_DELETE,
    AVATARS_ERROR_DELETE,
    PHOTOS_AFTER_UPLOAD,
    PHOTOS_AFTER_DELETE,
    PHOTOS_AFTER_SET_AS_AVATAR,
    BOOKMARKS_BEFORE_ADD,
    BOOKMARKS_ERROR_ADD,
    BOOKMARKS_AFTER_ADD,
    MESSAGES_BEFORE_ADD,
    CONVERSATIONS_SET,
    HOT_LIST_ERROR_DELETE,
    HOT_LIST_BEFORE_DELETE,
    HOT_LIST_AFTER_ADD,
    HOT_LIST_ERROR_ADD,
    HOT_LIST_BEFORE_ADD,
    HOT_LIST_SET,
    BOOKMARKS_ERROR_DELETE,
    BOOKMARKS_BEFORE_DELETE,
    BOOKMARKS_LOAD,
    USERS_LOAD, 
    USERS_LOGOUT, 
    APPLICATION_RESET, 
    GUESTS_SET,
    MATCHED_USERS_SET,
    MATCH_ACTIONS_BEFORE_DELETE,
    MATCH_ACTIONS_ERROR_DELETE,
    MATCH_ACTIONS_BEFORE_ADD,
    MATCH_ACTIONS_ERROR_ADD,
    MATCH_ACTIONS_AFTER_ADD,
    PERMISSIONS_UPDATE,
    COMPATIBLE_USERS_SET,
    USERS_BEFORE_BLOCK,
    USERS_ERROR_BLOCK,
    USERS_BEFORE_UNBLOCK,
    USERS_ERROR_UNBLOCK,
    VIDEO_IM_ADD_NOTIFICATION
} from 'store/actions';

// selectors
import { 
    getUserWithFullData,
    getUserWithAvatar,
    getUser,
    isUserBlocked,
    isUserLoaded
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Users reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(users(undefined, '')).toEqual(usersInitialState);
    });

    it('should handle AVATARS_AFTER_UPLOAD, PHOTOS_AFTER_SET_AS_AVATAR and do not mutate a previous state', () => {
        const userId: number = 1;
        const avatarId1: number = 1;
        const avatarId2: number = 2;

        const user: IUser = {
            id: userId,
            avatar: avatarId1
        };

        const avatar: IAvatarData = {
            id: avatarId2,
            userId: userId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IAvatarAfterUploadPayload = {
            id: avatarId1,
            userId: userId,
            avatar: avatar
        };

        // after upload
        expect(users(state, {
            type: AVATARS_AFTER_UPLOAD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                avatar: avatarId2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // after set avatar
        expect(users(state, {
            type: PHOTOS_AFTER_SET_AS_AVATAR,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                avatar: avatarId2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_BEFORE_DELETE and do not mutate a previous state', () => {
        const userId: number = 1;
        const avatarId: number = 1;

        const user: IUser = {
            id: userId,
            avatar: avatarId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: avatarId,
            entityId: userId
        };

        expect(users(state, {
            type: AVATARS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                avatar: null
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_ERROR_DELETE and do not mutate a previous state', () => {
        const userId: number = 1;
        const avatarId: number = 1;

        const user: IUser = {
            id: userId,
            avatar: null
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: avatarId,
            entityId: userId
        };

        expect(users(state, {
            type: AVATARS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                avatar: avatarId // avatar should be restored
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_AFTER_UPLOAD and do not mutate a previous state', () => {
        const userId: number = 1;
        const photoId1: number = 1;
        const photoId2: number = 2;

        const user: IUser = {
            id: userId,
            photos: [photoId1]
        };

        const photo2: IPhotoData = {
            id: photoId2
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IPhotosAfterUploadPayload = {
            id: photoId2,
            userId: userId,
            photo: photo2
        };

        expect(users(state, {
            type: PHOTOS_AFTER_UPLOAD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                photos: [photoId2, photoId1]
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_AFTER_DELETE and do not mutate a previous state', () => {
        const userId: number = 1;
        const photoId1: number = 1;

        const user: IUser = {
            id: userId,
            photos: [photoId1]
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: photoId1,
            entityId: userId
        };

        expect(users(state, {
            type: PHOTOS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                photos: []
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MESSAGES_BEFORE_ADD and do not mutate a previous state', () => {
        const userId: number = 1;
        const conversationId: string = '1-1';

        const user: IUser = {
            id: userId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IMessageDataPayload = {
            opponentId: userId,
            conversation: conversationId
        };

        expect(users(state, {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                conversation: conversationId
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_BEFORE_BLOCK and do not mutate a previous state', () => {
        const userId: number = 1;
        const user: IUser = {
            id: userId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(users(state, {
            type: USERS_BEFORE_BLOCK,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                _isMarkedAsBlocked: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_ERROR_BLOCK and do not mutate a previous state', () => {
        const userId: number = 1;
        const user: IUser = {
            id: userId,
            _isMarkedAsBlocked: false
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(users(state, {
            type: USERS_ERROR_BLOCK,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                _isMarkedAsBlocked: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_BEFORE_UNBLOCK and do not mutate a previous state', () => {
        const userId: number = 1;
        const user: IUser = {
            id: userId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(users(state, {
            type: USERS_BEFORE_UNBLOCK,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                _isMarkedAsBlocked: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_ERROR_UNBLOCK and do not mutate a previous state', () => {
        const userId: number = 1;
        const user: IUser = {
            id: userId,
            _isMarkedAsBlocked: true
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(users(state, {
            type: USERS_ERROR_UNBLOCK,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                _isMarkedAsBlocked: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_ERROR_DELETE', () => {
        const userId: number = 1;
        const bookmarkId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test',
            bookmark: null
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(users(state, {
            type: BOOKMARKS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                bookmark: bookmarkId // bookmark id must be restored
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_BEFORE_DELETE', () => {
        const userId: number = 1;
        const bookmarkId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test',
            bookmark: bookmarkId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(users(state, {
            type: BOOKMARKS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                bookmark: null
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });
 
    it('should handle HOT_LIST_AFTER_ADD, BOOKMARKS_AFTER_ADD', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId
        };

        const payload: IWrappedEntitiesPayload = {
            data: {
                entities: {
                    users: {
                        [userId]: user
                    }
                }
            }
        };

        // hot list
        expect(users(undefined, {
            type: HOT_LIST_AFTER_ADD,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // bookmarks
        expect(users(undefined, {
            type: BOOKMARKS_AFTER_ADD,
            payload: payload
        })).toEqual({
            [userId]: user
        });
    });

    it('should handle HOT_LIST_AFTER_ADD, BOOKMARKS_AFTER_ADD and do not mutate a previous state', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IWrappedEntitiesPayload = {
            data: {
                entities: {
                    users: {
                        [userId]: user
                    }
                }
            }
        };

        // hot list
        expect(users(state, {
            type: HOT_LIST_AFTER_ADD,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // bookmarks
        expect(users(state, {
            type: BOOKMARKS_AFTER_ADD,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCH_ACTIONS_AFTER_ADD', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId
        };

        const payload: IWrappedEntitiesPayload = {
            data: {
                entities: {
                    user: {
                        [userId]: user
                    }
                }
            }
        };

        // match actions
        expect(users(undefined, {
            type: MATCH_ACTIONS_AFTER_ADD,
            payload: payload
        })).toEqual({
            [userId]: user
        });
    });

    it('should handle MATCH_ACTIONS_AFTER_ADD and do not mutate a previous state', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IWrappedEntitiesPayload = {
            data: {
                entities: {
                    user: {
                        [userId]: user
                    }
                }
            }
        };

        expect(users(state, {
            type: MATCH_ACTIONS_AFTER_ADD,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCH_ACTIONS_ERROR_ADD, MATCH_ACTIONS_BEFORE_DELETE', () => {
        const matchId: number = 1;
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            matchAction: matchId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const payload: IMatchActionDataPayload = {
            id: matchId,
            userId: userId
        };

        // error add 
        expect(users(state, {
            type: MATCH_ACTIONS_ERROR_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                matchAction: null
            }
        });

        // before delete 
        expect(users(state, {
            type: MATCH_ACTIONS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                matchAction: null
            }
        });
    });

    it('should handle MATCH_ACTIONS_ERROR_ADD and do not mutate a previous state', () => {
        const userId: number = 1;
        const matchId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test',
            matchAction: matchId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IMatchActionDataPayload = {
            id: matchId,
            userId: userId
        };

        expect(users(state, {
            type: MATCH_ACTIONS_ERROR_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                matchAction: null
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCH_ACTIONS_BEFORE_ADD', () => {
        const matchId: number = 1;
        const userId: number = 1;

        const payload: IMatchActionDataPayload = {
            id: matchId,
            userId: userId
        };

        expect(users(undefined, {
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                id: userId,
                matchAction: matchId
            }
        });
    });

    it('should handle MATCH_ACTIONS_BEFORE_ADD, MATCH_ACTIONS_ERROR_DELETE and do not mutate a previous state', () => {
        const userId: number = 1;
        const matchId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IMatchActionDataPayload = {
            id: matchId,
            userId: userId
        };

        // before add
        expect(users(state, {
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                matchAction: matchId
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // error delete
        expect(users(state, {
            type: MATCH_ACTIONS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                matchAction: matchId
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_BEFORE_DELETE and HOT_LIST_ERROR_ADD', () => {
        const userId: number = 1;
        const hotListId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test',
            hotList: hotListId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId,
            entityId: userId
        };

        // before delete
        expect(users(state, {
            type: HOT_LIST_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                hotList: null
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // error add
        expect(users(state, {
            type: HOT_LIST_ERROR_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                hotList: null
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_ERROR_DELETE', () => {
        const userId: number = 1;
        const hotListId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test',
            hotList: null
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId,
            entityId: userId
        };

        expect(users(state, {
            type: HOT_LIST_ERROR_DELETE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                hotList: hotListId // hot list id should be restored
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_BEFORE_ADD', () => {
        const bookmarkId: string = 'fake_id';
        const userId: number = 1;

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(users(undefined, {
            type: BOOKMARKS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                id: userId,
                bookmark: bookmarkId
            }
        });
    });

    it('should handle BOOKMARKS_BEFORE_ADD and do not mutate a previous state', () => {
        const userId: number = 1;
        const bookmarkId: string = 'fake_id';
 
        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(users(state, {
            type: BOOKMARKS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                bookmark: bookmarkId
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_ERROR_ADD', () => {
        const userId: number = 1;
        const bookmarkId: number = 1;
 
        const user: IUser = {
            id: userId,
            userName: 'test',
            bookmark: bookmarkId
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(users(state, {
            type: BOOKMARKS_ERROR_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                bookmark: null // bookmark should be cleared
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_BEFORE_ADD', () => {
        const hotListId: string = 'fake_id';
        const userId: number = 1;

        const payload: IEntityPayload = {
            id: hotListId,
            entityId: userId
        };

        expect(users(undefined, {
            type: HOT_LIST_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                id: userId,
                hotList: hotListId
            }
        });
    });

    it('should handle HOT_LIST_BEFORE_ADD and do not mutate a previous state', () => {
        const userId: number = 1;
        const hotListId: string = 'fake_id';
 
        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId,
            entityId: userId
        };

        expect(users(state, {
            type: HOT_LIST_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                hotList: hotListId
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOAD and add a local property _isDataLoaded', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const payload: IEntitiesPayload = {
            entities: {
                user: {
                    [userId]: user
                }
            }
        };

        expect(users(undefined, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                _isDataLoaded: true
            }
        });
    });

    it('should handle USERS_LOAD and do not mutate a previous state', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                user: {
                    [userId]: {
                        ...user,
                        isOnline: false
                    }
                }
            }
        }; 

        expect(users(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false,
                _isDataLoaded: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('USERS_LOAD should replace arrays and do not merge them', () => {
        const userId: number = 1;
        const newPermissions: Array<string> = ['3', '4'];
        const newViewQuestions: IUserViewQuestions = [{
            order: 2,
            section: 'test2',
            items: [{
                name: 'test2', 
                label: 'test2', 
                value: 'test2'
            }, {
                name: 'test3', 
                label: 'test4', 
                value: 'test5'
            }]
        }];

        const user: IUser = {
            id: userId,
            userName: 'test',
            permissions: ['1', '2'],
            viewQuestions: [{
                order: 1,
                section: 'test',
                items: [{
                    name: 'test', 
                    label: 'test', 
                    value: 'test'
                }]
            }]
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                user: {
                    [userId]: {
                        ...user,
                        permissions: newPermissions,
                        viewQuestions: newViewQuestions
                    }
                }
            }
        }; 

        expect(users(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                permissions: newPermissions,
                viewQuestions: newViewQuestions,
                _isDataLoaded: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PERMISSIONS_UPDATE', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const payload: IEntitiesPayload = {
            entities: {
                users: {
                    [userId]: user
                }
            }
        };

        expect(users(undefined, {
            type: PERMISSIONS_UPDATE,
            payload: payload
        })).toEqual({
            [userId]: user
        });
    });

    it('should handle PERMISSIONS_UPDATE and do not mutate a previous state', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                users: {
                    [userId]: {
                        ...user,
                        isOnline: false
                    }
                }
            }
        }; 

        expect(users(state, {
            type: PERMISSIONS_UPDATE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('PERMISSIONS_UPDATE should correctly replace all old permissions ids', () => {
        const userId1: number = 1;
        const permissionId1: string = '1_1';
        const permissionId2: string = '1_2';
        const permissionId3: string = '1_3';

        const user1: IUser = {
            id: userId1,
            permissions: [
                permissionId1
            ]
        };

        const userId2: number = 2;
        const permissionId4: string = '1_4';

        const user2: IUser = {
            id: userId2,
            permissions: [
                permissionId4
            ]
        };

        const state: IMapType<IUser> = { // fake state
            [userId1]: user1,
            [userId2]: user2
        };

        const payload: IEntitiesPayload = {
            entities: {
                users: {
                    [userId1]: {
                        ...user1,
                        permissions: [
                            permissionId2,
                            permissionId3
                        ]
                    }
                }
            }
        }; 

        expect(users(state, {
            type: PERMISSIONS_UPDATE,
            payload: payload
        })).toEqual({
            [userId1]: {
                ...user1,
                permissions: [
                    permissionId2,
                    permissionId3
                ]
            },
            [userId2]: user2
        });
    });


    it('should handle PERMISSIONS_UPDATE and do not change anything else', () => {
        const userId: number = 1;
        const photoId: number = 1;
        const permissionId1: string = '1_1';
        const permissionId2: string = '1_2';

         const user: IUser = {
            id: userId,
            userName: 'test',
            photos: [photoId],
            permissions: [
                permissionId1
            ]
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                users: {
                    [userId]: {
                        id: userId,
                        permissions: [
                            permissionId2
                        ]
                    }
                }
            }
        }; 

        expect(users(state, {
            type: PERMISSIONS_UPDATE,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                permissions: [ // permissions should be fully replaced
                    permissionId2
                ]
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_SET, COMPATIBLE_USERS_SET, BOOKMARKS_LOAD, HOT_LIST_SET, MATCHED_USERS_SET, CONVERSATIONS_SET, VIDEO_IM_ADD_NOTIFICATION', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const payload: IEntitiesPayload = {
            entities: {
                users: {
                    [userId]: user
                }
            }
        };

        // guests
        expect(users(undefined, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // compatible users
        expect(users(undefined, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // bookmarks
        expect(users(undefined, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // hot list
        expect(users(undefined, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // matched users
        expect(users(undefined, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // conversations
        expect(users(undefined, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            [userId]: user
        });

        // videoim notifications
        expect(users(undefined, {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        })).toEqual({
            [userId]: user
        });
    });

    it('should handle GUESTS_SET, COMPATIBLE_USERS_SET, BOOKMARKS_LOAD, HOT_LIST_SET, MATCHED_USERS_SET, CONVERSATIONS_SET, VIDEO_IM_ADD_NOTIFICATION and do not mutate a previous state', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            userName: 'test'
        };

        const state: IMapType<IUser> = { // fake state
            [userId]: user
        };

        const controlState: IMapType<IUser> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                users: {
                    [userId]: {
                        ...user,
                        isOnline: false
                    }
                }
            }
        }; 

        // guests
        expect(users(state, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // compatible users
        expect(users(state, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // bookmarks
        expect(users(state, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // hot list
        expect(users(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // matched users
        expect(users(state, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // conversations
        expect(users(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // video im notifications
        expect(users(state, {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        })).toEqual({
            [userId]: {
                ...user,
                isOnline: false
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(users(undefined, {
            type: USERS_LOGOUT
        })).toEqual(usersInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(users(undefined, {
            type: APPLICATION_RESET
        })).toEqual(usersInitialState)
    });

    it('getUserWithAvatar should return correct value', () => {
        const userId: number = 1;
        const avatarId: number = 1;

        const user: IUser = {
            id: userId,
            avatar: avatarId
        };

        const avatar: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            avatars: {
                active: {
                    [avatarId]: avatar 
                }
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUserWithAvatar(fakeRedux.getState(), userId)).toEqual({
            user: user,
            avatar: avatar
        });
    });

    it('getUserWithFullData should return correct value', () => {
        const userId: number = 1;
        const avatarId: number = 1;
        const bookmarkId: number = 1;
        const photoId: number = 1;
        const matchId: number = 1;

        const user: IUser = {
            id: userId,
            avatar: avatarId,
            bookmark: bookmarkId,
            photos: [photoId],
            matchAction: matchId
        };

        const avatar: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const bookmark: IBookmarkData = {
            id: bookmarkId
        };

        const photo: IPhotoData = {
            id: photoId
        };

        const match: IMatchAction = {
            id: matchId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            avatars: {
                active: {
                    [avatarId]: avatar
                }
            },
            bookmarks: {
                byId: {
                    [bookmarkId]: bookmark
                }
            },
            photos: {
                active: {
                    [photoId]: photo
                }
            },
            matchActions: {
                [matchId]: match
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUserWithFullData(userId)(fakeRedux.getState())).toEqual({
            user: user,
            avatar: avatar,
            bookmark: bookmark,
            photos: [
                photo
            ],
            matchAction: match
        });
    });

    it('getUserWithFullData should return undefined for absent users', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
                active: {}
            },
            photos: {
                active: {}
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUserWithFullData(userId)(fakeRedux.getState())).toBeUndefined();
    });

    it('getUserWithAvatar should return undefined for absent users', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: { 
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUserWithAvatar(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('getUser should return correct value', () => {
        const userId: number = 1;

        const user: IUser = {
            id: userId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUser(fakeRedux.getState(), userId)).toEqual(user);
    });

    it('getUser should return undefined for absent users', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: { 
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUser(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('isUserLoaded should return a positive boolean value if there is a local variable _isDataLoaded', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            _isDataLoaded: true
        };

        expect(isUserLoaded(user)).toBeTruthy();
    });

    it('isUserLoaded should return a negative boolean value if there is no any user data', () => {
        expect(isUserLoaded(undefined)).toBeFalsy();
    });

    it('isUserLoaded should return a negative boolean value if there is a user with the absent _isDataLoaded property', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId
        };

        expect(isUserLoaded(user)).toBeFalsy();
    });

    it('isUserBlocked should return a positive boolean value if there is no local variable _isMarkedAsBlocked and the isBlocked property equals to true', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            isBlocked: true
        };

        expect(isUserBlocked(user)).toBeTruthy();
    });

    it('isUserBlocked should return a negative boolean value if there is no local variable _isMarkedAsBlocked and the isBlocked property equals to false', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            isBlocked: false
        };

        expect(isUserBlocked(user)).toBeFalsy();
    });

    it('isUserBlocked should return a positive boolean value if there is a local variable _isMarkedAsBlocked equals to true, using the isBlocked property should be avoided', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            isBlocked: false,
            _isMarkedAsBlocked: true
        };
 
        expect(isUserBlocked(user)).toBeTruthy();
    });

    it('isUserBlocked should return a negative boolean value if there is a local variable _isMarkedAsBlocked equals to false, using the isBlocked property should be avoided', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId,
            isBlocked: true,
            _isMarkedAsBlocked: false
        };

        expect(isUserBlocked(user)).toBeFalsy();
    });

    it('isUserBlocked should return a negative boolean value if there are not the local variable _isMarkedAsBlocked and isBlocked property', () => {
        const userId: number = 1;
 
        const user: IUser = {
            id: userId
        };

        expect(isUserBlocked(user)).toBeFalsy();
    });
});
