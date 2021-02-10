import { IHotList, IHotListData, IAvatarData, IUser } from 'store/states';
import { IAppState } from 'store';
import { getActiveAvatars, getUsers, isAvatarVisible } from 'store/reducers';
import { createSelector } from 'reselect'
import mapValues from 'lodash/mapValues';
import pick from 'lodash/pick';
import omit from 'lodash/omit';
import merge from 'lodash/merge';
import find from 'lodash/find';

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

// payloads
import {
    IEntityPayload,
    IWrappedEntitiesPayload,
    IEntitiesPayload
} from 'store/payloads';

/**
 * Hot list initial state
 */
export const hotListInitialState: IHotList = {
    isFetched: false,
    byId: {},
    allIds: []
};

/**
 * Hot list reducer
 */
export const hotList = (currentState: IHotList, action: any): IHotList => {
    // add initial state
    if (!currentState) {
        currentState = hotListInitialState;
    }

    switch(action.type) {
        case HOT_LIST_AFTER_DELETE :
            const hotListAfterDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    hotListAfterDeletePayload.id
                ]),
                allIds: currentState.allIds.filter((hotListId: number | string) => hotListId !== hotListAfterDeletePayload.id)
            };

        case HOT_LIST_ERROR_DELETE : // mark user as visible
            const hotListErrorDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [hotListErrorDeletePayload.id]: {
                        _isHidden: false 
                    }
                })
            };

        case HOT_LIST_BEFORE_DELETE : // mark user as hidden
            const hotListBeforeDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [hotListBeforeDeletePayload.id]: {
                        _isHidden: true
                    }
                })
            };

        case HOT_LIST_AFTER_ADD :
            const hotListAfterAddPayload: IWrappedEntitiesPayload = action.payload;

            // delete unnecessary relation
            const addedHotList = mapValues(hotListAfterAddPayload.data.entities.hotList, hotList => {
                return omit(hotList, ['avatar']);
            });

            return {
                ...currentState,
                byId: merge({}, omit(currentState.byId, [ // remove the fake hot list item
                    hotListAfterAddPayload.id
                ]), addedHotList),
                allIds: currentState.allIds.map((hotListId: number|string) => { // replace the fake id with a new one
                    return hotListId !== hotListAfterAddPayload.id
                        ? hotListId
                        : hotListAfterAddPayload.data.result[0];
                })
            };

        case HOT_LIST_ERROR_ADD :
            const hotListErrorAddPayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    hotListErrorAddPayload.id
                ]),
                allIds: currentState.allIds.filter((hotListId: number | string) => hotListId !== hotListErrorAddPayload.id)
            };

        case HOT_LIST_BEFORE_ADD :
            const hotListBeforeAddPayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [hotListBeforeAddPayload.id]: {
                        id: hotListBeforeAddPayload.id,
                        user: hotListBeforeAddPayload.entityId,
                        _isJoinPending: true
                    }
                }),
                allIds: [
                    hotListBeforeAddPayload.id,
                    ...currentState.allIds
                ]
            };

        case HOT_LIST_SET :
            const hotListSetPayload: IEntitiesPayload = action.payload;

            const updatable = hotListSetPayload.result.length && hotListSetPayload.entities.hotList
                ? pick(currentState.byId, hotListSetPayload.result)
                : {};

            // remove unnecessary relations
            const newHotList = hotListSetPayload.result.length && hotListSetPayload.entities.hotList
                ? mapValues(hotListSetPayload.entities.hotList, hotListData => omit(hotListData, ['avatar']))
                : {};

            const joinPendingIds = [];
            const joinPending = {};

            // find all join pending users
            currentState.allIds.forEach((hotListId: number) => {
                const currentHotListData = currentState.byId[hotListId];

                // don't merge join pending users if users already in a payload
                if (currentHotListData._isJoinPending && !find(newHotList, ['user', currentHotListData.user])) {
                    joinPendingIds.push(currentState.byId[hotListId].id);
                    joinPending[currentState.byId[hotListId].id] = merge({}, currentState.byId[hotListId]);
                }
            });

            return {
                isFetched: true,
                byId: hotListSetPayload.result.length && hotListSetPayload.entities.hotList
                    ? merge({}, updatable, newHotList, joinPending)
                    : joinPending,
                allIds: hotListSetPayload.result.length
                    ? [...joinPendingIds, ...hotListSetPayload.result]
                    : [...joinPendingIds]
            };

        case APPLICATION_RESET : // clear all hot list data
        case USERS_LOGOUT :  
            return hotListInitialState;
    }

    return currentState; 
};

// selectors

export interface IHotListItem {
    hotList: IHotListData;
    user: IUser;
    avatar: IAvatarData;
}

export const getHostList = (appState: IAppState) => appState.hotList;

/**
 * Is hot list fetched
 */
export function isHotListFetched(appState: IAppState): boolean {   
    return getHostList(appState).isFetched;
}

/**
 * Is user in hot list
 */
export function isUserInHotList(appState: IAppState, userId: number): boolean {
    const users = getUsers(appState);

    if (users[userId] && users[userId].hotList) {
        const hotList = getHostList(appState);

        return hotList.byId[users[userId].hotList] !== undefined;
    }

    return false;
}

/**
 * Get hot list id by user
 */
export function getHotListIdByUser(appState: IAppState, userId: number): number | string | undefined {
    const users = getUsers(appState);

    if (users[userId] && users[userId].hotList) {
        const hotList = getHostList(appState);

        if (hotList.byId[users[userId].hotList]) {
            return users[userId].hotList;
        }
    }
}

/**
 * Get hot list users
 */
export function getHostListUsers(): Function {
    return createSelector(
        [getHostList, getActiveAvatars, getUsers],
        (hotList, activeAvatars, users): Array<IHotListItem> | undefined => {
            if (hotList.allIds.length) {
                const userList = [];

                hotList.allIds.forEach((hotListId: number) => {
                    const hotListData = hotList.byId[hotListId];

                    // skip hidden users
                    if (hotListData._isHidden === true) {
                        return;
                    }

                    const user = hotListData.user ? users[hotListData.user] : undefined;
                    const avatar = user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                        ? activeAvatars[user.avatar] 
                        : undefined;

                    userList.push({
                        hotList: hotListData,
                        avatar: avatar,
                        user: user
                    });
                });

                return userList;
            }
    });
}
