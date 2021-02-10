import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { NgRedux } from '@angular-redux/store';
import isEqual from 'lodash/isEqual';

// services
import { AuthService } from 'services/auth';
import { SecureHttpService } from 'services/http';
import { StringUtilsService } from 'services/string-utils';
import { IAvatarData } from 'services/avatars';

// responses
import { IPhotoResponse, IAvatarResponse } from 'services/user/responses';

// payloads
import {
    IByIdPayload,
    IAvatarDataPayload, 
    IAvatarAfterUploadPayload,
    IEntityPayload,
    IPhotosAfterUploadPayload,
    IPhotoDataPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';
import { IPhotoData } from 'store/states';

import {
    getAllPhotos,
    isPhotoPending
} from 'store/reducers';

export { IPhotoData } from 'store/states';

import {
    PHOTOS_BEFORE_UPLOAD,
    PHOTOS_AFTER_UPLOAD,
    PHOTOS_ERROR_UPLOAD,
    PHOTOS_BEFORE_DELETE,
    PHOTOS_AFTER_DELETE,
    PHOTOS_ERROR_DELETE,
    PHOTOS_BEFORE_SET_AS_AVATAR,
    PHOTOS_AFTER_SET_AS_AVATAR,
    PHOTOS_ERROR_SET_AS_AVATAR
} from 'store/actions';

@Injectable()
export class PhotosService {
    /**
     * Constructor
     */
    constructor (
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService,
        private auth: AuthService,
        private stringUtils: StringUtilsService) {}

    /**
     * Watch my all photos
     */
    watchMyAllPhotos(): Observable<Array<IPhotoData>> | undefined {
        return this.ngRedux.select((appState: IAppState) => getAllPhotos(this.auth.getUserId())(appState), isEqual);
    }

    /**
     * Before upload my photo
     */
    beforeUploadMyPhoto(id: number | string, url: string): void {
        const userId: number = this.auth.getUserId();
        const photo: IPhotoDataPayload = {
            id: id,
            url: url,
            bigUrl: url,
            approved: true,
            userId: userId
        };
 
        this.ngRedux.dispatch({
            type: PHOTOS_BEFORE_UPLOAD,
            payload: photo
        });
    }

    /**
     * After upload my photo
     */
    afterUploadMyPhoto(id: number | string, photo: IPhotoResponse): void {
        const payload: IPhotosAfterUploadPayload = {
            id: id,
            userId: this.auth.getUserId(),
            photo: photo
        };

        this.ngRedux.dispatch({
            type: PHOTOS_AFTER_UPLOAD,
            payload: payload
        });
    }

    /**
     * Error upload photo
     */
    errorUploadPhoto(id: number | string): void {
        const payload: IByIdPayload = {
            id: id
        };

        this.ngRedux.dispatch({
            type: PHOTOS_ERROR_UPLOAD,
            payload: payload
        });
    }

    /**
     * Is photo pending
     */
    isPhotoPending(photo: IPhotoData): boolean {
        return isPhotoPending(photo);
    }

    /**
     * Delete my photo
     */
    deleteMyPhoto(photoId: number | string): Observable<any> {
        const userId: number = this.auth.getUserId();

        const payload: IEntityPayload = {
            id: photoId,
            entityId: userId
        };

        this.ngRedux.dispatch({
            type: PHOTOS_BEFORE_DELETE,
            payload: payload
        });

        const deletePhoto: Observable<any> = this.http.delete('/photos/' + photoId);

        deletePhoto.subscribe(() => {
            this.ngRedux.dispatch({
                type: PHOTOS_AFTER_DELETE,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: PHOTOS_ERROR_DELETE,
                payload: payload
            });
        });

        return deletePhoto;
    }

    /**
     * Set photo as my avatar
     */
    setPhotoAsMyAvatar(photoId: number | string, url: string): Observable<IAvatarResponse> {
        const userId: number = this.auth.getUserId();
        const fakeId: string = this.stringUtils.getRandomString();

        const avatar: IAvatarData = {
            id: fakeId,
            url: url,
            bigUrl: url,
            pendingUrl: url,
            pendingBigUrl: url,
            active: true,
            userId: userId
        };
 
        const beforeSetAvatarPayload: IAvatarDataPayload = {
            ...avatar
        };

        this.ngRedux.dispatch({
            type: PHOTOS_BEFORE_SET_AS_AVATAR,
            payload: beforeSetAvatarPayload
        });

        const setAsAvatar: Observable<IAvatarResponse> = this.http.put('/photos/' + photoId + '/setAsAvatar');

        setAsAvatar.subscribe(avatar => {
            const afterSetAvatarPayload: IAvatarAfterUploadPayload = {
                id: fakeId,
                userId: userId,
                avatar: avatar
            };

            this.ngRedux.dispatch({
                type: PHOTOS_AFTER_SET_AS_AVATAR,
                payload: afterSetAvatarPayload
            });
        }, () => {
            const errorSetAvatarPayload: IByIdPayload = {
                id: fakeId
            };

            this.ngRedux.dispatch({
                type: PHOTOS_ERROR_SET_AS_AVATAR,
                payload: errorSetAvatarPayload
            });
        });

        return setAsAvatar;
    }
}
