import { Injectable } from '@angular/core';
import { normalize } from 'normalizr';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Observable';
import isEqual from 'lodash/isEqual';

// services
import { SecureHttpService } from 'services/http';
import { AuthService } from 'services/auth';
import { StringUtilsService } from 'services/string-utils';

// payloads
import {
    IEntitiesPayload,
    IEntityPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';

// responses
import { IHotListResponse } from './responses';

// schemas
import { hotListSchema } from './schemas';

// store
import { IAppState } from 'store';

import { 
    isHotListFetched,
    getHostListUsers,
    isUserInHotList,
    getHotListIdByUser,
    IHotListItem
} from 'store/reducers';

import { 
    HOT_LIST_SET,
    HOT_LIST_BEFORE_ADD,
    HOT_LIST_AFTER_ADD,
    HOT_LIST_ERROR_ADD,
    HOT_LIST_BEFORE_DELETE,
    HOT_LIST_AFTER_DELETE,
    HOT_LIST_ERROR_DELETE
} from 'store/actions';

export { IHotListItem } from 'store/reducers';

@Injectable()
export class HotListService {
    /**
     * Constructor
     */
    constructor (
        private stringUtils: StringUtilsService,
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService,
        private auth: AuthService) {}

    /**
     * Set hot list
     */
    setHotList(users: Array<IHotListResponse>): void {
        const payload: IEntitiesPayload = normalize(users, hotListSchema);

        this.ngRedux.dispatch({
            type: HOT_LIST_SET,
            payload: payload
        });
    }

    /**
     * Delete me from hot list
     */
    deleteMeFromHotList(): Observable<any> {
        const hostListId: number | string = this.getMyHotListId();

        if (!hostListId) {
            throw new TypeError(`Logged user is not listed in hot list and could not be deleted`);
        }

        const payload: IEntityPayload = {
            id: hostListId,
            entityId: this.auth.getUserId()
        };

        this.ngRedux.dispatch({
            type: HOT_LIST_BEFORE_DELETE,
            payload: payload
        });

        const deleteUser: Observable<any> = this.http.delete('/hotlist-users/me');

        deleteUser.subscribe(() => {
            this.ngRedux.dispatch({
                type: HOT_LIST_AFTER_DELETE,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: HOT_LIST_ERROR_DELETE,
                payload: payload
            });
        });

        return deleteUser;
    }

    /** 
     * Add me to hot list
     */
    addMeToHotList(): Observable<Array<IHotListResponse>> {
        if (this.isMeInHotList()) {
            throw new TypeError(`Logged user already listed in hot list and could not be added`);
        }

        const randomId: string = this.stringUtils.getRandomString();
        const payload: IEntityPayload = {
            id: randomId,
            entityId: this.auth.getUserId()
        };

        this.ngRedux.dispatch({
            type: HOT_LIST_BEFORE_ADD,
            payload: payload
        });

        const hotList = this.http.post('/hotlist-users/me');

        hotList.subscribe((response: Array<IHotListResponse>) => {
            const afterAddPayload: IWrappedEntitiesPayload = {
                id: randomId,
                data: normalize(response, hotListSchema)
            };

            this.ngRedux.dispatch({
                type: HOT_LIST_AFTER_ADD,
                payload: afterAddPayload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: HOT_LIST_ERROR_ADD,
                payload: payload
            });
        });

        return hotList;
    }
 
    /**
     * Watch is hot list fetched
     */
    watchIsHotListFetched(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isHotListFetched(appState));
    }

    /**
     * Watch hot list
     */
    watchHotList(): Observable<Array<IHotListItem> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getHostListUsers()(appState), isEqual);
    }

    /**
     * Watch me in hot list
     */
    watchMeInHotList(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isUserInHotList(appState, this.auth.getUserId()));
    }

    /**
     * Is me in hot list
     */
    isMeInHotList(): boolean {
        return isUserInHotList(this.ngRedux.getState(), this.auth.getUserId());
    }
 
    /**
     * Get my hot list id
     */
    getMyHotListId(): number | string | undefined {
        return getHotListIdByUser(this.ngRedux.getState(), this.auth.getUserId());
    }
}
