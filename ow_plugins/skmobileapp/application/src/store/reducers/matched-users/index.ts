import { IMatchedUsers, IMatchedUserData, IUser, IAvatarData } from 'store/states';
import { IAppState } from 'store';
import { getActiveAvatars, getUsers, isAvatarVisible } from 'store/reducers';
import { createSelector } from 'reselect'
import merge from 'lodash/merge';
import omit from 'lodash/omit';
import pick from 'lodash/pick';
import mapValues from 'lodash/mapValues';

import {
    MATCHED_USERS_BEFORE_MARK_READ,
    MATCHED_USERS_ERROR_MARK_READ,
    MATCHED_USERS_BEFORE_MARK_NOTIFIED,
    MATCHED_USERS_ERROR_MARK_NOTIFIED,
    MATCHED_USERS_SET,
    USERS_LOGOUT, 
    APPLICATION_RESET, 
} from 'store/actions';

// payloads
import {
    IByIdPayload,
    IEntitiesPayload
} from 'store/payloads';

/**
 * Matched users initial state
 */
export const matchedUsersInitialState: IMatchedUsers = {
    isFetched: false,
    byId: {},
    allIds: []
};

/**
 * Matched users reducer
 */
export const matchedUsers = (currentState: IMatchedUsers, action: any): IMatchedUsers => {
    // add initial state
    if (!currentState) {
        currentState = matchedUsersInitialState;
    }

    switch(action.type) {
        case MATCHED_USERS_BEFORE_MARK_READ : // mark matched user as read
            const matchedUsersBeforeMarkReadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [matchedUsersBeforeMarkReadPayload.id]: {
                        _isRead: true
                    }
                })
            };

        case MATCHED_USERS_ERROR_MARK_READ : // mark matched user as unread
            const matchedUsersErrorMarkReadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [matchedUsersErrorMarkReadPayload.id]: {
                        _isRead: false
                    }
                })
            };

        case MATCHED_USERS_BEFORE_MARK_NOTIFIED : // mark matched user as notified
            const matchedUsersBeforeMarkNotifiedPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [matchedUsersBeforeMarkNotifiedPayload.id]: {
                        _isNotified: true
                    }
                })
            };

        case MATCHED_USERS_ERROR_MARK_NOTIFIED : // mark matched user as not notified
            const matchedUsersErrorMarkNotifiedPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [matchedUsersErrorMarkNotifiedPayload.id]: {
                        _isNotified: false
                    }
                })
            };

        case MATCHED_USERS_SET :
            const matchedUsersSetPayload: IEntitiesPayload = action.payload;

            const updatable = matchedUsersSetPayload.result.length && matchedUsersSetPayload.entities.matchedUsers
                ? pick(currentState.byId, matchedUsersSetPayload.result)
                : {};

            const newMatchedUsers = matchedUsersSetPayload.result.length && matchedUsersSetPayload.entities.matchedUsers
                ? mapValues(matchedUsersSetPayload.entities.matchedUsers, matchedUser => omit(matchedUser, ['avatar']))
                : {};

            return {
                isFetched: true,
                byId: matchedUsersSetPayload.result.length && matchedUsersSetPayload.entities.matchedUsers
                    ? merge({}, updatable, newMatchedUsers)
                    : {},
                allIds: matchedUsersSetPayload.result.length 
                    ? matchedUsersSetPayload.result
                    : []
            };

        case APPLICATION_RESET : // clear all data
        case USERS_LOGOUT :  
            return matchedUsersInitialState;
    }

    return currentState; 
};

// selectors

export interface IMatchedUserListItem {
    matchedUser: IMatchedUserData;
    user: IUser;
    avatar: IAvatarData;
}

export const getMatchedUsers = (appState: IAppState) => appState.matchedUsers;

/**
 * Get matched user data
 */
export function getMatchedUserData(userId: number): Function {
    return createSelector(
        [getUsers, getActiveAvatars, getMatchedUsers],
        (users, activeAvatars, matchedUsers): IMatchedUserListItem | undefined => {
            if (users[userId] && users[userId].matchUser && matchedUsers.byId[users[userId].matchUser]) {
                return {
                    matchedUser: matchedUsers.byId[users[userId].matchUser],
                    user: users[userId],
                    avatar: users[userId].avatar && activeAvatars[users[userId].avatar] && isAvatarVisible(activeAvatars[users[userId].avatar])
                        ? activeAvatars[users[userId].avatar] 
                        : undefined
                };
            }
    });
}

/**
 * Is matched user new
 */
export function isMatchedUserNew(matchedUserData: IMatchedUserListItem): boolean {   
    if (matchedUserData.matchedUser._isRead !== undefined) {
        return !matchedUserData.matchedUser._isRead;
    }

    return matchedUserData.matchedUser.isNew;
}

/**
 * Is matched user list fetched
 */
export function isMatchedUserListFetched(appState: IAppState): boolean {   
    return getMatchedUsers(appState).isFetched;
}

/**
 * Get not notified matched user
 */
export function getNotNotifiedMatchedUser(): Function {
    return createSelector(
        [getMatchedUsers, getActiveAvatars, getUsers],
        (matchedUsers, activeAvatars, users): IMatchedUserListItem | undefined => {
            if (matchedUsers.allIds.length) {
                // try to find a first not notified matched user
                const matchedUserId: number = matchedUsers.allIds.find((matchedUserId: number) => {
                    const matchedUser = matchedUsers.byId[matchedUserId];
        
                    // skip notified users
                    if (!matchedUser.isViewed && !matchedUser._isNotified) {
                        return true;
                    }
        
                    return false;
                });

                if (matchedUserId) {
                    const matchedUser = matchedUsers.byId[matchedUserId];
                    const user = matchedUser.user 
                        ? users[matchedUser.user] 
                        : undefined;

                    return {
                        matchedUser: matchedUser,
                        user: user,
                        avatar: user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                            ? activeAvatars[user.avatar] 
                            : undefined
                    };
                }
            }
    });
}

/**
 * Get new matched users count
 */
export function getNewMatchedUsersCount(): Function {
    return createSelector(
        [getMatchedUsers],
        (matchedUsers): number => {
            let notReadCount = 0;

            matchedUsers.allIds.forEach((matchedUserId: number) => {
                if (matchedUsers.byId[matchedUserId].isNew && !matchedUsers.byId[matchedUserId]._isRead) {
                    notReadCount++;
                }
            });
        
            return notReadCount;
    });
}

/**
 * Get matched user list
 */
export function getMatchedUserList(userNameFilter: string = ''): Function {
    return createSelector(
        [getMatchedUsers, getActiveAvatars, getUsers],
        (matchedUsers, activeAvatars, users): Array<IMatchedUserListItem> | undefined => {
            if (matchedUsers.allIds.length) {
                const matchedUserList = [];

                matchedUsers.allIds.forEach((matchedUserId: number) => {
                    const matchedUser = matchedUsers.byId[matchedUserId];
                    const user = matchedUser.user ? users[matchedUser.user] : undefined;

                    if (user && (!userNameFilter || user.userName.toLowerCase().startsWith(userNameFilter.toLocaleLowerCase()))) {
                        const avatar = user && user.avatar && activeAvatars[user.avatar]  && isAvatarVisible(activeAvatars[user.avatar])
                            ? activeAvatars[user.avatar] 
                            : undefined;

                        matchedUserList.push({
                            matchedUser: matchedUser,
                            avatar: avatar,
                            user: user
                        });
                    }
                });

                return matchedUserList;
            }
    });
}
