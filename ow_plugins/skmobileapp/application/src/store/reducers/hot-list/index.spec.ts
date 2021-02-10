import { hotList, hotListInitialState } from './';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

// payloads
import {
    IEntitiesPayload,
    IEntityPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';


// store
import { IAppState } from 'store';

import { 
    IHotListData,
    IHotList,
    IUser, 
    IAvatarData
} from 'store/states';

import {
    HOT_LIST_AFTER_DELETE,
    HOT_LIST_ERROR_DELETE,
    HOT_LIST_BEFORE_DELETE,
    HOT_LIST_AFTER_ADD,
    HOT_LIST_ERROR_ADD,
    HOT_LIST_BEFORE_ADD,
    HOT_LIST_SET,
    USERS_LOGOUT, 
    APPLICATION_RESET
} from 'store/actions';

// selectors
import {
    isHotListFetched,
    getHostListUsers,
    getHotListIdByUser,
    isUserInHotList
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Hot list reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(hotList(undefined, '')).toEqual(hotListInitialState);
    });

    it('should handle HOT_LIST_AFTER_DELETE and do not mutate a previous state', () => {
        const hotListId: string = 'fake_id';
        const userId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId,
            _isHidden: true
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId]: hotListData
            },
            allIds: [hotListId]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId
        };

        expect(hotList(state, {
            type: HOT_LIST_AFTER_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
            },
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_ERROR_DELETE and do not mutate a previous state', () => {
        const hotListId: string = 'fake_id';
        const userId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId,
            _isHidden: true
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId]: hotListData
            },
            allIds: [hotListId]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId
        };

        expect(hotList(state, {
            type: HOT_LIST_ERROR_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId]: {
                    ...hotListData,
                    _isHidden: false
                }
            },
            allIds: [hotListId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_BEFORE_DELETE and do not mutate a previous state', () => {
        const hotListId: string = 'fake_id';
        const userId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId]: hotListData
            },
            allIds: [hotListId]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId
        };

        expect(hotList(state, {
            type: HOT_LIST_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId]: {
                    ...hotListData,
                    _isHidden: true
                }
            },
            allIds: [hotListId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_AFTER_ADD and do not mutate a previous state', () => { 
        const hotListId1: string = 'fake_id';
        const userId: number = 1;

        const hotListData1: IHotListData = {
            id: hotListId1,
            user: userId
        };

        const hotListId2: number = 1;

        const hotListData2: IHotListData = {
            id: hotListId2,
            user: userId
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1
            },
            allIds: [hotListId1]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IWrappedEntitiesPayload = {
            id: hotListId1,
            data: {
                entities: {
                    hotList: {
                        [hotListId2]: hotListData2
                    }
                },
                result: [hotListId2]
            }
        };
 
        expect(hotList(state, {
            type: HOT_LIST_AFTER_ADD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId2]: hotListData2
            },
            allIds: [
                hotListId2
            ]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_ERROR_ADD and do not mutate a previous state', () => { 
        const hotListId: string = 'fake_id';
        const userId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId]: hotListData
            },
            allIds: [hotListId]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId
        };

        expect(hotList(state, {
            type: HOT_LIST_ERROR_ADD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {},
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_BEFORE_ADD and do not mutate a previous state', () => {
        const hotListId1: number = 1;
        const userId1: number = 1;

        const hotListData1: IHotListData = {
            id: hotListId1,
            user: userId1
        };

        const hotListId2: string = 'fake_id';
        const userId2: number = 2;

        const hotListData2: IHotListData = {
            id: hotListId2,
            user: userId2
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1
            },
            allIds: [hotListId1]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntityPayload = {
            id: hotListId2,
            entityId: userId2
        };

        expect(hotList(state, {
            type: HOT_LIST_BEFORE_ADD,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1,
                [hotListId2]: {
                    ...hotListData2,
                    _isJoinPending: true
                }
            },
            allIds: [hotListId2, hotListId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle HOT_LIST_SET', () => {
        const hotListId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                hotList: {
                    [hotListId]: {
                        id: hotListId
                    }
                }
            },
            result: [hotListId]
        };

        expect(hotList(undefined, { 
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId]: {
                    id: hotListId
                }
            },
            allIds: [hotListId]
        });
    });

    it('should handle HOT_LIST_SET and do not mutate a previous state', () => {
        const hotListId: number = 1;
        const userId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId]: hotListData
            },
            allIds: [hotListId]
        };

        const controlState: IHotList = cloneDeep(state);;

        const payload: IEntitiesPayload = {
            entities: {
                hotList: {
                    [hotListId]: {
                        id: hotListId
                    }
                }
            },
            result: [hotListId]
        };

        expect(hotList(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId]: hotListData                
            },
            allIds: [hotListId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('HOT_LIST_SET should clear lost data and merge with old properties', () => {
        const hotListId1: string = '1';
        const userId1: number = 1;

        const hotListData1: IHotListData = {
            id: hotListId1,
            user: userId1,
            _isHidden: true
        };

        const hotListId2: number = 2;
        const userId2: number = 2;

        const hotListData2: IHotListData = {
            id: hotListId2,
            user: userId2
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1,
                [hotListId2]: hotListData2
            },
            allIds: [hotListId1, hotListId2]
        };

        const controlState: IHotList = cloneDeep(state);;

        const payload: IEntitiesPayload = {
            entities: {
                hotList: {
                    [hotListId1]: {
                        id: hotListId1,
                        user: userId1
                    }
                }
            },
            result: [hotListId1]
        };

        expect(hotList(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1                
            },
            allIds: [hotListId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('HOT_LIST_SET should merge all join pending users with an empty payload', () => {
        const hotListId: number = 1;
        const userId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId,
            _isJoinPending: true
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId]: hotListData
            },
            allIds: [hotListId]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
            },
            result: []
        };

        expect(hotList(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId]: hotListData           
            },
            allIds: [hotListId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('HOT_LIST_SET should merge all join pending users with a not empty payload', () => {
        const hotListId1: number = 1;
        const userId1: number = 1;

        const hotListData1: IHotListData = {
            id: hotListId1,
            user: userId1,
            _isJoinPending: true
        };

        const hotListId2: number = 2;
        const userId2: number = 2;

        const hotListData2: IHotListData = {
            id: hotListId2,
            user: userId2
        };

        const hotListId3: number = 3;
        const userId3: number = 3;

        const hotListData3: IHotListData = {
            id: hotListId3,
            user: userId3
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1
            },
            allIds: [hotListId1]
        };

        const controlState: IHotList = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                hotList: {
                    [hotListId2]: hotListData2,
                    [hotListId3]: hotListData3
                }
            },
            result: [hotListId2, hotListId3]
        };

        expect(hotList(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId2]: hotListData2,
                [hotListId3]: hotListData3,
                [hotListId1]: hotListData1           
            },
            allIds: [hotListId1, hotListId2, hotListId3]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('HOT_LIST_SET should remove all join pending users if users already in a payload', () => {
        const hotListId1: string = 'fake_id';
        const userId: number = 1;

        const hotListData1: IHotListData = {
            id: hotListId1,
            user: userId,
            _isJoinPending: true
        };

        const hotListId2: number = 2;

        const hotListData2: IHotListData = {
            id: hotListId2,
            user: userId
        };

        const state: IHotList = { // fake state
            isFetched: true,
            byId: {
                [hotListId1]: hotListData1
            },
            allIds: [hotListId1]
        };

        const controlState: IHotList = cloneDeep(state);;

        const payload: IEntitiesPayload = {
            entities: {
                hotList: {
                    [hotListId2]: hotListData2
                }
            },
            result: [hotListId2]
        };

        expect(hotList(state, {
            type: HOT_LIST_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [hotListId2]: hotListData2           
            },
            allIds: [hotListId2]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(hotList(undefined, {
            type: USERS_LOGOUT
        })).toEqual(hotListInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(hotList(undefined, {
            type: APPLICATION_RESET
        })).toEqual(hotListInitialState)
    });

    it('isHotListFetched should return a positive boolean value if the list loaded', () => {
        const state: IAppState = { // fake state
            hotList: {
                isFetched: true
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isHotListFetched(fakeRedux.getState())).toBeTruthy();
    });

    it('isHotListFetched should return a negative boolean value if the list not loaded', () => {
        const state: IAppState = { // fake state
            hotList: {
                isFetched: false
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isHotListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('getHotListIdByUser should return an id if user in the list', () => {
        const hotListId: number = 1;
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            hotList: hotListId
        };

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId
        }

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            hotList: {
                byId: {
                    [hotListId]: hotListData
                },
                allIds: [hotListId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getHotListIdByUser(fakeRedux.getState(), userId)).toEqual(hotListId);
    });

    it('getHotListIdByUser should return an undefined value if user not in the list', () => {
        const userId: number = 1;

        const state: IAppState = { // fake state
            users: { 
            },
            hotList: {
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getHotListIdByUser(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('isUserInHotList should return a positive boolean value if an user in the list', () => {
        const hotListId: number = 1;
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            hotList: hotListId
        };

        const hotListData: IHotListData = {
            id: hotListId,
            user: userId
        }

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            hotList: {
                byId: {
                    [hotListId]: hotListData
                },
                allIds: [hotListId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isUserInHotList(fakeRedux.getState(), userId)).toBeTruthy();
    });

    it('isUserInHotList should return a negative boolean value if an user not in the list', () => {
        const userId: number = 1;

        const state: IAppState = { // fake state
            users: { 
            },
            hotList: {
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isUserInHotList(fakeRedux.getState(), userId)).toBeFalsy();
    });

    it('isUserInHotList should return a negative boolean value if there is lost reference  between users and hot list storage', () => {
        const hotListId: number = 1;
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            hotList: hotListId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            hotList: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isUserInHotList(fakeRedux.getState(), userId)).toBeFalsy();
    });

    it('getHostListUsers should return correct value and skipping hidden users', () => {
        const hotListId1: number = 1;
        const userId1: number = 1;
        const avatarId1: number = 1;

        const user1: IUser = {
            id: userId1,
            avatar: avatarId1
        };

        const avatar1: IAvatarData = {
            id: avatarId1,
            userId: userId1
        };

        const hotListData1: IHotListData = {
            id: hotListId1,
            user: userId1
        }

        const hotListId2: number = 2;
        const userId2: number = 2;

        const user2: IUser = {
            id: userId2
        };

        const hotListData2: IHotListData = {
            id: hotListId2,
            user: userId2,
            _isHidden: true
        }

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
            hotList: {
                byId: {
                    [hotListId1]: hotListData1,
                    [hotListId2]: hotListData2
                },
                allIds: [hotListId1, hotListId2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getHostListUsers()(fakeRedux.getState())).toEqual([{
            hotList: hotListData1,
            avatar: avatar1,
            user: user1
        }]);
    });

    it('getHostListUsers should return an undefined value if user list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            hotList: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getHostListUsers()(fakeRedux.getState())).toBeUndefined();
    });
});
