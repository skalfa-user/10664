import { IMapType } from 'store/types';
import { IUser, IAvatarData, IPhotoData, IBookmarkData, IMatchAction } from 'store/states';
import { IAppState } from 'store';
import { getActiveAvatars, getActivePhotos, getBookmarks, getMatchActions } from 'store/reducers';
import { createSelector } from 'reselect'

import merge from 'lodash/merge';
import mergeWith from 'lodash/mergeWith';
import isArray from 'lodash/isArray';
import forOwn from 'lodash/forOwn';

// payloads
import {
    IWrappedEntitiesPayload,
    IEntitiesPayload,
    IEntityPayload, 
    IAvatarAfterUploadPayload,
    IMessageDataPayload,
    IMatchActionDataPayload,
    IPhotosAfterUploadPayload,
    IByIdPayload
} from 'store/payloads';

import {
    isPhotoVisible,
    isAvatarVisible
} from 'store/reducers';

import {
    AVATARS_AFTER_UPLOAD,
    AVATARS_BEFORE_DELETE,
    AVATARS_ERROR_DELETE,
    PHOTOS_AFTER_UPLOAD,
    PHOTOS_AFTER_DELETE,
    PHOTOS_AFTER_SET_AS_AVATAR,
    BOOKMARKS_BEFORE_ADD,
    BOOKMARKS_ERROR_ADD,
    BOOKMARKS_AFTER_ADD,
    MESSAGES_BEFORE_ADD,
    CONVERSATIONS_SET,
    HOT_LIST_ERROR_DELETE,
    HOT_LIST_BEFORE_DELETE,
    HOT_LIST_AFTER_ADD,
    HOT_LIST_ERROR_ADD,
    HOT_LIST_BEFORE_ADD,
    HOT_LIST_SET,
    BOOKMARKS_ERROR_DELETE,
    BOOKMARKS_BEFORE_DELETE,
    BOOKMARKS_LOAD,
    COMPATIBLE_USERS_SET,
    USERS_LOAD, 
    USERS_LOGOUT, 
    APPLICATION_RESET,
    GUESTS_SET,
    MATCHED_USERS_SET,
    MATCH_ACTIONS_ERROR_DELETE,
    MATCH_ACTIONS_BEFORE_DELETE,
    MATCH_ACTIONS_AFTER_ADD,
    MATCH_ACTIONS_BEFORE_ADD,
    MATCH_ACTIONS_ERROR_ADD,
    PERMISSIONS_UPDATE,
    USERS_BEFORE_BLOCK,
    USERS_ERROR_BLOCK,
    USERS_BEFORE_UNBLOCK,
    USERS_ERROR_UNBLOCK,
    VIDEO_IM_ADD_NOTIFICATION
} from 'store/actions';

/**
 * Users initial state
 */
export const usersInitialState: IMapType<IUser> = {};

/**
 * Users reducer
 */
