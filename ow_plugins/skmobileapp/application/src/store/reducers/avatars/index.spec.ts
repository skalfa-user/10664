import { avatars, avatarsInitialState } from './';
import { IAvatars, IAvatarData, IUser } from 'store/states';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload,
    IEntityPayload, 
    IAvatarDataPayload, 
    IAvatarAfterUploadPayload 
} from 'store/payloads';

// store
import { IAppState } from 'store';

import {
    CONVERSATIONS_SET,
    HOT_LIST_SET,
    BOOKMARKS_LOAD,
    USERS_LOAD, 
    USERS_LOGOUT, 
    APPLICATION_RESET, 
    GUESTS_SET,
    COMPATIBLE_USERS_SET,
    MATCHED_USERS_SET,
    VIDEO_IM_ADD_NOTIFICATION,
    AVATARS_BEFORE_UPLOAD,
    AVATARS_AFTER_UPLOAD,
    AVATARS_ERROR_UPLOAD,
    AVATARS_BEFORE_DELETE,
    AVATARS_AFTER_DELETE,
    AVATARS_ERROR_DELETE,
    PHOTOS_BEFORE_SET_AS_AVATAR,
    PHOTOS_ERROR_SET_AS_AVATAR,
    PHOTOS_AFTER_SET_AS_AVATAR
} from 'store/actions';

