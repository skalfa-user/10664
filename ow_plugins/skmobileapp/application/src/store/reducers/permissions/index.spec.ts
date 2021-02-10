import { permissions, permissionsInitialState } from './';
import { IPermission, IUser } from 'store/states';
import { IMapType } from 'store/types';
import { IAppState } from 'store';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import {
    IEntitiesPayload
} from 'store/payloads';

import { 
    USERS_LOAD, 
    USERS_LOGOUT, 
    APPLICATION_RESET, 
    PERMISSIONS_UPDATE 
} from 'store/actions';

// selectors
import { 
    getPermission
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Permissions reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(permissions(undefined, '')).toEqual(permissionsInitialState);
    });

    it('should handle USERS_LOAD and PERMISSIONS_UPDATE', () => {
        const permissionId: string = '1_1';

        const permission: IPermission = {
            id: permissionId,
            userId: 1,
            permission: 'test',
            isPromoted: true,
            isAllowedAfterTracking: true,
            isAllowed: true,
            creditsCost: 0,
            authorizedByCredits: true
        };

        const payload: IEntitiesPayload = {
            entities: {
                permissions: {
                    [permissionId]: permission
                }
            }
        }; 

        expect(permissions(undefined, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [permissionId]: permission
        });

        expect(permissions(undefined, {
            type: PERMISSIONS_UPDATE,
            payload: payload
        })).toEqual({
            [permissionId]: permission
        });
    });

    it('should handle USERS_LOAD and PERMISSIONS_UPDATE and do not mutate a previous state', () => {
        const permissionId: string = '1_1';

        const permission: IPermission = {
            id: permissionId,
            permission: 'test'
        };

        const state: IMapType<IPermission> = { // fake state
            [permissionId]: permission
        };

        const controlState = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                permissions: {
                    [permissionId]: {
                        ...permission,
                        isAllowed: true
                    }
                }
            }
        }; 

        expect(permissions(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            [permissionId]: {
                ...permission,
                isAllowed: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();

        expect(permissions(state, {
            type: PERMISSIONS_UPDATE,
            payload: payload
        })).toEqual({
            [permissionId]: {
                ...permission,
                isAllowed: true
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(permissions(undefined, {
            type: USERS_LOGOUT
        })).toEqual(permissionsInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(permissions(undefined, {
            type: APPLICATION_RESET
        })).toEqual(permissionsInitialState)
    });

    it('getPermission should return correct value', () => {
        const userId: number = 1;
        const permissionId: string = 'test';
        const permissionName: string = 'test_permission';

        const user: IUser = {
            id: userId,
            permissions: [
                permissionId
            ]
        };

        const permission: IPermission = {
            id: permissionId,
            permission: permissionName
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user  
            },
            permissions: {
                [permissionId]: permission
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getPermission(permissionName, userId)(fakeRedux.getState())).toEqual(permission);
    });
});
