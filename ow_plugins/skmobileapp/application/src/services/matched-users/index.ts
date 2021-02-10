import { Injectable } from '@angular/core';
import { normalize } from 'normalizr';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Observable';
import isEqual from 'lodash/isEqual';

// services
import { SecureHttpService } from 'services/http';

// payloads
import {
    IByIdPayload,
    IEntitiesPayload
} from 'store/payloads';

// responses
import { IMatchedUserResponse } from './responses';

// schemas
import { matchedUserListSchema } from './schemas';

// store
import { IAppState } from 'store';

import {
    getMatchedUserData,
    getNewMatchedUsersCount,
    getNotNotifiedMatchedUser,
    isMatchedUserListFetched,
    getMatchedUserList,
    IMatchedUserListItem,
    isMatchedUserNew
} from 'store/reducers';

import {
    MATCHED_USERS_BEFORE_MARK_READ,
    MATCHED_USERS_AFTER_MARK_READ,
    MATCHED_USERS_ERROR_MARK_READ,
    MATCHED_USERS_BEFORE_MARK_NOTIFIED,
    MATCHED_USERS_AFTER_MARK_NOTIFIED,
    MATCHED_USERS_ERROR_MARK_NOTIFIED,
    MATCHED_USERS_SET
} from 'store/actions';

export { IMatchedUserListItem } from 'store/reducers';

@Injectable()
export class MatchedUsersService {
    /**
     * Constructor
     */
    constructor(
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService) {}

    /**
     * Get matched user data
     */
    getMatchedUserData(userId: number): IMatchedUserListItem | undefined {
        return getMatchedUserData(userId)(this.ngRedux.getState());
    }

    /**
     * Is matched user new
     */
    isMatchedUserNew(matchedUserData: IMatchedUserListItem): boolean {
        return isMatchedUserNew(matchedUserData);
    }

    /**
     * Set matched users
     */
    setMatchedUsers(matchedUsers: Array<IMatchedUserResponse>): void {
        const payload: IEntitiesPayload = normalize(matchedUsers, matchedUserListSchema);

        this.ngRedux.dispatch({
            type: MATCHED_USERS_SET,
            payload: payload
        });
    }

    /**
     * Watch is matched users fetched
     */
    watchIsMatchedUsersFetched(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isMatchedUserListFetched(appState));
    }

    /**
     * Watch not notified matched user
     */
    watchNotNotifiedMatchedUser(): Observable<IMatchedUserListItem> | undefined {
        return this.ngRedux.select((appState: IAppState) => getNotNotifiedMatchedUser()(appState), isEqual);
    }

    /**
     * Watch new matched users count
     */
    watchNewMatchedUsersCount(): Observable<number> {
        return this.ngRedux.select((appState: IAppState) => getNewMatchedUsersCount()(appState));
    }

    /**
     * Watch matched user list
     */
    watchMatchedUserList(userNameFilter: string = ''): Observable<Array<IMatchedUserListItem> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getMatchedUserList(userNameFilter)(appState), isEqual);
    }

    /**
     * Mark matched user as notified
     */
    markMatchedUserAsNotified(matchedUserId: number): Observable<any> {
        const payload: IByIdPayload = {
            id: matchedUserId
        };

        this.ngRedux.dispatch({
            type: MATCHED_USERS_BEFORE_MARK_NOTIFIED,
            payload: payload
        });

        const markMatchedUser: Observable<any> = this.http.put('/matched-users/' + matchedUserId, {
            isRead: true
        });

        markMatchedUser.subscribe(() => {
            this.ngRedux.dispatch({
                type: MATCHED_USERS_AFTER_MARK_NOTIFIED,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: MATCHED_USERS_ERROR_MARK_NOTIFIED,
                payload: payload
            });
        });

        return markMatchedUser;
    }

    /**
     * Mark matched user as read
     */
    markMatchedUserAsRead(matchedUserId: number): Observable<any> {
        const payload: IByIdPayload = {
            id: matchedUserId
        };

        this.ngRedux.dispatch({
            type: MATCHED_USERS_BEFORE_MARK_READ,
            payload: payload
        });

        const markMatchedUser: Observable<any> = this.http.put('/matched-users/' + matchedUserId, {
            isNew: false
        });

        markMatchedUser.subscribe(() => {
            this.ngRedux.dispatch({
                type: MATCHED_USERS_AFTER_MARK_READ,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: MATCHED_USERS_ERROR_MARK_READ,
                payload: payload
            });
        });

        return markMatchedUser;
    }
}
