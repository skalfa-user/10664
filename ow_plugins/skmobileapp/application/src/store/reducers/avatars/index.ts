import { IAvatars, IAvatarData } from 'store/states';
import { IAppState } from 'store';
import { createSelector } from 'reselect'
import merge from 'lodash/merge';
import omit from 'lodash/omit';
import { getUsers } from 'store/reducers';

import {
    CONVERSATIONS_SET,
    HOT_LIST_SET,
    BOOKMARKS_LOAD,
    COMPATIBLE_USERS_SET,
    USERS_LOAD, 
    USERS_LOGOUT, 
    APPLICATION_RESET,
    GUESTS_SET,
    MATCHED_USERS_SET,
    VIDEO_IM_ADD_NOTIFICATION,
    AVATARS_BEFORE_UPLOAD,
    AVATARS_AFTER_UPLOAD,
    AVATARS_ERROR_UPLOAD,
    AVATARS_BEFORE_DELETE,
    AVATARS_AFTER_DELETE,
    AVATARS_ERROR_DELETE,
    PHOTOS_BEFORE_SET_AS_AVATAR,
    PHOTOS_ERROR_SET_AS_AVATAR,
    PHOTOS_AFTER_SET_AS_AVATAR
} from 'store/actions';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload,
    IEntityPayload, 
    IAvatarDataPayload, 
    IAvatarAfterUploadPayload 
} from 'store/payloads';

/**
 * Avatars initial state
 */
export const avatarsInitialState: IAvatars = {
    active: {},
    uploading: []
};

/**
 * Avatars reducer
 */
export const avatars = (currentState: IAvatars, action: any): IAvatars => {
    // add initial state
    if (!currentState) {
        currentState = avatarsInitialState;
    }
 
    switch(action.type) {
        case AVATARS_BEFORE_DELETE :
            const beforeDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                active: merge({}, currentState.active, {
                    [beforeDeletePayload.id]: {
                        _isHidden: true
                    }
                })
            };

        case AVATARS_AFTER_DELETE :
            const afterDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                active: omit(currentState.active, [
                    afterDeletePayload.id
                ])
            };

        case AVATARS_ERROR_DELETE :
            const errorDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                active: merge({}, currentState.active, {
                    [errorDeletePayload.id]: {
                        _isHidden: false
                    }
                })
            };

        case PHOTOS_BEFORE_SET_AS_AVATAR :
        case AVATARS_BEFORE_UPLOAD :
           const beforeSetAvatarPayload: IAvatarDataPayload = action.payload;
 
            return {
                ...currentState,
                uploading: [{
                        ...beforeSetAvatarPayload,
                        _isPending: true
                    },
                    ...currentState.uploading
                ]};

        case AVATARS_AFTER_UPLOAD :
        case PHOTOS_AFTER_SET_AS_AVATAR : 
            const afterUploadAvatarPayload: IAvatarAfterUploadPayload = action.payload;

            return {
                ...currentState,
                active: merge({}, currentState.active, {
                    [afterUploadAvatarPayload.avatar.id]: afterUploadAvatarPayload.avatar
                }),
                uploading: currentState.uploading.filter((avatar: IAvatarData) => avatar.id !== afterUploadAvatarPayload.id)
            }

        case AVATARS_ERROR_UPLOAD :
        case PHOTOS_ERROR_SET_AS_AVATAR :
            const errorUploadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                uploading: currentState.uploading.filter((avatar: IAvatarData) => avatar.id !== errorUploadPayload.id)
            }

        case USERS_LOAD :
            const usersLoadPayload: IEntitiesPayload = action.payload;

            if (usersLoadPayload.entities.avatar) {
                return {
                    ...currentState,
                    active: merge({}, currentState.active, usersLoadPayload.entities.avatar)
                };
            }

            return currentState;

        case CONVERSATIONS_SET :
        case HOT_LIST_SET :
        case BOOKMARKS_LOAD :
        case COMPATIBLE_USERS_SET :
        case GUESTS_SET :
        case MATCHED_USERS_SET :
        case VIDEO_IM_ADD_NOTIFICATION :
            const avatarsPayload: IEntitiesPayload = action.payload;

            if (avatarsPayload.entities.avatars) {
                return {
                    ...currentState,
                    active: merge({}, currentState.active, avatarsPayload.entities.avatars)
                };
            }

            return currentState;

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return avatarsInitialState;
    }

    return currentState; 
};

// selectors

export const getActiveAvatars = (appState: IAppState) => appState.avatars.active;
export const getUploadingAvatars = (appState: IAppState) => appState.avatars.uploading;

/**
 * Get uploading avatar
 */
export function getUploadingAvatar(userId: number): Function {
    return createSelector(
        [getUsers, getActiveAvatars, getUploadingAvatars],
        (users, activeAvatars, uploadingAvatars): IAvatarData | undefined => {
            const uploadingAvatar = uploadingAvatars.find(avatar => avatar.userId === userId);

            // get the regular avatar
            if (!uploadingAvatar) {
                const regularAvatar = users[userId] && users[userId].avatar 
                        && activeAvatars[users[userId].avatar] && isAvatarVisible(activeAvatars[users[userId].avatar]) 
                    ? activeAvatars[users[userId].avatar] 
                    : undefined;

                return regularAvatar;
            }

            return uploadingAvatar;
    });
}

/**
 * Is avatar visible
 */
export function isAvatarVisible(avatar: IAvatarData): boolean {
    return avatar._isHidden !== true;
}

/**
 * Is avatar pending
 */
export function isAvatarPending(avatar: IAvatarData): boolean {
    return avatar._isPending === true;
}
