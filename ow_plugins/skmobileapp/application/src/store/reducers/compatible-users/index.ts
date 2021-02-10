import { ICompatibleUsers, ICompatibleUserData, IAvatarData, IUser, IMatchAction } from 'store/states';
import { IAppState } from 'store';
import { getActiveAvatars, getUsers, getMatchActions, isAvatarVisible } from 'store/reducers';
import { createSelector } from 'reselect'
import merge from 'lodash/merge';
import omit from 'lodash/omit';
import pick from 'lodash/pick';
import mapValues from 'lodash/mapValues';

import {
    COMPATIBLE_USERS_SET,
    USERS_LOGOUT, 
    APPLICATION_RESET
} from 'store/actions';

// payloads
import {
    IEntitiesPayload 
} from 'store/payloads';

/**
 * Compatible users initial state
 */
export const compatibleUsersInitialState: ICompatibleUsers = {
    isFetched: false,
    byId: {},
    allIds: []
};

/**
 * Compatible users reducer
 */
export const compatibleUsers = (currentState: ICompatibleUsers, action: any): ICompatibleUsers => {
    // add initial state
    if (!currentState) {
        currentState = compatibleUsersInitialState;
    }

    switch(action.type) {
        case COMPATIBLE_USERS_SET :
            const usersSetPayload: IEntitiesPayload = action.payload;

            const updatable = usersSetPayload.result.length && usersSetPayload.entities.compatibleUsers
                ? pick(currentState.byId, usersSetPayload.result)
                : {};

            const newUsers = usersSetPayload.result.length && usersSetPayload.entities.compatibleUsers
                ? mapValues(usersSetPayload.entities.compatibleUsers, user => omit(user, ['avatar', 'matchAction']))
                : {};

            return {
                isFetched: true,
                byId: usersSetPayload.result.length && usersSetPayload.entities.compatibleUsers
                    ? merge({}, updatable, newUsers)
                    : {},
                allIds: usersSetPayload.result.length 
                    ? usersSetPayload.result
                    : []
            };

        case APPLICATION_RESET : // clear all compatible users data
        case USERS_LOGOUT :  
            return compatibleUsersInitialState;
    }

    return currentState; 
};

// selectors

export interface ICompatibleUserListItem {
    compatibleUser: ICompatibleUserData;
    user: IUser;
    avatar: IAvatarData;
    matchAction: IMatchAction
}

export const getCompatibleUsers = (appState: IAppState) => appState.compatibleUsers;

/**
 * Is compatible user list fetched
 */
export function isCompatibleUserListFetched(appState: IAppState): boolean {   
    return getCompatibleUsers(appState).isFetched;
}

/**
 * Get compatible user list
 */
export function getCompatibleUserList(): Function {
    return createSelector(
        [getCompatibleUsers, getActiveAvatars, getUsers, getMatchActions],
        (compatibleUsers, activeAvatars, users, matchActions): Array<ICompatibleUserListItem> | undefined => {
            if (compatibleUsers.allIds.length) {
                const userList = [];

                compatibleUsers.allIds.forEach((userId: number) => {
                    const compatibleUser = compatibleUsers.byId[userId];

                    const user = compatibleUser.user ? users[compatibleUser.user] : undefined;
                    const matchAction = user && user.matchAction ? matchActions[user.matchAction] : undefined;
                    const avatar = user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                        ? activeAvatars[user.avatar] 
                        : undefined;

                    userList.push({
                        compatibleUser: compatibleUser,
                        avatar: avatar,
                        user: user,
                        matchAction: matchAction
                    });
                });

                return userList;
            }
    });
}
