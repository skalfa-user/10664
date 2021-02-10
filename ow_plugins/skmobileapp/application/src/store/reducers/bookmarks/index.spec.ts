import { bookmarks, bookmarksInitialState } from './';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import {
    IWrappedEntitiesPayload,
    IEntitiesPayload,
    IEntityPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import {
    BOOKMARKS_BEFORE_ADD,
    BOOKMARKS_AFTER_ADD,
    BOOKMARKS_ERROR_ADD,
    USERS_LOAD,
    BOOKMARKS_ERROR_DELETE,
    BOOKMARKS_AFTER_DELETE,
    BOOKMARKS_BEFORE_DELETE,
    BOOKMARKS_LOAD,
    APPLICATION_RESET, 
    USERS_LOGOUT
} from 'store/actions';

import {
    IBookmarks,
    IBookmarkData,
    IUser, 
    IAvatarData, 
    IMatchAction 
} from 'store/states';


// selectors
import {
    getBookmark,
    isBookmarkListFetched,
    getBookmarkList
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Bookmarks reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(bookmarks(undefined, '')).toEqual(bookmarksInitialState);
    });

    it('should handle BOOKMARKS_BEFORE_ADD and do not mutate a previous state', () => {
        const bookmarkId1: number = 1;
        const userId1: number = 1;

        const bookmarkData1: IBookmarkData = {
            id: bookmarkId1,
            user: userId1
        };

        const bookmarkId2: string = 'fake_id';
        const userId2: number = 2;

        const bookmarkData2: IBookmarkData = {
            id: bookmarkId2,
            user: userId2
        };

        const state: IBookmarks = { // fake state
            isFetched: false,
            byId: {
                [bookmarkId1]: bookmarkData1
            },
            allIds: [bookmarkId1]
        };

        const controlState: IBookmarks = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId2,
            entityId: userId2
        };

        expect(bookmarks(state, {
            type: BOOKMARKS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            isFetched: false,
            byId: {
                [bookmarkId1]: bookmarkData1,
                [bookmarkId2]: bookmarkData2
            },
            allIds: [bookmarkId2, bookmarkId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_AFTER_ADD and do not mutate a previous state', () => { 
        const bookmarkId1: string = 'fake_id';
        const userId: number = 1;

        const bookmarkData1: IBookmarkData = {
            id: bookmarkId1,
            user: userId
        };

        const bookmarkId2: number = 1;

        const bookmarkData2: IBookmarkData = {
            id: bookmarkId2,
            user: userId
        };

        const state: IBookmarks = { // fake state
            isFetched: false,
            byId: {
                [bookmarkId1]: bookmarkData1
            },
            allIds: [bookmarkId1]
        };

        const controlState: IBookmarks = cloneDeep(state);

        const payload: IWrappedEntitiesPayload = {
            id: bookmarkId1,
            data: {
                entities: {
                    bookmarks: {
                        [bookmarkId2]: bookmarkData2
                    }
                },
                result: [bookmarkId2]
            }
        };
 
        expect(bookmarks(state, {
            type: BOOKMARKS_AFTER_ADD,
            payload: payload
        })).toEqual({
            isFetched: false,
            byId: {
                [bookmarkId2]: bookmarkData2
            },
            allIds: [
                bookmarkId2
            ]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_ERROR_ADD and do not mutate a previous state', () => { 
        const bookmarkId: string = 'fake_id';
        const userId: number = 1;

        const bookmarkData: IBookmarkData = {
            id: bookmarkId,
            user: userId
        };

        const state: IBookmarks = { // fake state
            isFetched: false,
            byId: {
                [bookmarkId]: bookmarkData
            },
            allIds: [bookmarkId]
        };

        const controlState: IBookmarks = cloneDeep(state);
    
        const payload: IEntityPayload = {
            id: bookmarkId
        };

        expect(bookmarks(state, {
            type: BOOKMARKS_ERROR_ADD,
            payload: payload
        })).toEqual({
            isFetched: false,
            byId: {},
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOAD and do not mutate a previous state', () => {
        const bookmarkId1: number = 1;
        const bookmarkId2: number = 2;
        const userId2: number = 1;

        const bookmarkData1: IBookmarkData = {
            id: bookmarkId1
        };

        const bookmarkData2: IBookmarkData = {
            id: bookmarkId2
        };

        const userData2: IUser = {
            id: userId2,
            bookmark: bookmarkId2
        };

        const state: IBookmarks = { // fake state
            isFetched: true,
            byId: {
                [bookmarkId1]: bookmarkData1
            },
            allIds: [bookmarkId1]
        };

        const controlState: IBookmarks = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                user: {
                    [userId2]: userData2
                },
                bookmark: {
                    [bookmarkId2]: bookmarkData2
                }
            },
            result: [userId2]
        };

        expect(bookmarks(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [bookmarkId1]: bookmarkData1,
                [bookmarkId2]: bookmarkData2
            },
            allIds: [bookmarkId1, bookmarkId2]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('USERS_LOAD should handle only uniq bookmarks ids', () => {
        const bookmarkId: number = 1;
        const userId: number = 1;

        const bookmarkData: IBookmarkData = {
            id: bookmarkId
        };

        const userData: IUser = {
            id: userId,
            bookmark: bookmarkId
        };

        const state: IBookmarks = { // fake state
            isFetched: true,
            byId: {
                [bookmarkId]: bookmarkData
            },
            allIds: [bookmarkId]
        };

        const payload: IEntitiesPayload = {
            entities: {
                user: {
                    [userId]: userData
                },
                bookmark: {
                    [bookmarkId]: bookmarkData
                }
            },
            result: [userId]
        };

        expect(bookmarks(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [bookmarkId]: bookmarkData
            },
            allIds: [bookmarkId]
        });
    });

    it('should handle BOOKMARKS_BEFORE_DELETE and do not mutate a previous state', () => {
        const bookmarkId: number = 1;
        const bookmarkData: IBookmarkData = {
            id: bookmarkId
        };

        const state: IBookmarks = { // fake state
            isFetched: true,
            byId: {
                [bookmarkId]: bookmarkData
            },
            allIds: [bookmarkId]
        };

        const controlState: IBookmarks = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId
        };

        expect(bookmarks(state, {
            type: BOOKMARKS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [bookmarkId]: {
                    ...bookmarkData,
                    _isHidden: true
                }
            },
            allIds: [bookmarkId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_AFTER_DELETE and do not mutate a previous state', () => {
        const bookmarkId: number = 1;
        const bookmarkData: IBookmarkData = {
            id: bookmarkId
        };

        const state: IBookmarks = { // fake state
            isFetched: true,
            byId: {
                [bookmarkId]: bookmarkData
            },
            allIds: [bookmarkId]
        };

        const controlState: IBookmarks = cloneDeep(state);
    
        const payload: IEntityPayload = {
            id: bookmarkId
        };

        expect(bookmarks(state, {
            type: BOOKMARKS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {},
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_ERROR_DELETE and do not mutate a previous state', () => {
        const bookmarkId: number = 1;
        const bookmarkData: IBookmarkData = {
            id: bookmarkId
        };

        const state: IBookmarks = { // fake state
            isFetched: true,
            byId: {
                [bookmarkId]: bookmarkData
            },
            allIds: [bookmarkId]
        };

        const controlState: IBookmarks = cloneDeep(state);

        const payload: IEntityPayload = {
            id: bookmarkId
        };

        expect(bookmarks(state, {
            type: BOOKMARKS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [bookmarkId]: {
                    ...bookmarkData,
                    _isHidden: false
                }
            },
            allIds: [bookmarkId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle BOOKMARKS_LOAD', () => {
        const bookmarkId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                bookmarks: {
                    [bookmarkId]: {
                        id: bookmarkId
                    }
                }
            },
            result: [bookmarkId]
        };

        expect(bookmarks(undefined, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [bookmarkId]: {
                    id: bookmarkId
                }
            },
            allIds: [bookmarkId]
        });
    });

    it('should handle USERS_LOGOUT', () => {
        expect(bookmarks(undefined, {
            type: USERS_LOGOUT
        })).toEqual(bookmarksInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(bookmarks(undefined, {
            type: APPLICATION_RESET
        })).toEqual(bookmarksInitialState)
    });

    it('isBookmarkListFetched should return a positive boolean value if the list loaded', () => {
        const state: IAppState = { // fake state
            bookmarks: {
                isFetched: true
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBookmarkListFetched(fakeRedux.getState())).toBeTruthy();
    });

    it('isBookmarkListFetched should return a negative boolean value if the list not loaded', () => {
        const state: IAppState = { // fake state
            bookmarks: {
                isFetched: false
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBookmarkListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('getBookmarkList should return correct value and skipping hidden bookmarks', () => {
        const bookmarkId1: number = 1;
        const userId1: number = 1;
        const avatarId1: number = 1;
        const matchActionId1: number = 1;

        const user1: IUser = {
            id: userId1,
            avatar: avatarId1,
            matchAction: matchActionId1
        };

        const avatar1: IAvatarData = {
            id: avatarId1,
            userId: userId1
        };

        const matchAction1: IMatchAction = {
            id: matchActionId1
        };

        const bookmark1: IBookmarkData = {
            id: bookmarkId1,
            user: userId1
        }

        const bookmarkId2: number = 2;
        const userId2: number = 2;

        const bookmark2: IBookmarkData = {
            id: bookmarkId2,
            user: userId2,
            _isHidden: true
        }

        const user2: IUser = {
            id: userId2
        };

        const state: IAppState = { // fake state
            users: { 
                [userId1] : user1,
                [userId2] : user2
            },
            avatars: {
                active: {
                    [avatarId1]: avatar1
                }
            },
            matchActions: {
                [matchActionId1]: matchAction1
            },
            bookmarks: {
                byId: {
                    [bookmarkId1]: bookmark1,
                    [bookmarkId2]: bookmark2
                },
                allIds: [bookmarkId1, bookmarkId2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getBookmarkList()(fakeRedux.getState())).toEqual([{
            bookmark: bookmark1,
            avatar: avatar1,
            user: user1,
            matchAction: matchAction1
        }]);
    });

    it('getBookmarkList should return an undefined value if the user list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            matchActions: {
            },
            bookmarks: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getBookmarkList()(fakeRedux.getState())).toBeUndefined();
    });

    it('getBookmark should return correct value', () => {
        const userId: number = 1;
        const bookmarkId: number = 1;

        const user: IUser = {
            id: userId,
            bookmark: bookmarkId
        };

        const bookmark: IBookmarkData = {
            id: bookmarkId,
            user: userId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            bookmarks: {
                isFetched: false,
                byId: {
                    [bookmarkId]: bookmark
                },
                allIds: [bookmarkId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getBookmark(fakeRedux.getState(), userId)).toEqual(bookmark);
    });

    it('getBookmark should return undefined for not found bookmarks', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: {},
            bookmarks: {
                isFetched: false,
                byId: {},
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getBookmark(fakeRedux.getState(), userId)).toBeUndefined();
    });
});
