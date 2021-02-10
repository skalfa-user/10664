import { Injectable } from '@angular/core';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';
import { Observable } from 'rxjs/Observable';
import isEqual from 'lodash/isEqual';

// store
import { IAppState } from 'store';
import { PERMISSIONS_UPDATE } from 'store/actions';
import { IPermission } from 'store/states';
import { getPermission } from 'store/reducers';

// payloads
import {
    IEntitiesPayload
} from 'store/payloads';

// services
import { AuthService } from 'services/auth';
import { SecureHttpService } from 'services/http';

// responses
import { IPermissionResponse } from 'services/user/responses';

// schemas
import { permissionListSchema } from './schemas';

export { IPermission } from 'store/states';

@Injectable()
export class PermissionsService {
    /**
     * Constructor
     */
    constructor (
        private http: SecureHttpService,
        private ngRedux: NgRedux<IAppState>,
        private auth: AuthService) {}

    /**
     * Update permissions
     */
    updatePermissions(permissions: Array<IPermissionResponse>): void {
        const payload: IEntitiesPayload = normalize(permissions, permissionListSchema);

        this.ngRedux.dispatch({
            type: PERMISSIONS_UPDATE,
            payload: payload
        });
    }

    /**
     * Watch me
     */
    watchMe(permission: string): Observable<IPermission> {
        return this.ngRedux.select((appState: IAppState) => getPermission(permission, this.auth.getUserId())(appState), isEqual);
    }

    /**
     * Watch me group
     */
    watchMeGroup(permissions: Array<string>): Observable<Array<IPermission>> {
        const observablePermissions = [];

        permissions.forEach(permission => observablePermissions.push(this.watchMe(permission)));

        return Observable.combineLatest(observablePermissions);
    }

    /**
     * Get me
     */
    getMe(permission: string): IPermission {
        return getPermission(permission, this.auth.getUserId())(this.ngRedux.getState());
    }

    /**
     * Track action
     */
    trackAction(groupName: string, actionName, extra = {}): Observable<any> {
        const trackAction: Observable<any> = this.http.post('/permissions/track-actions', {
            groupName,
            actionName,
            extra
        });

        return trackAction;
    }
}
