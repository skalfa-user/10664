import { photos, photosInitialState } from './';
import { IPhotos, IPhotoData, IUser } from 'store/states';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

// payloads
import {
    IEntitiesPayload,
    IEntityPayload,
    IPhotoDataPayload,
    IPhotosAfterUploadPayload,
    IByIdPayload
} from 'store/payloads';

// store
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

import { IAppState } from 'store';

// selectors
import { 
    getAllPhotos,
    isPhotoVisible,
    isPhotoPending
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Photos reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(photos(undefined, '')).toEqual(photosInitialState);
    });

    it('should handle PHOTOS_BEFORE_DELETE and do not mutate a previous state', () => {
        const photoId: number = 1;
        const photoData: IPhotoData = {
            id: photoId
        };

        const state: IPhotos = { // fake state
            active: {
                [photoId]: photoData
            }
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IEntityPayload = {
            id: photoId
        };

        expect(photos(state, {
            type: PHOTOS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            active: {
                [photoId]: {
                    ...photoData,
                    _isHidden: true
                }
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_AFTER_DELETE and do not mutate a previous state', () => {
        const photoId: number = 1;
        const photoData: IPhotoData = {
            id: photoId
        };

        const state: IPhotos = { // fake state
            active: {
                [photoId]: photoData
            }
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IEntityPayload = {
            id: photoId
        };

        expect(photos(state, {
            type: PHOTOS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            active: {}
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_ERROR_DELETE and do not mutate a previous state', () => {
        const photoId: number = 1;
        const photoData: IPhotoData = {
            id: photoId,
            _isHidden: true
        };

        const state: IPhotos = { // fake state
            active: {
                [photoId]: photoData
            }
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IEntityPayload = {
            id: photoId
        };

        expect(photos(state, {
            type: PHOTOS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            active: {
                [photoId]: {
                    ...photoData,
                    _isHidden: false
                }
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_BEFORE_UPLOAD', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId,
            url: 'test',
            bigUrl: 'test',
            approved: true,
            userId: 1
        };

        const payload: IPhotoDataPayload = photo; 

        expect(photos(undefined, {
            type: PHOTOS_BEFORE_UPLOAD,
            payload: payload
        })).toEqual({
            active: {},
            uploading: [{
                ...photo,
                _isPending: true
            }]
        });
    });

    it('should handle PHOTOS_BEFORE_UPLOAD and do not mutate a previous state', () => {
        const photoId1: number = 1;
        const photo1: IPhotoData = {
            id: photoId1
        };

        const photoId2: number = 2;
        const photo2: IPhotoData = {
            id: photoId2
        };

        const state: IPhotos = { // fake state
            uploading: [
                photo1
            ]
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IPhotoDataPayload = photo2; 

        expect(photos(state, {
            type: PHOTOS_BEFORE_UPLOAD,
            payload: payload
        })).toEqual({
            uploading: [
                {
                    ...photo2,
                    _isPending: true
                },
                photo1
            ]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_ERROR_UPLOAD and do not mutate a previous state', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId
        };

        const state: IPhotos = { // fake state
            uploading: [
                photo
            ]
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IByIdPayload = {
            id: photoId
        }; 

        expect(photos(state, {
            type: PHOTOS_ERROR_UPLOAD,
            payload: payload
        })).toEqual({
            uploading: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle PHOTOS_AFTER_UPLOAD and do not mutate a previous state', () => {
        const userId: number = 1;
        const photoId1: string = 'test'; // an uploading photo id
        const photoId2: number = 2;

        const photo: IPhotoData = {
            id: photoId1
        };

        const state: IPhotos = { // fake state
            active: {
            },
            uploading: [
                photo
            ]
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IPhotosAfterUploadPayload = {
            id: photoId1, // the uploading photo id
            userId: userId,
            photo: {
                id: photoId2 // the real photo id
            }
        }; 

        expect(photos(state, {
            type: PHOTOS_AFTER_UPLOAD,
            payload: payload
        })).toEqual({
            active: {
                [photoId2]: {
                    id: photoId2
                }
            },
            uploading: [] // the photo should be removed from uploading store
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOAD', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId,
            url: 'test',
            bigUrl: 'test',
            approved: true,
            userId: 1
        };

        const payload: IEntitiesPayload = {
            entities: {
                photos: {
                    [photoId]: photo
                }
            }
        }; 

        expect(photos(undefined, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            active: {
                [photoId]: photo
            },
            uploading: []
        });
    });

    it('should handle USERS_LOAD and do not mutate a previous state', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId
        };

        const state: IPhotos = { // fake state
            active: {
                [photoId]: photo
            }
        };

        const controlState: IPhotos = cloneDeep(state);

        const payload: IEntitiesPayload = {
            entities: {
                photos: {
                    [photoId]: {
                        ...photo,
                        approved: true
                    }
                }
            }
        }; 

        expect(photos(state, {
            type: USERS_LOAD,
            payload: payload
        })).toEqual({
            active: {
                [photoId]: {
                    ...photo,
                    approved: true
                }
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(photos(undefined, {
            type: USERS_LOGOUT
        })).toEqual(photosInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(photos(undefined, {
            type: APPLICATION_RESET
        })).toEqual(photosInitialState)
    });

    it('getAllPhotos should return correct value', () => {
        const userId: number = 1;
        const photoId1: number = 1;
        const photoId2: number = 2;
 
        const user: IUser = {
            id: userId,
            photos: [photoId1]
        };

        const photo1: IPhotoData = {
            id: photoId1,
            userId: userId
        };

        const photo2: IPhotoData = {
            id: photoId2,
            userId: userId
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user
            },
            photos: {
                active: {
                    [photoId1]: photo1
                },
                uploading: [photo2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getAllPhotos(userId)(fakeRedux.getState())).toEqual([
            photo2,
            photo1
        ]);
    });

    it('getAllPhotos should return an undefined value if there are no photos', () => {
        const userId: number = 1;
        const state: IAppState = { // fake state
            users: { 
            },
            photos: {
                active: {},
                uploading: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getAllPhotos(userId)(fakeRedux.getState())).toBeUndefined();
    });

    it('getAllPhotos should avoid getting hidden photos', () => {
        const userId: number = 1;
        const photoId: number = 1;
 
        const user: IUser = {
            id: userId,
            photos: [photoId]
        };

        const photo: IPhotoData = {
            id: photoId,
            _isHidden: true
        };

        const state: IAppState = { // fake state
            users: { 
                [userId] : user
            },
            photos: {
                active: {
                    [photoId]: photo
                },
                uploading: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getAllPhotos(userId)(fakeRedux.getState())).toBeUndefined();
    });

    it('isPhotoVisible should return a positive boolean value if there is no local variable _isHidden', () => {
        const photoId: number = 1;
 
        const photo: IPhotoData = {
            id: photoId
        };

        expect(isPhotoVisible(photo)).toBeTruthy();
    });

    it('isPhotoVisible should return a negative boolean value if there is local variable _isHidden', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId,
            _isHidden: true
        };

        expect(isPhotoVisible(photo)).toBeFalsy();
    });

    it('isPhotoVisible should return a positive boolean value if there is local variable _isHidden with value equals to false', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId,
            _isHidden: false
        };

        expect(isPhotoVisible(photo)).toBeTruthy();
    });

    it('isPhotoPending should return a negative boolean value if there is no local variable _isPending', () => {
        const photoId: number = 1;
 
        const photo: IPhotoData = {
            id: photoId
        };

        expect(isPhotoPending(photo)).toBeFalsy();
    });

    it('isPhotoPending should return a positive boolean value if there is local variable _isPending', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId,
            _isPending: true
        };

        expect(isPhotoPending(photo)).toBeTruthy();
    });

    it('isPhotoPending should return a negative boolean value if there is local variable _isPending with value equals to false', () => {
        const photoId: number = 1;

        const photo: IPhotoData = {
            id: photoId,
            _isPending: false
        };

        expect(isPhotoPending(photo)).toBeFalsy();
    });
});