export const users = (currentState: IMapType<IUser>, action: any): IMapType<IUser> => {
    // add initial state
    if (!currentState) {
        currentState = usersInitialState;
    }

    switch(action.type) {
        case AVATARS_AFTER_UPLOAD :
        case PHOTOS_AFTER_SET_AS_AVATAR :
            const afterUploadAvatarPayload: IAvatarAfterUploadPayload = action.payload;

            return merge({}, currentState, {
                [afterUploadAvatarPayload.userId]: {
                    avatar: afterUploadAvatarPayload.avatar.id
                }
            });

        case AVATARS_BEFORE_DELETE :
            const beforeAvatarDeletePayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [beforeAvatarDeletePayload.entityId]: {
                    avatar: null
                }
            });

        case AVATARS_ERROR_DELETE :
            const errorAvatarDeletePayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [errorAvatarDeletePayload.entityId]: {
                    avatar: errorAvatarDeletePayload.id // restore the avatar 
                }
            });

        case PHOTOS_AFTER_DELETE :
            const photosAfterDeletePayload: IEntityPayload = action.payload;

            return mergeWith({}, currentState, {
                [photosAfterDeletePayload.entityId]: currentState[photosAfterDeletePayload.entityId]
            }, (objValue, srcValue, key) => {
                if (key == 'photos') {
                    return currentState[photosAfterDeletePayload.entityId].photos.filter((photoId: number) => photoId !== photosAfterDeletePayload.id);
                }
            });

        case PHOTOS_AFTER_UPLOAD :
            const photosAfterUploadPayload: IPhotosAfterUploadPayload = action.payload;

            return mergeWith({}, currentState, {
                [photosAfterUploadPayload.userId]: currentState[photosAfterUploadPayload.userId]
            }, (objValue, srcValue, key) => {
                if (key == 'photos') {
                    if (isArray(objValue)) {
                        return [
                            photosAfterUploadPayload.photo.id,
                            ...currentState[photosAfterUploadPayload.userId].photos
                        ];
                    }

                    return [photosAfterUploadPayload.photo.id];
                }
            });

        case MESSAGES_BEFORE_ADD :
            const messagesBeforeAddPayload: IMessageDataPayload = action.payload;

            return merge({}, currentState, {
                [messagesBeforeAddPayload.opponentId]: {
                    conversation: messagesBeforeAddPayload.conversation
                }
            });

        case USERS_BEFORE_BLOCK : // mark user as blocked
            const usersBeforeBlockPayload: IByIdPayload = action.payload;

            return merge({}, currentState, {
                [usersBeforeBlockPayload.id]: {
                    _isMarkedAsBlocked: true
                }
            });

        case USERS_ERROR_BLOCK : // mark user as unblocked
            const usersErrorBlockPayload: IByIdPayload = action.payload;

            return merge({}, currentState, {
                [usersErrorBlockPayload.id]: {
                    _isMarkedAsBlocked: false
                }
            });

        case USERS_BEFORE_UNBLOCK : // mark user as unblocked
            const usersBeforeUnBlockPayload: IByIdPayload = action.payload;

            return merge({}, currentState, {
                [usersBeforeUnBlockPayload.id]: {
                    _isMarkedAsBlocked: false
                }
            });

        case USERS_ERROR_UNBLOCK : // mark user as blocked
            const usersErrorUnBlockPayload: IByIdPayload = action.payload;

            return merge({}, currentState, {
                [usersErrorUnBlockPayload.id]: {
                    _isMarkedAsBlocked: true
                }
            });

        case HOT_LIST_ERROR_DELETE : // restore hot list id
            const hotListErrorDeletePayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [hotListErrorDeletePayload.entityId]: {
                    hotList: hotListErrorDeletePayload.id
                }
            });

        case HOT_LIST_BEFORE_DELETE :
        case HOT_LIST_ERROR_ADD :
            const hotListBeforeDeletePayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [hotListBeforeDeletePayload.entityId]: {
                    hotList: null
                }
            });
 
        case HOT_LIST_BEFORE_ADD :
            const hotListBeforeAddPayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [hotListBeforeAddPayload.entityId]: {
                    id: hotListBeforeAddPayload.entityId,
                    hotList: hotListBeforeAddPayload.id
                }
            });

        case BOOKMARKS_BEFORE_ADD :
            const beforeAddBookmarksPayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [beforeAddBookmarksPayload.entityId]: {
                    id: beforeAddBookmarksPayload.entityId,
                    bookmark: beforeAddBookmarksPayload.id
                }
            });

        case BOOKMARKS_ERROR_ADD :
            const bookmarksErrorAddPayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [bookmarksErrorAddPayload.entityId]: {
                    bookmark: null
                }
            });

        case BOOKMARKS_ERROR_DELETE : // restore bookmark id
            const bookmarksErrorDeletePayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [bookmarksErrorDeletePayload.entityId]: {
                    bookmark: bookmarksErrorDeletePayload.id
                }
            });

        case BOOKMARKS_BEFORE_DELETE :
            const bookmarksBeforeDeletePayload: IEntityPayload = action.payload;

            return merge({}, currentState, {
                [bookmarksBeforeDeletePayload.entityId]: {
                    bookmark: null
                }
            });

        case MATCH_ACTIONS_BEFORE_ADD :
        case MATCH_ACTIONS_ERROR_DELETE :
            const matchActionsBeforeAddPayload: IMatchActionDataPayload = action.payload;

            return merge({}, currentState, {
                [matchActionsBeforeAddPayload.userId]: {
                    id: matchActionsBeforeAddPayload.userId,
                    matchAction: matchActionsBeforeAddPayload.id
                }
            });

        case BOOKMARKS_AFTER_ADD :
        case HOT_LIST_AFTER_ADD :
            const bookmarksAfterAddPayload: IWrappedEntitiesPayload = action.payload;

            return merge({}, currentState, bookmarksAfterAddPayload.data.entities.users);

        case MATCH_ACTIONS_AFTER_ADD :
            const matchActionsAfterAddPayload: IWrappedEntitiesPayload = action.payload;

            return merge({}, currentState, matchActionsAfterAddPayload.data.entities.user);

        case MATCH_ACTIONS_ERROR_ADD :
        case MATCH_ACTIONS_BEFORE_DELETE :
            const matchActionsErrorAddPayload: IMatchActionDataPayload = action.payload;

            return merge({}, currentState, {
                [matchActionsErrorAddPayload.userId]: {
                    matchAction: null
                }
            });

        case USERS_LOAD :
            const usersLoadPayload: IEntitiesPayload = action.payload;
            const processedUser = {};

            // add the additional flag
            forOwn(usersLoadPayload.entities.user, (value, key) => {
                processedUser[key] = {
                    ...value,
                    _isDataLoaded: true
                };
            });

            return mergeWith({}, currentState, processedUser, (objValue, srcValue) => {
                if (isArray(objValue)) {
                    return srcValue; // replace all arrays 
                }
            });
 
        case BOOKMARKS_LOAD :
        case CONVERSATIONS_SET :
        case HOT_LIST_SET :
        case COMPATIBLE_USERS_SET :
        case GUESTS_SET :
        case MATCHED_USERS_SET :
        case VIDEO_IM_ADD_NOTIFICATION :
            const bookmarksLoadPayload: IEntitiesPayload = action.payload;

            if (bookmarksLoadPayload.entities.users) {
                return merge({}, currentState, bookmarksLoadPayload.entities.users);
            }

            return currentState;

        case PERMISSIONS_UPDATE :
            const permissionsUpdatePayload: IEntitiesPayload = action.payload;

            if (permissionsUpdatePayload.entities.users) {
                return mergeWith({}, currentState, permissionsUpdatePayload.entities.users, (objValue, srcValue, key) => {
                    if (isArray(objValue) && key == 'permissions') {
                        return srcValue; // replace arrays 
                    }
                });
            }

            return currentState;

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return usersInitialState;
    }
 
    return currentState; 
};

