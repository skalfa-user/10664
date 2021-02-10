import { IPhotos, IPhotoData } from 'store/states';
import { IAppState } from 'store';
import { createSelector } from 'reselect'
import { getUsers } from 'store/reducers';
import merge from 'lodash/merge';
import omit from 'lodash/omit';

import { 
    USERS_LOAD, 
    USERS_LOGOUT, 
    APPLICATION_RESET,
    PHOTOS_BEFORE_UPLOAD,
    PHOTOS_AFTER_UPLOAD,
    PHOTOS_ERROR_UPLOAD,
    PHOTOS_BEFORE_DELETE,
    PHOTOS_AFTER_DELETE,
    PHOTOS_ERROR_DELETE
} from 'store/actions';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload,
    IEntityPayload,
    IPhotoDataPayload,
    IPhotosAfterUploadPayload
} from 'store/payloads';

/**
 * Photos initial state 
 */
export const photosInitialState: IPhotos = {
    active: {},
    uploading: []
};

/**
 * Photos reducer
 */
export const photos = (currentState: IPhotos, action: any): IPhotos => {
    // add initial state
    if (!currentState) {
        currentState = photosInitialState;
    }

    switch(action.type) {
        case PHOTOS_BEFORE_DELETE :
            const photosBeforeDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                active: merge({}, currentState.active, {
                    [photosBeforeDeletePayload.id]: {
                        _isHidden: true
                    }
                })
            };

        case PHOTOS_AFTER_DELETE :
            const photosAfterDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                active: omit(currentState.active, [
                    photosAfterDeletePayload.id
                ])
            };

        case PHOTOS_ERROR_DELETE :
            const photosErrorDeletePayload: IEntityPayload = action.payload;

            return {
                ...currentState,
                active: merge({}, currentState.active, {
                    [photosErrorDeletePayload.id]: {
                        _isHidden: false
                    }
                })
            };

        case PHOTOS_BEFORE_UPLOAD :
            const photosBeforeUploadPayload: IPhotoDataPayload = action.payload;

            return {
                ...currentState,
                uploading: [{
                        ...photosBeforeUploadPayload,
                        _isPending: true
                    },
                    ...currentState.uploading
                ]};

        case PHOTOS_AFTER_UPLOAD :
            const photosAfterUploadPayload: IPhotosAfterUploadPayload = action.payload;

            return {
                ...currentState,
                active: merge({}, currentState.active, {
                    [photosAfterUploadPayload.photo.id]: photosAfterUploadPayload.photo
                }),
                uploading: currentState.uploading.filter((photo: IPhotoData) => photo.id !== photosAfterUploadPayload.id)
            }

        case PHOTOS_ERROR_UPLOAD :
            const photosErrorUploadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                uploading: currentState.uploading.filter((photo: IPhotoData) => photo.id !== photosErrorUploadPayload.id)
            }

        case USERS_LOAD :
            const usersLoadsPayload: IEntitiesPayload = action.payload;

            if (usersLoadsPayload.entities.photos) {
                return {
                    ...currentState,
                    active: merge({}, currentState.active, usersLoadsPayload.entities.photos)
                };
            }

            return currentState;

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return photosInitialState;
    }

    return currentState; 
};

// selectors

export const getActivePhotos = (appState: IAppState) => appState.photos.active;
export const getUploadingPhotos = (appState: IAppState) => appState.photos.uploading;

/**
 * Get all photos
 */
export function getAllPhotos(userId: number): Function {
    return createSelector(
        [getUsers, getActivePhotos, getUploadingPhotos],
        (users, activePhotos, uploadingPhotos): Array<IPhotoData> | undefined => {
            const uploadedPhotos = [];
            const user = users[userId];

            // collect active photos
            if (user && user.photos && user.photos.length) {
                user.photos.forEach((photoId: number) => {
                    if (activePhotos[photoId] && isPhotoVisible(activePhotos[photoId])) {
                        uploadedPhotos.push(activePhotos[photoId]);
                    }
                });
            }

            // collect uploading photos
            const notUploadedPhotos = uploadingPhotos.filter(photo => photo.userId === userId);

            if (uploadedPhotos.length || notUploadedPhotos.length) {
                return [
                    ...notUploadedPhotos,
                    ...uploadedPhotos
                ];
            }
    });
}

/**
 * Is photo visible
 */
export function isPhotoVisible(photo: IPhotoData): boolean {
    return photo._isHidden !== true;
}

/**
 * Is photo pending
 */
export function isPhotoPending(photo: IPhotoData): boolean {
    return photo._isPending === true;
}
