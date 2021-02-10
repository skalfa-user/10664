import { compatibleUsers, compatibleUsersInitialState } from './';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

// payloads
import {
    IEntitiesPayload
} from 'store/payloads';


// store
import { IAppState } from 'store';

import { 
    USERS_LOGOUT, 
    APPLICATION_RESET,
    COMPATIBLE_USERS_SET
} from 'store/actions';

import { 
    ICompatibleUserData,
    ICompatibleUsers,
    IUser, 
    IAvatarData, 
    IMatchAction 
} from 'store/states';


// selectors
import {
    isCompatibleUserListFetched,
    getCompatibleUserList
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Compatible users reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(compatibleUsers(undefined, '')).toEqual(compatibleUsersInitialState);
    });

    it('should handle COMPATIBLE_USERS_SET', () => {
        const id: number = 1;
        const userId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                compatibleUsers: {
                    [id]: {
                        id: id,
                        user: userId
                    }
                }
            },
            result: [userId]
        };

        expect(compatibleUsers(undefined, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [id]: {
                    id: id,
                    user: userId
                }
            },
            allIds: [id]
        });
    });

    it('should handle COMPATIBLE_USERS_SET and do not mutate a previous state', () => {
        const id: number = 1;
        const userId: number = 1;

        const userData: ICompatibleUserData = {
            id: id
        };
    
        const state: ICompatibleUsers = { // fake state
            isFetched: true,
            byId: {
                [userId]: userData
            },
            allIds: [userId]
        };

        const controlState: ICompatibleUsers = cloneDeep(state);;

        const payload: IEntitiesPayload = {
            entities: {
                compatibleUsers: {
                    [id]: {
                        ...userData,
                        user: userId
                    }
                }
            },
            result: [id]
        };

        expect(compatibleUsers(state, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [userId]: {
                    ...userData,
                    user: userId
                }                
            },
            allIds: [userId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('COMPATIBLE_USERS_SET should clear lost data', () => {
        const userId1: number = 1;
        const userId2: number = 2;
        const userId3: number = 3;

        const user1Data: ICompatibleUserData = {
            id: userId1
        };

        const user2Data: ICompatibleUserData = { // should be deleted after merge
            id: userId2
        };

        const user3Data: ICompatibleUserData = {
            id: userId3
        };

        const state: ICompatibleUsers = { // fake state
            isFetched: true,
            byId: {
                [userId1]: user1Data,
                [userId2]: user2Data
            },
            allIds: [userId1, userId2]
        };

        const controlState: ICompatibleUsers = cloneDeep(state);
    
        const payload: IEntitiesPayload = {
            entities: {
                compatibleUsers: {
                    [userId3]: user3Data,
                    [userId1]: {
                        ...user1Data
                    }
                }
            },
            result: [userId3, userId1]
        };

        expect(compatibleUsers(state, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [userId1]: {
                    ...user1Data
                },
                [userId3]: user3Data
            },
            allIds: [userId3, userId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });
 
    it('should handle USERS_LOGOUT', () => {
        expect(compatibleUsers(undefined, {
            type: USERS_LOGOUT
        })).toEqual(compatibleUsersInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(compatibleUsers(undefined, {
            type: APPLICATION_RESET
        })).toEqual(compatibleUsersInitialState)
    });

    it('isCompatibleUserListFetched should return a positive boolean value if the list loaded', () => {
        const state: IAppState = { // fake state
            compatibleUsers: {
                isFetched: true
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isCompatibleUserListFetched(fakeRedux.getState())).toBeTruthy();
    });

    it('isCompatibleUserListFetched should return a negative boolean value if the list not loaded', () => {
        const state: IAppState = { // fake state
            compatibleUsers: {
                isFetched: false
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isCompatibleUserListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('getCompatibleUserList should return correct value', () => {
        const compatibleUserId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;

        const user: IUser = {
            id: userId,
            avatar: avatarId,
            matchAction: matchActionId
        };

        const avatar: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const matchAction: IMatchAction = {
            id: matchActionId
        };

        const compatibleUser: ICompatibleUserData = {
            id: compatibleUserId,
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
            matchActions: {
                [matchActionId]: matchAction
            },
            compatibleUsers: {
                byId: {
                    [compatibleUserId]: compatibleUser
                },
                allIds: [compatibleUserId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getCompatibleUserList()(fakeRedux.getState())).toEqual([{
            compatibleUser: compatibleUser,
            avatar: avatar,
            user: user,
            matchAction: matchAction
        }]);
    });

    it('getCompatibleUserList should return an undefined value if user list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            matchActions: {
            },
            compatibleUsers: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getCompatibleUserList()(fakeRedux.getState())).toBeUndefined();
    });
});
