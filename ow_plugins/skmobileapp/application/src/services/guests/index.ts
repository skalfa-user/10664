import { Injectable } from '@angular/core';
import { normalize } from 'normalizr';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Observable';
import isEqual from 'lodash/isEqual';

// services
import { SecureHttpService } from 'services/http';

// responses
import { IGuestResponse } from './responses';

// schemas
import { guestListSchema } from './schemas';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import {
    isGuestNew,
    getNewGuestsCount, 
    isGuestListFetched, 
    getGuestList, 
    IGuestListItem,
    getNotNotifiedGuestsCount 
} from 'store/reducers';

import { 
    GUESTS_SET, 
    GUESTS_BEFORE_DELETE,
    GUESTS_AFTER_DELETE,
    GUESTS_ERROR_DELETE,
    GUESTS_BEFORE_MARK_ALL_READ,
    GUESTS_AFTER_MARK_ALL_READ,
    GUESTS_ERROR_MARK_ALL_READ,
    GUESTS_MARK_READ,
    GUESTS_MARK_ALL_NOTIFIED
} from 'store/actions';

export { IGuestListItem } from 'store/reducers';

@Injectable()
export class GuestsService {
    /**
     * Constructor
     */
    constructor (
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService) {}

    /**
     * Is guest new
     */
    isGuestNew(guestData: IGuestListItem): boolean {
        return isGuestNew(guestData);
    }

    /**
     * Set guests
     */
    setGuests(guests: Array<IGuestResponse>): void {
        const payload: IEntitiesPayload = normalize(guests, guestListSchema);

        this.ngRedux.dispatch({
            type: GUESTS_SET,
            payload: payload
        });
    }

    /**
     * Watch not notified guests count
     */
    watchNotNotifiedGuestCount(): Observable<number> {
        return this.ngRedux.select((appState: IAppState) => getNotNotifiedGuestsCount()(appState));
    }

    /**
     * Watch new guests count
     */
    watchNewGuestCount(): Observable<number> {
        return this.ngRedux.select((appState: IAppState) => getNewGuestsCount()(appState));
    }

    /**
     * Get new guests count
     */
    getNewGuestsCount(): number {
        return getNewGuestsCount()(this.ngRedux.getState());
    }

    /**
     * Watch is guests fetched
     */
    watchIsGuestsFetched(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isGuestListFetched(appState));
    }

    /**
     * Is is guests fetched
     */
    isGuestsFetched(): boolean {
        return isGuestListFetched(this.ngRedux.getState());
    }

    /**
     * Watch guest list
     */
    watchGuestList(): Observable<Array<IGuestListItem> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getGuestList()(appState), isEqual);
    }

    /**
     * Mark guest as read
     */
    markGuestsAsRead(guestId: number): void {
        const payload: IByIdPayload = {
            id: guestId
        };

        this.ngRedux.dispatch({
            type: GUESTS_MARK_READ,
            payload: payload
        });
    }

    /**
     * Mark all guests as notified
     */
    markAllGuestsAsNotified(): void {
        this.ngRedux.dispatch({
            type: GUESTS_MARK_ALL_NOTIFIED,
            payload: {}
        });
    }

    /**
     * Mark all guests as read
     */
    markAllGuestsAsRead(): Observable<any> {
        this.ngRedux.dispatch({
            type: GUESTS_BEFORE_MARK_ALL_READ,
            payload: {}
        });

        const markGuests: Observable<any> = this.http.put('/guests/me/mark-all-as-read');

        markGuests.subscribe(() => {
            this.ngRedux.dispatch({
                type: GUESTS_AFTER_MARK_ALL_READ,
                payload: {}
            });
        }, () => {
            this.ngRedux.dispatch({
                type: GUESTS_ERROR_MARK_ALL_READ,
                payload: {}
            });
        });

        return markGuests;
    }

    /**
     * Delete guest
     */
    deleteGuest(guestId: number): Observable<any> {
        const payload: IByIdPayload = {
            id: guestId
        };
        
        this.ngRedux.dispatch({
            type: GUESTS_BEFORE_DELETE,
            payload: payload
        });

        const deleteGuest: Observable<any> = this.http.delete('/guests/' + guestId);

        deleteGuest.subscribe(() => {
            this.ngRedux.dispatch({
                type: GUESTS_AFTER_DELETE,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: GUESTS_ERROR_DELETE,
                payload: payload
            });
        });

        return deleteGuest;
    }
}
