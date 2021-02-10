import { IAppState } from 'store';
import { IMapType } from 'store/types';
import { IPermission } from 'store/states';
import { getUsers } from 'store/reducers';
import { createSelector } from 'reselect'
import merge from 'lodash/merge';

import {
    USERS_LOAD,
    USERS_LOGOUT,
    APPLICATION_RESET,
    PERMISSIONS_UPDATE
} from 'store/actions';

// payloads
import {
    IEntitiesPayload
} from 'store/payloads';

/**
 * Permissions initial state
 */
export const permissionsInitialState: IMapType<IPermission> = {};

/**
 * Permissions reducer
 */
export const permissions = (currentState: IMapType<IPermission>, action: any): IMapType<IPermission> => {
    // add initial state
    if (!currentState) {
        currentState = permissionsInitialState;
    }

    switch(action.type) {
        case USERS_LOAD :
        case PERMISSIONS_UPDATE :
            const usersLoadPayload: IEntitiesPayload = action.payload;

            if (usersLoadPayload.entities.permissions) {
                return merge({}, currentState, usersLoadPayload.entities.permissions);
            }

            return currentState;

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return permissionsInitialState;
    }

    return currentState;
};

// selectors

export const getPermissions = (appState: IAppState) => appState.permissions;

/**
 * Get permission
 */
export function getPermission(name: string, userId: number): Function {
    return createSelector(
        [getPermissions, getUsers],
        (permissions, users): IPermission | undefined => {
            const user = users[userId];

            if (user && user.permissions.length) {
                const permissionId: string = user.permissions.find((id: string) => {
                    return permissions[id] && permissions[id].permission === name;
                });

                if (permissionId) {
                    return permissions[permissionId];
                }
            }
    });
}
