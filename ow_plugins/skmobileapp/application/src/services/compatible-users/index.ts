import { Injectable } from '@angular/core';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Observable';
import { normalize } from 'normalizr';
import isEqual from 'lodash/isEqual';

// responses
import { ICompatibleUserResponse } from './responses';

// schemas
import { userListSchema } from './schemas';

// payloads
import {
    IEntitiesPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import { 
    isCompatibleUserListFetched,
    ICompatibleUserListItem,
    getCompatibleUserList
} from 'store/reducers';

import { 
    COMPATIBLE_USERS_SET
} from 'store/actions';

export { ICompatibleUserListItem } from 'store/reducers';

@Injectable()
export class CompatibleUsersService {
    /**
     * Constructor
     */
    constructor (private ngRedux: NgRedux<IAppState>) {}

    /**
     * Set compatible users
     */
    setCompatibleUsers(users: Array<ICompatibleUserResponse>): void {
        const payload: IEntitiesPayload = normalize(users, userListSchema);

        this.ngRedux.dispatch({
            type: COMPATIBLE_USERS_SET,
            payload: payload
        });
    }

    /**
     * Watch is compatible users fetched
     */
    watchIsCompatibleUsersFetched(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isCompatibleUserListFetched(appState));
    }

    /**
     * Watch compatible user list
     */
    watchCompatibleUserList(): Observable<Array<ICompatibleUserListItem> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getCompatibleUserList()(appState), isEqual);
    }
}
