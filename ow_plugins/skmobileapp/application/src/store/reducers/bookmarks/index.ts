import { IAppState } from 'store';
import { getActiveAvatars, getUsers, getMatchActions, isAvatarVisible } from 'store/reducers';
import { createSelector } from 'reselect'
import omit from 'lodash/omit';
import mapValues from 'lodash/mapValues';
import merge from 'lodash/merge';
import uniq from 'lodash/uniq';

import {
    IBookmarkData, 
    IBookmarks,
    IUser, 
    IAvatarData, 
    IMatchAction 
} from 'store/states';

import {
    USERS_LOAD,
    BOOKMARKS_BEFORE_DELETE,
    BOOKMARKS_AFTER_DELETE,
    BOOKMARKS_ERROR_DELETE,
    BOOKMARKS_BEFORE_ADD,
    BOOKMARKS_AFTER_ADD,
    BOOKMARKS_ERROR_ADD,
    BOOKMARKS_LOAD,
    USERS_LOGOUT, 
    APPLICATION_RESET,
} from 'store/actions';

// payloads
import {
    IWrappedEntitiesPayload,
    IEntityPayload, 
    IEntitiesPayload 
} from 'store/payloads';

/**
 * Bookmarks initial state
 */
export const bookmarksInitialState: IBookmarks = {
    isFetched: false,
    byId: {},
    allIds: []
};

/**
 * Bookmarks reducer
 */
export const bookmarks = (currentState: IBookmarks, action: any): IBookmarks => {
    // add initial state
    if (!currentState) {
        currentState = bookmarksInitialState;
    }

    switch(action.type) {
        case BOOKMARKS_BEFORE_ADD :
            const beforeAddPayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [beforeAddPayload.id]: {
                        id: beforeAddPayload.id,
                        user: beforeAddPayload.entityId
                    }
                }),
                allIds: [
                    beforeAddPayload.id,
                    ...currentState.allIds
                ]
            };

        case BOOKMARKS_AFTER_ADD :
            const bookmarksAfterAddPayload: IWrappedEntitiesPayload = action.payload;

            // delete unnecessary relations
            const addedBookmarks = mapValues(bookmarksAfterAddPayload.data.entities.bookmarks, bookmark => {
                return omit(bookmark, [
                    'avatar',
                    'matchAction'
                ]);
            });

            return {
                ...currentState,
                byId: merge({}, omit(currentState.byId, [ // remove the fake bookmark item
                    bookmarksAfterAddPayload.id
                ]), addedBookmarks),
                allIds: currentState.allIds.map((bookmarkId: number | string) => { // replace the fake id with a new one
                    return bookmarkId !== bookmarksAfterAddPayload.id
                        ? bookmarkId
                        : bookmarksAfterAddPayload.data.result[0];
                })
            };

        case BOOKMARKS_ERROR_ADD :
            const bookmarksErrorAddPayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    bookmarksErrorAddPayload.id
                ]),
                allIds: currentState.allIds.filter((bookmarkId: number | string) => bookmarkId !== bookmarksErrorAddPayload.id)
            };

        case BOOKMARKS_BEFORE_DELETE : // mark a bookmark as hidden
            const bookmarksBeforeDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [bookmarksBeforeDeletePayload.id]: {
                        _isHidden: true
                    }
                })
            };

        case BOOKMARKS_AFTER_DELETE :
            const bookmarksAfterDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    bookmarksAfterDeletePayload.id
                ]),
                allIds: currentState.allIds.filter((bookmarkId: number) => bookmarkId !== bookmarksAfterDeletePayload.id)
            };

        case BOOKMARKS_ERROR_DELETE : // mark a bookmark as visible
            const bookmarksErrorDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [bookmarksErrorDeletePayload.id]: {
                        _isHidden: false
                    }
                })
            };

        case USERS_LOAD :
            const usersLoadPayload: IEntitiesPayload = action.payload;

            if (usersLoadPayload.entities.bookmark && usersLoadPayload.entities.user && usersLoadPayload.result) {
                const bookmarkId: number | string = usersLoadPayload.entities.user[usersLoadPayload.result].bookmark;

                return {
                    ...currentState,
                    byId: merge({}, currentState.byId, usersLoadPayload.entities.bookmark),
                    allIds: uniq([
                        ...currentState.allIds,
                        bookmarkId
                    ])
                };
            }

            return currentState;

        case BOOKMARKS_LOAD :
            const bookmarksLoadPayload: IEntitiesPayload = action.payload;

            return {
                isFetched: true,
                byId: bookmarksLoadPayload.result.length && bookmarksLoadPayload.entities.bookmarks
                    ? mapValues(bookmarksLoadPayload.entities.bookmarks, bookmark => {
                        return {
                            id: bookmark.id,
                            ...omit(bookmark, ['avatar', 'matchAction'])
                        };
                    })
                    : {},
                allIds: bookmarksLoadPayload.result.length
                    ? bookmarksLoadPayload.result
                    : []
            };

        case APPLICATION_RESET : // clear all bookmarks data
        case USERS_LOGOUT :  
            return bookmarksInitialState;
    }

    return currentState; 
};

// selectors

export interface IBookmarkListItem {
    bookmark: IBookmarkData;
    user: IUser;
    avatar: IAvatarData;
    matchAction: IMatchAction
}

export const getBookmarks = (appState: IAppState) => appState.bookmarks;

/**
 * Is bookmark list fetched
 */
export function isBookmarkListFetched(appState: IAppState): boolean {   
    return getBookmarks(appState).isFetched;
}

/**
 * Get bookmark list
 */
export function getBookmarkList(): Function {
    return createSelector(
        [getBookmarks, getActiveAvatars, getUsers, getMatchActions],
        (bookmarks, activeAvatars, users, matchActions): Array<IBookmarkListItem> | undefined => {
            if (bookmarks.allIds.length) {
                const bookmarkList = [];

                bookmarks.allIds.forEach((bookmarkId: number) => {
                    const bookmark = bookmarks.byId[bookmarkId];

                    // skip hidden bookmarks
                    if (bookmark._isHidden === true) {
                        return;
                    }

                    const user = bookmark.user ? users[bookmark.user] : undefined;
                    const matchAction = user && user.matchAction ? matchActions[user.matchAction] : undefined;
                    const avatar = user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                        ? activeAvatars[user.avatar] 
                        : undefined;

                    bookmarkList.push({
                        bookmark: bookmark,
                        avatar: avatar,
                        user: user,
                        matchAction: matchAction
                    });
                });

                return bookmarkList;
            }
    });
}

/**
 * Get bookmark
 */
export function getBookmark(appState: IAppState, userId: number): IBookmarkData | undefined {
    const user = getUsers(appState)[userId];

    if (user && user.bookmark) {
        return getBookmarks(appState).byId[user.bookmark];
    }
}
