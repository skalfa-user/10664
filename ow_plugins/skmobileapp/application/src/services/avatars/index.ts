import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { NgRedux } from '@angular-redux/store';
import isEqual from 'lodash/isEqual';

// services
import { AuthService } from 'services/auth';
import { SecureHttpService } from 'services/http';

// responses
import { IAvatarResponse } from 'services/user/responses';

// payloads
import {
    IByIdPayload,
    IEntityPayload, 
    IAvatarDataPayload, 
    IAvatarAfterUploadPayload 
} from 'store/payloads';

// store
import { IAppState } from 'store';
import { IAvatarData } from 'store/states';

import {
    isAvatarPending,
    getUploadingAvatar
} from 'store/reducers';

export { IAvatarData } from 'store/states';

import {
    AVATARS_BEFORE_UPLOAD,
    AVATARS_AFTER_UPLOAD,
    AVATARS_ERROR_UPLOAD,
    AVATARS_BEFORE_DELETE,
    AVATARS_AFTER_DELETE,
    AVATARS_ERROR_DELETE
} from 'store/actions';

@Injectable()
export class AvatarsService {
    /**
     * Constructor
     */
    constructor (
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService,
        private auth: AuthService) {}

    /**
     * Watch my uploading avatar
     */
    watchMyUploadingAvatar(): Observable<IAvatarData> | undefined {
        return this.ngRedux.select((appState: IAppState) => getUploadingAvatar(this.auth.getUserId())(appState), isEqual);
    }

    /**
     * Before upload my avatar
     */
    beforeUploadMyAvatar(id: number | string, url: string): void {
        const payload: IAvatarDataPayload = {
            id: id,
            url: url,
            bigUrl: url,
            pendingUrl: url,
            pendingBigUrl: url,
            active: true,
            userId: this.auth.getUserId()
        };
 
        this.ngRedux.dispatch({
            type: AVATARS_BEFORE_UPLOAD,
            payload: payload
        });
    }

    /**
     * After upload my avatar
     */
    afterUploadMyAvatar(id: number | string, avatar: IAvatarResponse): void {
        const payload: IAvatarAfterUploadPayload = {
            id: id,
            userId: this.auth.getUserId(),
            avatar: avatar
        };

        this.ngRedux.dispatch({
            type: AVATARS_AFTER_UPLOAD,
            payload: payload
        });
    }

    /**
     * Error upload avatar
     */
    errorUploadAvatar(id: number | string): void {
        const payload: IByIdPayload = {
            id: id
        };

        this.ngRedux.dispatch({
            type: AVATARS_ERROR_UPLOAD,
            payload: payload
        });
    }

    /**
     * Is avatar pending
     */
    isAvatarPending(avatar: IAvatarData): boolean {
        return isAvatarPending(avatar);
    }

    /**
     * Delete my avatar
     */
    deleteMyAvatar(avatarId: number | string): Observable<any> {
        const userId: number = this.auth.getUserId();
        const payload: IEntityPayload = {
            id: avatarId,
            entityId: userId
        };

        this.ngRedux.dispatch({
            type: AVATARS_BEFORE_DELETE,
            payload: payload
        });

        const deleteAvatar: Observable<any> = this.http.delete('/avatars/' + avatarId);

        deleteAvatar.subscribe(() => {
            this.ngRedux.dispatch({
                type: AVATARS_AFTER_DELETE,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: AVATARS_ERROR_DELETE,
                payload: payload
            });
        });

        return deleteAvatar;
    }
}
