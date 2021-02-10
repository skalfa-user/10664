import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import {
    IEntitiesPayload,
    IMatchActionDataPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';

// store
import { matchActions, matchActionsInitialState } from './';
import { IAppState } from 'store';
import { IMatchAction, IUser } from 'store/states';
import { IMapType } from 'store/types';
import { getMatch } from 'store/reducers';

import {
    USERS_LOAD,
    BOOKMARKS_LOAD,
    GUESTS_SET,
    USERS_LOGOUT, 
    APPLICATION_RESET,
    MATCH_ACTIONS_BEFORE_ADD,
    MATCH_ACTIONS_ERROR_ADD,
    MATCH_ACTIONS_AFTER_ADD,
    MATCH_ACTIONS_AFTER_DELETE,
    COMPATIBLE_USERS_SET
} from 'store/actions';

// fakes
import {
    ReduxFake 
} from 'test/fake';

describe('Match actions reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(matchActions(undefined, '')).toEqual(matchActionsInitialState);
    });

    it('should handle MATCH_ACTIONS_AFTER_DELETE and do not mutate a previous state', () => {
        const matchActionId1: number = 1;
        const matchActionId2: number = 2;
 
        const matchAction1: IMatchAction = {
            id: matchActionId1
        };

        const matchAction2: IMatchAction = {
            id: matchActionId2
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId1]: matchAction1,
            [matchActionId2]: matchAction2
        };

        const controlState: IMapType<IMatchAction> = cloneDeep(state);

        const payload: IMatchActionDataPayload = {
            id: matchActionId1
        };

        expect(matchActions(state, {
            type: MATCH_ACTIONS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            [matchActionId2]: matchAction2
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCH_ACTIONS_AFTER_ADD', () => {
        const fakeId: string = 'test';
        const matchActionId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const payload: IWrappedEntitiesPayload = {
            id: fakeId,
            data: {
                entities: {
                    matchAction: {
                        [matchActionId]: matchAction
                    }
                }
            }
        };

        expect(matchActions(undefined, {
            type: MATCH_ACTIONS_AFTER_ADD,
            payload: payload
        })).toEqual({
            [matchActionId]: matchAction
        });
    });

    it('should handle MATCH_ACTIONS_AFTER_ADD and do not mutate a previous state', () => {
        const matchActionId: number = 1;
        const fakeId: string = 'test';
 
        const matchAction: IMatchAction = {
            id: fakeId,
            type: 'like'
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId]: matchAction
        };

        const controlState: IMapType<IMatchAction> = cloneDeep(state);

        const payload: IWrappedEntitiesPayload = {
            id: fakeId,
            data: {
                entities: {
                    matchAction: {
                        [matchActionId]: {
                            ...matchAction,
                            id: matchActionId
                        }
                    }
                }
            }
        };

        expect(matchActions(state, {
            type: MATCH_ACTIONS_AFTER_ADD,
            payload: payload
        })).toEqual({
            [matchActionId]: {
                ...matchAction,
                id: matchActionId
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCH_ACTIONS_ERROR_ADD', () => {
        const matchActionId: number = 1;
        const userId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId]: matchAction
        };

        const payload: IMatchActionDataPayload = {
            id: matchActionId,
            type: 'like',
            userId: userId
        };

        expect(matchActions(state, {
            type: MATCH_ACTIONS_ERROR_ADD,
            payload: payload
        })).toEqual({
        });
    });

    it('should handle MATCH_ACTIONS_ERROR_ADD and do not mutate a previous state', () => {
        const matchActionId: number = 1;
        const userId: number = 1;
 
        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId]: matchAction
        };

        const controlState: IMapType<IMatchAction> = cloneDeep(state);

        const payload: IMatchActionDataPayload = {
            id: matchActionId,
            type: 'like',
            userId: userId
        };

        expect(matchActions(state, {
            type: MATCH_ACTIONS_ERROR_ADD,
            payload: payload
        })).toEqual({
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle MATCH_ACTIONS_BEFORE_ADD', () => {
        const matchActionId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const payload: IMatchActionDataPayload = {
            id: matchActionId,
            type: 'like'
        };

        expect(matchActions(undefined, {
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [matchActionId]: matchAction
        });
    });

    it('should handle MATCH_ACTIONS_BEFORE_ADD and do not mutate a previous state', () => {
        const matchActionId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId]: matchAction
        };

        const controlState: IMapType<IMatchAction> = cloneDeep(state);

        const payload: IMatchActionDataPayload = {
            id: matchActionId,
            type: 'like',
            isMutual: true
        };

        expect(matchActions(state, {
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: payload
        })).toEqual({
            [matchActionId]: {
                ...matchAction,
                isMutual: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOAD', () => {
        const matchActionId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const payload: IEntitiesPayload = {
            entities: {
                matchAction: {
                    [matchActionId]: matchAction
                }
            }
        };

        expect(matchActions(undefined, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [matchActionId]: matchAction
        });
    }); 

    it('should handle USERS_LOAD and do not mutate a previous state', () => {
        const matchActionId: number = 1;
        const userId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like',
            userId: userId
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId]: matchAction
        };

        const controlState: IMapType<IMatchAction> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                matchAction: {
                    [matchActionId]: {
                        ...matchAction,
                        isMutual: true
                    }
                }
            }
        }; 

        expect(matchActions(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [matchActionId]: {
                ...matchAction,
                isMutual: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_SET and COMPATIBLE_USERS_SET and BOOKMARKS_LOAD', () => {
        const matchActionId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const payload: IEntitiesPayload = {
            entities: {
                matchActions: {
                    [matchActionId]: matchAction
                }
            }
        };

        // guests
        expect(matchActions(undefined, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            [matchActionId]: matchAction
        });

        // compatible users
        expect(matchActions(undefined, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            [matchActionId]: matchAction
        });

        // bookmarks
        expect(matchActions(undefined, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            [matchActionId]: matchAction
        });
    }); 

    it('should handle GUESTS_SET and COMPATIBLE_USERS_SET and BOOKMARKS_LOAD and do not mutate a previous state', () => {
        const matchActionId: number = 1;

        const matchAction: IMatchAction = {
            id: matchActionId,
            type: 'like'
        };

        const state: IMapType<IMatchAction> = { // fake state
            [matchActionId]: matchAction
        };

        const controlState: IMapType<IMatchAction> = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                matchActions: {
                    [matchActionId]: {
                        ...matchAction,
                        isMutual: true
                    }
                }
            }
        }; 

        // guests
        expect(matchActions(state, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            [matchActionId]: {
                ...matchAction,
                isMutual: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // compatible users
        expect(matchActions(state, {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        })).toEqual({
            [matchActionId]: {
                ...matchAction,
                isMutual: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        // bookmarks
        expect(matchActions(state, {
            type: BOOKMARKS_LOAD,
            payload: payload
        })).toEqual({
            [matchActionId]: {
                ...matchAction,
                isMutual: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(matchActions(undefined, {
            type: USERS_LOGOUT
        })).toEqual(matchActionsInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(matchActions(undefined, {
            type: APPLICATION_RESET
        })).toEqual(matchActionsInitialState)
    });

    it('getMatch should return correct value', () => {
        const userId: number = 1;
        const matchId: number = 1;

        const user: IUser = {
            id: userId,
            matchAction: matchId
        };

        const match: IMatchAction = {
            id: matchId,
            userId: userId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            matchActions: {
                [matchId]: match 
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatch(fakeRedux.getState(), userId)).toEqual(match);
    });

    it('getMatch should return undefined for not found matches', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: {},
            matchActions: {}
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getMatch(fakeRedux.getState(), userId)).toBeUndefined();
    });
});