// selectors
import { 
    isAvatarVisible,
    isAvatarPending,
    getUploadingAvatar
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Avatars reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(avatars(undefined, '')).toEqual(avatarsInitialState);
    });

    it('should handle AVATARS_BEFORE_DELETE and do not mutate a previous state', () => {
        const avatarId: number = 1;
        const avatarData: IAvatarData = {
            id: avatarId
        };

        const state: IAvatars = { // fake state
            active: {
                [avatarId]: avatarData
            }
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IEntityPayload = {
            id: avatarId
        };

        expect(avatars(state, {
            type: AVATARS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: {
                    ...avatarData,
                    _isHidden: true
                }
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_AFTER_DELETE and do not mutate a previous state', () => {
        const avatarId: number = 1;
        const avatarData: IAvatarData = {
            id: avatarId
        };

        const state: IAvatars = { // fake state
            active: {
                [avatarId]: avatarData
            }
        };

        const controlState: IAvatars = cloneDeep(state);
    
        const payload: IEntityPayload = {
            id: avatarId
        };

        expect(avatars(state, {
            type: AVATARS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            active: {}
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_ERROR_DELETE and do not mutate a previous state', () => {
        const avatarId: number = 1;
        const avatarData: IAvatarData = {
            id: avatarId,
            _isHidden: true
        };

        const state: IAvatars = { // fake state
            active: {
                [avatarId]: avatarData
            }
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IEntityPayload = {
            id: avatarId
        };

        expect(avatars(state, {
            type: AVATARS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: {
                    ...avatarData,
                    _isHidden: false
                }
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_BEFORE_UPLOAD, PHOTOS_BEFORE_SET_AS_AVATAR', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            url: 'test',
            bigUrl: 'test',
            active: true,
            userId: 1
        };

        const payload: IAvatarDataPayload = {
            ...avatar
        }; 

        // before upload
        expect(avatars(undefined, {
            type: AVATARS_BEFORE_UPLOAD,
            payload: payload
        })).toEqual({
            active: {},
            uploading: [{
                ...avatar,
                _isPending: true
            }]
        });

        // before set as avatar
        expect(avatars(undefined, {
            type: PHOTOS_BEFORE_SET_AS_AVATAR,
            payload: payload
        })).toEqual({
            active: {},
            uploading: [{
                ...avatar,
                _isPending: true
            }]
        });
    });

    it('should handle AVATARS_BEFORE_UPLOAD and do not mutate a previous state', () => {
        const avatarId1: number = 1;
        const avatar1: IAvatarData = {
            id: avatarId1
        };

        const avatarId2: number = 2;
        const avatar2: IAvatarData = {
            id: avatarId2
        };

        const state: IAvatars = { // fake state
            uploading: [
                avatar1
            ]
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IAvatarDataPayload = {
            ...avatar2
        }; 

        expect(avatars(state, {
            type: AVATARS_BEFORE_UPLOAD,
            payload: payload
        })).toEqual({
            uploading: [{
                    ...avatar2,
                    _isPending: true
                },
                avatar1
            ]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_ERROR_UPLOAD, PHOTOS_ERROR_SET_AS_AVATAR and do not mutate a previous state', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId
        };

        const state: IAvatars = { // fake state
            uploading: [
                avatar
            ]
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IByIdPayload = {
            id: avatarId
        };

        // error upload
        expect(avatars(state, {
            type: AVATARS_ERROR_UPLOAD,
            payload: payload
        })).toEqual({
            uploading: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // error set as avatar
        expect(avatars(state, {
            type: PHOTOS_ERROR_SET_AS_AVATAR,
            payload: payload
        })).toEqual({
            uploading: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle AVATARS_AFTER_UPLOAD, PHOTOS_AFTER_SET_AS_AVATAR and do not mutate a previous state', () => {
        const userId: number = 1;
        const avatarId1: string = 'test'; // an uploading avatar id
        const avatarId2: number = 2;

        const avatar: IAvatarData = {
            id: avatarId1
        };

        const state: IAvatars = { // fake state
            active: {
            },
            uploading: [
                avatar
            ]
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IAvatarAfterUploadPayload = {
            id: avatarId1, // the uploading avatar id
            userId: userId,
            avatar: {
                id: avatarId2 // the real avatar id
            }
        }; 

        // after upload
        expect(avatars(state, {
            type: AVATARS_AFTER_UPLOAD,
            payload: payload
        })).toEqual({
            active: {
                [avatarId2]: {
                    id: avatarId2
                }
            },
            uploading: [] // the avatar should be removed from uploading store
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // after set as avatar
        expect(avatars(state, {
            type: PHOTOS_AFTER_SET_AS_AVATAR,
            payload: payload
        })).toEqual({
            active: {
                [avatarId2]: {
                    id: avatarId2
                }
            },
            uploading: [] // the avatar should be removed from uploading store
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOAD', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            url: 'test'
        };

        const payload: IEntitiesPayload = {
            entities: {
                avatar: {
                    [avatarId]: avatar
                }
            }
        };

        expect(avatars(undefined, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });
    });

    it('should handle USERS_LOAD and do not mutate a previous state', () => {
        const avatarId: number = 1;
        
        const avatar: IAvatarData = {
            id: avatarId,
            url: 'test'
        };

        const state: IAvatars = { // fake state
            active: {
                [avatarId]: avatar
            }
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                avatar: {
                    [avatarId]: {
                        ...avatar,
                        active: false
                    }
                }
            }
        }; 

        expect(avatars(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: {
                    ...avatar,
                    active: false
                }
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_SET, COMPATIBLE_USERS_SET, BOOKMARKS_LOAD, HOT_LIST_SET, MATCHED_USERS_SET, CONVERSATIONS_SET, VIDEO_IM_ADD_NOTIFICATION', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            url: 'test'
        };

        const payload: IEntitiesPayload = {
            entities: {
                avatars: {
                    [avatarId]: avatar
                }
            }
        }; 

        // guests
        expect(avatars(undefined, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });

        // compatible users
        expect(avatars(undefined, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });

        // bookmarks
        expect(avatars(undefined, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });

        // hot list
        expect(avatars(undefined, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });

        // matched users
        expect(avatars(undefined, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });

        // conversations
        expect(avatars(undefined, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });

        // video im notifications
        expect(avatars(undefined, {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        })).toEqual({
            active: {
                [avatarId]: avatar
            },
            uploading: []
        });
    });
 
    it('should handle GUESTS_SET, COMPATIBLE_USERS_SET, BOOKMARKS_LOAD, HOT_LIST_SET, MATCHED_USERS_SET, CONVERSATIONS_SET, VIDEO_IM_ADD_NOTIFICATION and do not mutate a previous state', () => {
        const avatarId1: number = 1;
        const avatarId2: number = 2;
        
        const avatar1: IAvatarData = {
            id: avatarId1,
            url: 'test'
        };

        const avatar2: IAvatarData = {
            id: avatarId2,
            url: 'test'
        };

        const state: IAvatars = { // fake state
            active: {
                [avatarId1]: avatar1,
                [avatarId2]: avatar2
            }
        };

        const controlState: IAvatars = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                avatars: {
                    [avatarId1]: {
                        ...avatar1,
                        active: false
                    }
                }
            }
        }; 

        // guests 
        expect(avatars(state, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // compatible users
        expect(avatars(state, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // bookmarks
        expect(avatars(state, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // hot list
        expect(avatars(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // matched user list
        expect(avatars(state, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // conversations
        expect(avatars(state, {
            type: CONVERSATIONS_SET,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // video im notifications
        expect(avatars(state, {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        })).toEqual({
            active: {
                [avatarId1]: {
                    ...avatar1,
                    active: false
                },
                [avatarId2]: avatar2
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(avatars(undefined, {
            type: USERS_LOGOUT
        })).toEqual(avatarsInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(avatars(undefined, {
            type: APPLICATION_RESET
        })).toEqual(avatarsInitialState)
    });

    it('getUploadingAvatar should return correct value', () => {
        const userId: number = 1;
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const state: IAppState = { // fake state
            avatars: {
                active: {
                },
                uploading: [avatar]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUploadingAvatar(userId)(fakeRedux.getState())).toEqual(avatar);
    });

    it('getUploadingAvatar should return a regular avatar if the uploading is absent ', () => {
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
                },
                uploading: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUploadingAvatar(userId)(fakeRedux.getState())).toEqual(avatar);
    });

    it('getUploadingAvatar should return an undefined value if there are no avatars', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
                active: {},
                uploading: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getUploadingAvatar(userId)(fakeRedux.getState())).toBeUndefined();
    });

    it('isAvatarVisible should return a positive boolean value if there is no local variable _isHidden', () => {
        const avatarId: number = 1;
 
        const avatar: IAvatarData = {
            id: avatarId
        };

        expect(isAvatarVisible(avatar)).toBeTruthy();
    });

    it('isAvatarVisible should return a negative boolean value if there is local variable _isHidden', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            _isHidden: true
        };

        expect(isAvatarVisible(avatar)).toBeFalsy();
    });

    it('isAvatarVisible should return a positive boolean value if there is local variable _isHidden with value equals to false', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            _isHidden: false
        };

        expect(isAvatarVisible(avatar)).toBeTruthy();
    });

    it('isAvatarPending should return a negative boolean value if there is no local variable _isPending', () => {
        const avatarId: number = 1;
 
        const avatar: IAvatarData = {
            id: avatarId
        };

        expect(isAvatarPending(avatar)).toBeFalsy();
    });

    it('isAvatarPending should return a positive boolean value if there is local variable _isPending', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            _isPending: true
        };

        expect(isAvatarPending(avatar)).toBeTruthy();
    });

    it('isAvatarPending should return a negative boolean value if there is local variable _isPending with value equals to false', () => {
        const avatarId: number = 1;

        const avatar: IAvatarData = {
            id: avatarId,
            _isPending: false
        };

        expect(isAvatarPending(avatar)).toBeFalsy();
    });
});