// selectors 

export interface IUserWithAvatar {
    user: IUser;
    avatar: IAvatarData
}

export interface IUserWithFullData {
    user: IUser;
    avatar: IAvatarData;
    bookmark: IBookmarkData;
    photos: Array<IPhotoData>;
    matchAction: IMatchAction;
}

export const getUsers = (appState: IAppState) => appState.users;

/**
 * Is user loaded
 */
export function isUserLoaded(user: IUser | undefined): boolean {
    return user && user._isDataLoaded;
}

/**
 * Is user blocked
 */
export function isUserBlocked(user: IUser): boolean {
    if (user._isMarkedAsBlocked !== undefined) {
        return user._isMarkedAsBlocked;
    }

    return user.isBlocked;
}

/**
 * Get user with full data
 */
export function getUserWithFullData(userId: number): Function {
    return createSelector(
        [getUsers, getActiveAvatars, getBookmarks, getActivePhotos, getMatchActions],
        (users, activeAvatars, bookmarks, activePhotos, matchActions): IUserWithFullData | undefined => {
            if (users[userId]) {
                const bookmark = users[userId].bookmark ? bookmarks.byId[users[userId].bookmark] : undefined;
                const matchAction = users[userId].matchAction ? matchActions[users[userId].matchAction] : undefined;
                const photoList: Array<IPhotoData> = [];
                const avatar = users[userId].avatar && activeAvatars[users[userId].avatar] && isAvatarVisible(activeAvatars[users[userId].avatar]) 
                    ? activeAvatars[users[userId].avatar] 
                    : undefined;

                // find user's photos
                if (users[userId].photos && users[userId].photos.length) {
                    users[userId].photos.forEach((photoId: number) => {
                        const photo: IPhotoData = activePhotos[photoId];

                        if (photo && isPhotoVisible(photo)) {
                            photoList.push(photo);
                        }
                    });
                }

                return {
                    user: users[userId],
                    avatar: avatar,
                    bookmark: bookmark,
                    photos: photoList,
                    matchAction: matchAction
                }
            }
    });
}

/**
 * Get user with avatar
 */
export function getUserWithAvatar(appState: IAppState, userId: number): IUserWithAvatar | undefined {
    const user = getUsers(appState)[userId];

    if (user) {
        const avatar = user.avatar && getActiveAvatars(appState)[user.avatar] && isAvatarVisible(getActiveAvatars(appState)[user.avatar])
            ? getActiveAvatars(appState)[user.avatar] 
            : undefined;

        return {
            user: user,
            avatar: avatar
        }
    }
}

/**
 * Get user
 */
export function getUser(appState: IAppState, userId: number): IUser | undefined {
    const user = getUsers(appState)[userId];

    if (user) {
        return user;
    }
}
