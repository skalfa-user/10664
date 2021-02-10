import { Injectable } from '@angular/core';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Observable';
import { normalize } from 'normalizr';
import { ISubscription } from 'rxjs/Subscription';
import { IMapType } from 'store/types';
import isEqual from 'lodash/isEqual';

// services
import { SecureHttpService } from 'services/http';
import { StringUtilsService } from 'services/string-utils';

// payloads
import {
    IWrappedEntitiesPayload,
    IEntitiesPayload,
    IEntityPayload
} from 'store/payloads';

// responses
import { IBookmarkResponse } from './responses';

// schemas
import { bookmarkListSchema } from './schemas';

// store
import { IAppState } from 'store';
import { IBookmarkData } from 'store/states';

import { 
    IBookmarkListItem,
    isBookmarkListFetched,
    getBookmarkList,
    getBookmark
} from 'store/reducers';

import { 
    BOOKMARKS_BEFORE_ADD,
    BOOKMARKS_AFTER_ADD,
    BOOKMARKS_ERROR_ADD,
    BOOKMARKS_BEFORE_DELETE,
    BOOKMARKS_AFTER_DELETE,
    BOOKMARKS_ERROR_DELETE,
    BOOKMARKS_LOAD
} from 'store/actions';

export { IBookmarkListItem } from 'store/reducers';

@Injectable()
export class BookmarksService {
    private deleteSubscribes: IMapType<ISubscription> = {};
    private createSubscribes: IMapType<ISubscription> = {};

    /**
     * Constructor
     */
    constructor (
        private stringUtils: StringUtilsService,
        private ngRedux: NgRedux<IAppState>, 
        private http: SecureHttpService) {}

    /**
     * Load bookmark list
     */
    loadBookmarkList(): Observable<Array<IBookmarkResponse>> {
        const bookmarkList = this.http.get('/bookmarks');

        // normalize response
        bookmarkList.subscribe(response => {
            const payload: IEntitiesPayload = normalize(response, bookmarkListSchema);

            this.ngRedux.dispatch({
                type: BOOKMARKS_LOAD,
                payload: payload
            });
        }, () => {});

        return bookmarkList;
    }

    /**
     * Add bookmark
     */
    addBookmark(userId: number): Observable<Array<IBookmarkResponse>> {
        const bookmarkId: string = this.stringUtils.getRandomString();

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        this.ngRedux.dispatch({
            type: BOOKMARKS_BEFORE_ADD,
            payload: payload
        });

        const addBookmark: Observable<Array<IBookmarkResponse>> = this.http.post('/bookmarks', {
            userId: userId
        });

        this.createSubscribes[userId] = addBookmark.subscribe((response) => {
            const afterAddPayload: IWrappedEntitiesPayload = {
                id: bookmarkId,
                data: normalize(response, bookmarkListSchema)
            };

            this.ngRedux.dispatch({
                type: BOOKMARKS_AFTER_ADD,
                payload: afterAddPayload
            });

            delete this.createSubscribes[userId];
        }, () => {
            this.ngRedux.dispatch({
                type: BOOKMARKS_ERROR_ADD,
                payload: payload
            });

            delete this.createSubscribes[userId];
        });

        return addBookmark;
    }

    /**
     * Delete bookmark
     */
    deleteBookmark(bookmarkId: number | string, userId: number): Observable<any> {
        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        this.ngRedux.dispatch({
            type: BOOKMARKS_BEFORE_DELETE,
            payload: payload
        });

        const deleteBookmark: Observable<any> = this.http.delete('/bookmarks/users/' + userId);

        this.deleteSubscribes[userId] = deleteBookmark.subscribe(() => {
            this.ngRedux.dispatch({
                type: BOOKMARKS_AFTER_DELETE,
                payload: payload
            });

            delete this.deleteSubscribes[userId];
        }, () => {
            this.ngRedux.dispatch({
                type: BOOKMARKS_ERROR_DELETE,
                payload: payload
            });

            delete this.deleteSubscribes[userId];
        });

        return deleteBookmark;
    }

    /**
     * Stop all user subscriptions
     */
    stopAllUserSubscriptions(userId: number): void {
        if (this.createSubscribes[userId]) {
            this.createSubscribes[userId].unsubscribe();
        }

        if (this.deleteSubscribes[userId]) {
            this.deleteSubscribes[userId].unsubscribe();
        }
    }

    /**
     * Watch is bookmarks fetched
     */
    watchIsBookmarksFetched(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isBookmarkListFetched(appState));
    }

    /**
     * Watch bookmark list
     */
    watchBookmarkList(): Observable<Array<IBookmarkListItem> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getBookmarkList()(appState), isEqual);
    }

    /**
     * Get bookmark
     */
    getBookmark(userId: number): IBookmarkData | undefined {
        return getBookmark(this.ngRedux.getState(), userId);
    }
}
