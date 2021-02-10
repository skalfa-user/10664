import { matchedUsers, matchedUsersInitialState } from './';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import { 
    IMatchedUserData,
    IMatchedUsers,
    IUser, 
    IAvatarData
} from 'store/states';

import {
    MATCHED_USERS_BEFORE_MARK_READ,
    MATCHED_USERS_ERROR_MARK_READ,
    MATCHED_USERS_BEFORE_MARK_NOTIFIED,
    MATCHED_USERS_ERROR_MARK_NOTIFIED,
    MATCHED_USERS_SET,
    USERS_LOGOUT, 
    APPLICATION_RESET
} from 'store/actions';

// selectors
import {
    getMatchedUserData,
    isMatchedUserNew,
    getNewMatchedUsersCount,
    getNotNotifiedMatchedUser,
    isMatchedUserListFetched,
    getMatchedUserList,
    IMatchedUserListItem
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Matched users reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(matchedUsers(undefined, '')).toEqual(matchedUsersInitialState);
    });

    it('should handle MATCHED_USERS_BEFORE_MARK_READ and do not mutate a previous state', () => {
        const matchedUserId: number = 1;
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId
        };

        const state: IMatchedUsers = { // fake state
            isFetched: true,
            byId: {
                [matchedUserId]: matchedUserData
            },
            allIds: [matchedUserId]
        };

        const controlState: IMatchedUsers = cloneDeep(state);

        const payload: IByIdPayload = {
            id: matchedUserId
        };

        expect(matchedUsers(state, {
            type: MATCHED_USERS_BEFORE_MARK_READ,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId]: {
                    ...matchedUserData,
                    _isRead: true
                }
            },
            allIds: [matchedUserId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCHED_USERS_ERROR_MARK_READ and do not mutate a previous state', () => {
        const matchedUserId: number = 1;
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId
        };

        const state: IMatchedUsers = { // fake state
            isFetched: true,
            byId: {
                [matchedUserId]: matchedUserData
            },
            allIds: [matchedUserId]
        };

        const controlState: IMatchedUsers = cloneDeep(state);

        const payload: IByIdPayload = {
            id: matchedUserId
        };

        expect(matchedUsers(state, {
            type: MATCHED_USERS_ERROR_MARK_READ,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId]: {
                    ...matchedUserData,
                    _isRead: false
                }
            },
            allIds: [matchedUserId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCHED_USERS_BEFORE_MARK_NOTIFIED and do not mutate a previous state', () => {
        const matchedUserId: number = 1;
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId
        };

        const state: IMatchedUsers = { // fake state
            isFetched: true,
            byId: {
                [matchedUserId]: matchedUserData
            },
            allIds: [matchedUserId]
        };

        const controlState: IMatchedUsers = cloneDeep(state);

        const payload: IByIdPayload = {
            id: matchedUserId
        };

        expect(matchedUsers(state, {
            type: MATCHED_USERS_BEFORE_MARK_NOTIFIED,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId]: {
                    ...matchedUserData,
                    _isNotified: true
                }
            },
            allIds: [matchedUserId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCHED_USERS_ERROR_MARK_NOTIFIED and do not mutate a previous state', () => {
        const matchedUserId: number = 1;
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId
        };

        const state: IMatchedUsers = { // fake state
            isFetched: true,
            byId: {
                [matchedUserId]: matchedUserData
            },
            allIds: [matchedUserId]
        };

        const controlState: IMatchedUsers = cloneDeep(state);

        const payload: IByIdPayload = {
            id: matchedUserId
        };

        expect(matchedUsers(state, {
            type: MATCHED_USERS_ERROR_MARK_NOTIFIED,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId]: {
                    ...matchedUserData,
                    _isNotified: false
                }
            },
            allIds: [matchedUserId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCHED_USERS_SET', () => {
        const matchedUserId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                matchedUsers: {
                    [matchedUserId]: {
                        id: matchedUserId
                    }
                }
            },
            result: [matchedUserId]
        };

        expect(matchedUsers(undefined, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId]: {
                    id: matchedUserId
                }
            },
            allIds: [matchedUserId]
        });
    });

    it('should handle MATCHED_USERS_SET and do not mutate a previous state', () => {
        const matchedUserId: number = 1;
        const userId: number = 1;

        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isViewed: false
        };

        const state: IMatchedUsers = { // fake state
            isFetched: true,
            byId: {
                [matchedUserId]: matchedUserData
            },
            allIds: [matchedUserId]
        };

        const controlState: IMatchedUsers = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                matchedUsers: {
                    [matchedUserId]: {
                        id: matchedUserId,
                        user: userId
                    }
                }
            },
            result: [matchedUserId]
        };

        expect(matchedUsers(state, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId]: {
                    ...matchedUserData,
                    user: userId
                }                
            },
            allIds: [matchedUserId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('MATCHED_USERS_SET should clear lost data and merge with old properties', () => {
        const matchedUserId1: number = 1;
        const matchedUserId2: number = 2;
        const matchedUserId3: number = 3;

        const matchedUserData1: IMatchedUserData = {
            id: matchedUserId1,
            _isNotified: true // this property should stayed after merging
        };

        const matchedUserData2: IMatchedUserData = {
            id: matchedUserId2
        };

        const matchedUserData3: IMatchedUserData = {
            id: matchedUserId3
        };

        const state: IMatchedUsers = { // fake state
            isFetched: true,
            byId: {
                [matchedUserId1]: matchedUserData1,
                [matchedUserId2]: matchedUserData2
            },
            allIds: [matchedUserId1, matchedUserId2]
        };

        const controlState: IMatchedUsers = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                matchedUsers: {
                    [matchedUserId3]: matchedUserData3,
                    [matchedUserId1]: {
                        id: matchedUserId1,
                        isViewed: true
                    }
                }
            },
            result: [matchedUserId3, matchedUserId1]
        };

        expect(matchedUsers(state, {
            type: MATCHED_USERS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [matchedUserId3]: matchedUserData3,
                [matchedUserId1]: {
                    ...matchedUserData1,
                    isViewed: true
                }
            },
            allIds: [matchedUserId3, matchedUserId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(matchedUsers(undefined, {
            type: USERS_LOGOUT
        })).toEqual(matchedUsersInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(matchedUsers(undefined, {
            type: APPLICATION_RESET
        })).toEqual(matchedUsersInitialState)
    });

    it('getNewMatchedUsersCount should return correct value', () => {
        const matchedUserId: number = 1;
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isNew: true
        };

        const state: IAppState = { // fake state
            matchedUsers: {
                byId: {
                    [matchedUserId]: matchedUserData
                },
                allIds: [matchedUserId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewMatchedUsersCount()(fakeRedux.getState())).toEqual(1);
    });

    it('getNewMatchedUsersCount should not return any value if matched user list marked as read', () => {
        const matchedUserId: number = 1;
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isNew: true,
            _isRead: true
        };

        const state: IAppState = { // fake state
            matchedUsers: {
                byId: {
                    [matchedUserId]: matchedUserData
                },
                allIds: [matchedUserId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewMatchedUsersCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getNewMatchedUsersCount should return 0 if matched user list is empty', () => {
        const state: IAppState = { // fake state
            matchedUsers: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewMatchedUsersCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getMatchedUserData should return a correct value', () => {
        const matchedUserId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;

        const user: IUser = {
            id: userId,
            avatar: avatarId,
            matchUser: matchedUserId
        };

        const avatar: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const matchedUser: IMatchedUserData = {
            id: matchedUserId,
            user: userId
        }

        const state: IAppState = { // fake state
            users: { 
                [userId] : user
            },
            avatars: {
                active: {
                    [avatarId]: avatar
                }
            },
            matchedUsers: {
                byId: {
                    [matchedUserId]: matchedUser
                },
                allIds: [matchedUserId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatchedUserData(userId)(fakeRedux.getState())).toEqual({
            matchedUser: matchedUser,
            user: user,
            avatar: avatar
        });
    });

    it('getMatchedUserData should return an undefined value if matched user data is empty', () => {
        const userId: number = 1;

        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            matchedUsers: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatchedUserData(userId)(fakeRedux.getState())).toBeUndefined();
    });

    it('getMatchedUserData should return an undefined value if there is a lost reference between users and matched users store', () => {
        const matchedUserId: number = 1;
        const userId: number = 1;

        const user: IUser = {
            id: userId,
            matchUser: matchedUserId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user
            },
            avatars: {
                active: {}
            },
            matchedUsers: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatchedUserData(userId)(fakeRedux.getState())).toBeUndefined();
    });
 
    it('getNotNotifiedMatchedUser should return a correct value and skipping notified users', () => {
        const matchedUserId1: number = 1;
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

        const matchedUser1: IMatchedUserData = {
            id: matchedUserId1,
            user: userId1
        }

        const matchedUserId2: number = 2;
        const userId2: number = 2;

        const matchedUser2: IMatchedUserData = {
            id: matchedUserId2,
            user: userId2,
            _isNotified: true
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
            matchedUsers: {
                byId: {
                    [matchedUserId1]: matchedUser1,
                    [matchedUserId2]: matchedUser2
                },
                allIds: [matchedUserId1, matchedUserId2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNotNotifiedMatchedUser()(fakeRedux.getState())).toEqual({
            matchedUser: matchedUser1,
            user: user1,
            avatar: avatar1
        });
    });

    it('getNotNotifiedMatchedUser should return an undefined value if matched user list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            matchedUsers: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNotNotifiedMatchedUser()(fakeRedux.getState())).toBeUndefined();
    });

    it('isMatchedUserListFetched should return a positive boolean value if list loaded', () => {
        const state: IAppState = { // fake state
            matchedUsers: {
                isFetched: true
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isMatchedUserListFetched(fakeRedux.getState())).toBeTruthy();
    });

    it('isMatchedUserListFetched should return a negative boolean value if list not loaded', () => {
        const state: IAppState = { // fake state
            matchedUsers: {
                isFetched: false
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isMatchedUserListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('getMatchedUserList should return correct value', () => {
        const matchedUserId: number = 1;
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

        const matchedUser: IMatchedUserData = {
            id: matchedUserId,
            user: userId
        }

        const state: IAppState = { // fake state
            users: { 
                [userId] : user
            },
            avatars: {
                active: {
                    [avatarId]: avatar
                }
            },
            matchedUsers: {
                byId: {
                    [matchedUserId]: matchedUser
                },
                allIds: [matchedUserId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatchedUserList()(fakeRedux.getState())).toEqual([{
            matchedUser: matchedUser,
            avatar: avatar,
            user: user
        }]);
    });

    it('getMatchedUserList should return correct value and skipping not relevant users and ignore case sensitive in usernames', () => {
        const matchedUserId1: number = 1;
        const userId1: number = 1;
        const userName1 = 'test';

        const user1: IUser = {
            id: userId1,
            userName: userName1
        };

        const matchedUser1: IMatchedUserData = {
            id: matchedUserId1,
            user: userId1
        }

        const matchedUserId2: number = 2;
        const userId2: number = 2;
        const userName2 = 'test2';

        const matchedUser2: IMatchedUserData = {
            id: matchedUserId2,
            user: userId2
        }

        const user2: IUser = {
            id: userId2,
            userName: userName2
        };

        const state: IAppState = { // fake state
            users: { 
                [userId1] : user1,
                [userId2] : user2
            },
            avatars: {
                active: {}
            },
            matchedUsers: {
                byId: {
                    [matchedUserId1]: matchedUser1,
                    [matchedUserId2]: matchedUser2
                },
                allIds: [matchedUserId1, matchedUserId2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatchedUserList(userName2.toLocaleUpperCase())(fakeRedux.getState())).toEqual([{
            matchedUser: matchedUser2,
            user: user2,
            avatar: undefined
        }]);
    });

    it('getMatchedUserList should return an undefined value if matched user list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            matchedUsers: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatchedUserList()(fakeRedux.getState())).toBeUndefined();
    });

    it('isMatchedUserNew should return a positive boolean value if there is no local variable _isRead and the isNew property equals to true', () => {
        const matchedUserId: number = 1;
 
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isNew: true
        };

        const matchedUserItem: IMatchedUserListItem = {
            matchedUser: matchedUserData,
            user: undefined, 
            avatar: undefined
        };

        expect(isMatchedUserNew(matchedUserItem)).toBeTruthy();
    });

    it('isMatchedUserNew should return a negative boolean value if there is no local variable _isRead and the isNew property equals to false', () => {
        const matchedUserId: number = 1;
 
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isNew: false
        };

        const matchedUserItem: IMatchedUserListItem = {
            matchedUser: matchedUserData,
            user: undefined, 
            avatar: undefined
        };

        expect(isMatchedUserNew(matchedUserItem)).toBeFalsy();
    });

    it('isMatchedUserNew should return a negative boolean value if there is a local variable _isRead equals to true, using the isNew property should be avoided', () => {
        const matchedUserId: number = 1;
 
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isNew: true,
            _isRead: true
        };

        const matchedUserItem: IMatchedUserListItem = {
            matchedUser: matchedUserData,
            user: undefined, 
            avatar: undefined
        };

        expect(isMatchedUserNew(matchedUserItem)).toBeFalsy();
    });

    it('isMatchedUserNew should return a positive boolean value if there is a local variable _isRead equals to false, using the isNew property should be avoided', () => {
        const matchedUserId: number = 1;
 
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            isNew: false,
            _isRead: false
        };

        const matchedUserItem: IMatchedUserListItem = {
            matchedUser: matchedUserData,
            user: undefined, 
            avatar: undefined
        };

        expect(isMatchedUserNew(matchedUserItem)).toBeTruthy();
    });
});
